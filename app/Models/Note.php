<?php

namespace App\Models;

use App\Services\Security\AesGcmEncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Note extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Tema: Catatan Pribadi (NIM 230170187, Digit 7)
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',          // Menyimpan ciphertext Base64 AES-256-GCM (SEC-03)
        'category',
        'public_comment',   // Teks bebas untuk simulasi pengujian XSS (Syarat 9)
        'is_archived',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Relasi belongsTo dengan model User (pemilik catatan pribadi).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Periksa apakah sebuah string sudah merupakan envelope ciphertext terenkripsi AES-256-GCM.
     */
    public function isEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false || strlen($decoded) < 29) {
            return false;
        }

        // Versi envelope harus 0x01
        return ord($decoded[0]) === 1;
    }

    /**
     * Mutator untuk kolom `content` (SEC-03 Enkripsi Transparan).
     * Saat disimpan, plaintext dienkripsi secara transparan menggunakan AES-256-GCM
     * dengan nonce acak 12-byte baru dan AAD terikat ke konteks catatan / user.
     */
    public function setContentAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['content'] = '';
            return;
        }

        // Jika sudah berupa ciphertext envelope terenkripsi, simpan langsung
        if ($this->isEncrypted($value)) {
            $this->attributes['content'] = $value;
            return;
        }

        // Enkripsi plaintext menggunakan AES-256-GCM
        $service = app(AesGcmEncryptionService::class);
        $userId = $this->attributes['user_id'] ?? Auth::id() ?? null;
        $aad = $userId ? 'note-' . $userId : '';

        $this->attributes['content'] = $service->encrypt($value, $aad);
    }

    /**
     * Accessor untuk kolom `content` (SEC-03 Dekripsi Transparan).
     * Mendekripsi ciphertext secara transparan di memori saat diakses.
     * Mengembalikan pesan peringatan aman jika data dimanipulasi (tampered) tanpa memicu crash.
     */
    public function getContentAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return '';
        }

        // Jika bukan format ciphertext terenkripsi, kembalikan apa adanya
        if (! $this->isEncrypted($value)) {
            return $value;
        }

        $service = app(AesGcmEncryptionService::class);
        $userId = $this->attributes['user_id'] ?? Auth::id() ?? null;

        // 1. Coba dekripsi dengan AAD standar: 'note-' . $userId
        if ($userId) {
            $decrypted = $service->decrypt($value, 'note-' . $userId);
            if ($decrypted !== null) {
                return $decrypted;
            }

            // 2. Fallback AAD variasi numerik string: (string) $userId
            $decrypted = $service->decrypt($value, (string) $userId);
            if ($decrypted !== null) {
                return $decrypted;
            }
        }

        // 3. Fallback AAD kosong
        $decrypted = $service->decrypt($value, '');
        if ($decrypted !== null) {
            return $decrypted;
        }

        // Jika seluruh dekripsi gagal (misal ciphertext di-tamper atau AAD mismatch)
        return '[Peringatan: Integritas Data Rusak / Ciphertext Termodifikasi]';
    }

    /**
     * Ambil ciphertext mentah langsung dari database (tanpa melalui dekripsi).
     * Sangat berguna untuk verifikasi dan bukti empiris SEC-03.
     */
    public function getRawContentAttribute(): ?string
    {
        return $this->attributes['content'] ?? null;
    }
}
