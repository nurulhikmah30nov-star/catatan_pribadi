<?php

namespace Tests\Unit;

use App\Services\Security\AesGcmEncryptionService;
use Tests\TestCase;
use RuntimeException;

class AesGcmEncryptionTest extends TestCase
{
    protected string $testKey;
    protected AesGcmEncryptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // 64 karakter hex = 32 byte = 256 bit
        $this->testKey = '73870f4cf8e910af98aabe7524cf38ad04e0b90560d393a36c20c8781ca39958';
        $this->service = new AesGcmEncryptionService($this->testKey);
    }

    /**
     * Syarat 3 (SEC-03): Memastikan enkripsi dan dekripsi data teks sensitif berhasil dengan AAD yang cocok.
     */
    public function test_encrypt_and_decrypt_successfully(): void
    {
        $plaintext = '081234567890';
        $aad = 'user-1';

        $encrypted = $this->service->encrypt($plaintext, $aad);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);

        $decrypted = $this->service->decrypt($encrypted, $aad);
        $this->assertEquals($plaintext, $decrypted, 'Hasil dekripsi wajib sama persis dengan plaintext semula.');
    }

    /**
     * Syarat 3 (SEC-03): Memastikan dua operasi enkripsi terhadap plaintext identik menghasilkan ciphertext berbeda (nonce 12-byte acak baru).
     */
    public function test_encryption_produces_unique_ciphertexts_due_to_random_nonce(): void
    {
        $plaintext = '081234567890';
        $aad = 'user-1';

        $encrypted1 = $this->service->encrypt($plaintext, $aad);
        $encrypted2 = $this->service->encrypt($plaintext, $aad);

        // Ciphertext harus berbeda secara visual
        $this->assertNotEquals($encrypted1, $encrypted2, 'Enkripsi berulang terhadap plaintext identik wajib menghasilkan ciphertext berbeda.');

        // Nonce yang diekstrak harus berbeda dan tepat berukuran 12 byte
        $nonce1 = $this->service->extractNonce($encrypted1);
        $nonce2 = $this->service->extractNonce($encrypted2);

        $this->assertEquals(12, strlen($nonce1), 'Nonce biner wajib tepat 12 byte (96-bit).');
        $this->assertEquals(12, strlen($nonce2), 'Nonce biner wajib tepat 12 byte (96-bit).');
        $this->assertNotEquals($nonce1, $nonce2, 'Setiap enkripsi wajib menggunakan nonce unik acak baru.');
    }

    /**
     * Syarat 3 (SEC-03): Memastikan verifikasi AAD menolak dekripsi jika konteks ID entitas tidak cocok (mitigasi serangan transplantasi ciphertext).
     */
    public function test_decryption_with_mismatched_aad_fails_and_returns_null(): void
    {
        $plaintext = '081234567890';
        $aadAsli = 'user-1';
        $aadPalsu = 'user-2';

        $encrypted = $this->service->encrypt($plaintext, $aadAsli);

        // Dekripsi dengan AAD yang salah wajib mengembalikan null (fail-safe)
        $decrypted = $this->service->decrypt($encrypted, $aadPalsu);
        $this->assertNull($decrypted, 'Dekripsi dengan AAD berbeda wajib gagal dan mengembalikan null.');
    }

    /**
     * Syarat 3 (SEC-03): Memastikan auth tag 16-byte menolak data yang dimanipulasi (perubahan 1 bit ciphertext menghasilkan null).
     */
    public function test_decryption_with_tampered_ciphertext_fails_and_returns_null(): void
    {
        $plaintext = '081234567890';
        $aad = 'user-1';

        $encrypted = $this->service->encrypt($plaintext, $aad);
        $raw = base64_decode($encrypted);

        // Modifikasi (flip) satu bit pada bagian ciphertext di posisi akhir
        $tamperedRaw = substr($raw, 0, -1) . chr(ord(substr($raw, -1)) ^ 0x01);
        $tamperedBase64 = base64_encode($tamperedRaw);

        $decrypted = $this->service->decrypt($tamperedBase64, $aad);
        $this->assertNull($decrypted, 'Ciphertext yang dimanipulasi 1 bit wajib gagal verifikasi auth tag dan mengembalikan null.');
    }

    /**
     * Syarat 3 (SEC-03): Memastikan struktur envelope biner mematuhi spesifikasi [Version 1B][Nonce 12B][Tag 16B][Ciphertext].
     */
    public function test_envelope_structure_matches_specification(): void
    {
        $plaintext = 'RahasiaPerpustakaan2026';
        $aad = 'audit-1';

        $encrypted = $this->service->encrypt($plaintext, $aad);
        $raw = base64_decode($encrypted);

        // 1 byte Version (0x01)
        $version = ord($raw[0]);
        $this->assertEquals(1, $version, 'Versi envelope wajib bernilai 1 (0x01).');

        // 12 bytes Nonce
        $nonce = substr($raw, 1, 12);
        $this->assertEquals(12, strlen($nonce));

        // 16 bytes Auth Tag
        $tag = substr($raw, 13, 16);
        $this->assertEquals(16, strlen($tag));

        // Total panjang biner: 1 + 12 + 16 + strlen(plaintext)
        $expectedLength = 1 + 12 + 16 + strlen($plaintext);
        $this->assertEquals($expectedLength, strlen($raw), 'Panjang total envelope biner harus sesuai spesifikasi format.');
    }

    /**
     * Syarat 3 (SEC-03): Memastikan kegagalan dekripsi pada payload korup atau terpotong ditangani secara aman.
     */
    public function test_decryption_with_corrupt_payload_returns_null(): void
    {
        $this->assertNull($this->service->decrypt('invalid-base64-string!@#'));
        $this->assertNull($this->service->decrypt(base64_encode('too-short'))); // Kurang dari 29 byte
    }

    /**
     * Syarat 3 (SEC-03): Memastikan service menolak kunci enkripsi yang tidak berukuran 256-bit.
     */
    public function test_service_rejects_invalid_key_length(): void
    {
        $this->expectException(RuntimeException::class);
        new AesGcmEncryptionService('kunci-pendek');
    }
}
