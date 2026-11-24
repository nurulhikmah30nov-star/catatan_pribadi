<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class AesGcmEncryptionService
{
    protected const CIPHER = 'aes-256-gcm';
    protected const NONCE_LENGTH = 12; // 96-bit nonce sesuai standar NIST SP 800-38D
    protected const TAG_LENGTH = 16;   // 128-bit authentication tag
    protected const VERSION = 1;       // Versi envelope 0x01

    protected string $binaryKey;

    /**
     * Inisialisasi service dengan kunci 256-bit (32 bytes).
     */
    public function __construct(?string $key = null)
    {
        $rawKey = $key ?? config('cipher.key') ?? env('APP_CIPHER_KEY');

        if (empty($rawKey)) {
            throw new RuntimeException('Kunci enkripsi APP_CIPHER_KEY belum dikonfigurasi di lingkungan (.env).');
        }

        // Jika kunci berbentuk 64 karakter hex, konversi ke 32 byte biner
        if (strlen($rawKey) === 64 && ctype_xdigit($rawKey)) {
            $this->binaryKey = hex2bin($rawKey);
        } elseif (strlen($rawKey) === 32) {
            $this->binaryKey = $rawKey;
        } else {
            throw new RuntimeException('Kunci enkripsi APP_CIPHER_KEY wajib berukuran 256-bit (64 karakter heksadesimal atau 32 byte biner).');
        }
    }

    /**
     * Mengenkripsi data teks menggunakan AES-256-GCM dengan nonce acak baru dan AAD opsional.
     *
     * Format Envelope Biner:
     * [1 byte Version (0x01)] + [12 bytes Nonce] + [16 bytes Auth Tag] + [Ciphertext]
     *
     * @param string $plaintext Data mentah yang akan dienkripsi
     * @param string $aad Additional Authenticated Data (opsional, misal ID entitas/user)
     * @return string Ciphertext yang dibungkus dalam string Base64 aman
     */
    public function encrypt(string $plaintext, string $aad = ''): string
    {
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->binaryKey,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $aad,
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            if (\Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                Log::critical('Operasi openssl_encrypt AES-256-GCM mengalami kegagalan.');
            }
            throw new RuntimeException('Gagal mengenkripsi data sensitif.');
        }

        $binaryEnvelope = chr(self::VERSION) . $nonce . $tag . $ciphertext;

        return base64_encode($binaryEnvelope);
    }

    /**
     * Mendekripsi ciphertext envelope Base64 dan memverifikasi integritas via tag otentikasi GCM.
     * Mengembalikan null jika data rusak, dimanipulasi (bit-flip), atau AAD tidak cocok (fail-safe).
     *
     * @param string $envelopeBase64 String ciphertext ter-encode Base64
     * @param string $aad Additional Authenticated Data yang wajib cocok persis saat enkripsi
     * @return string|null Plaintext asli jika valid, atau null jika integritas gagal
     */
    public function decrypt(string $envelopeBase64, string $aad = ''): ?string
    {
        $raw = base64_decode($envelopeBase64, true);

        $minHeaderLength = 1 + self::NONCE_LENGTH + self::TAG_LENGTH;
        if ($raw === false || strlen($raw) < $minHeaderLength) {
            return null;
        }

        $version = ord($raw[0]);
        if ($version !== self::VERSION) {
            if (\Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                Log::warning('Versi envelope kriptografi tidak didukung: ' . $version);
            }
            return null;
        }

        $nonce = substr($raw, 1, self::NONCE_LENGTH);
        $tag = substr($raw, 1 + self::NONCE_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, $minHeaderLength);

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->binaryKey,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $aad
        );

        if ($decrypted === false) {
            // Verifikasi tag GCM gagal atau AAD tidak cocok: potensi serangan manipulasi data
            if (\Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                Log::warning('Verifikasi integritas AES-256-GCM gagal: auth tag atau AAD tidak valid.');
            }
            return null;
        }

        return $decrypted;
    }

    /**
     * Menghasilkan kunci 256-bit baru dalam format heksadesimal 64 karakter.
     */
    public static function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Mengekstrak nonce biner dari envelope Base64 (untuk audit/pengujian kriptografi).
     */
    public function extractNonce(string $envelopeBase64): ?string
    {
        $raw = base64_decode($envelopeBase64, true);
        if ($raw === false || strlen($raw) < 13) {
            return null;
        }
        return substr($raw, 1, self::NONCE_LENGTH);
    }

    /**
     * Mengekstrak authentication tag biner dari envelope Base64 (untuk audit/pengujian).
     */
    public function extractTag(string $envelopeBase64): ?string
    {
        $raw = base64_decode($envelopeBase64, true);
        if ($raw === false || strlen($raw) < 29) {
            return null;
        }
        return substr($raw, 13, self::TAG_LENGTH);
    }
}
