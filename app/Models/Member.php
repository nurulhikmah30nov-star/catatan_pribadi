<?php

namespace App\Models;

use App\Services\Security\AesGcmEncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'phone_encrypted',
        'phone_number',
        'address',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'phone_number',
    ];

    /**
     * Cache sementara nilai plaintext nomor telepon sebelum disimpan.
     */
    protected ?string $pendingPhoneNumber = null;

    /**
     * Booted model callback untuk memastikan enkripsi sebelum penyimpanan.
     */
    protected static function booted(): void
    {
        static::saving(function (Member $member) {
            if ($member->pendingPhoneNumber !== null) {
                $member->encryptPhoneNumber();
            }
        });
    }

    /**
     * Relasi belongsTo dengan model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mutator untuk atribut user_id.
     * Jika ada pending phone_number, enkripsi ulang dengan AAD user_id yang baru.
     */
    public function setUserIdAttribute($value): void
    {
        $this->attributes['user_id'] = $value;
        if ($this->pendingPhoneNumber !== null) {
            $this->encryptPhoneNumber();
        }
    }

    /**
     * Mutator otomatis untuk nomor telepon (SEC-03).
     * Mengenkripsi nomor telepon menggunakan AES-256-GCM ke kolom phone_encrypted.
     */
    public function setPhoneNumberAttribute(?string $value): void
    {
        $this->pendingPhoneNumber = $value;
        $this->encryptPhoneNumber();
    }

    /**
     * Mengeksekusi enkripsi nomor telepon menggunakan AesGcmEncryptionService.
     */
    protected function encryptPhoneNumber(): void
    {
        if ($this->pendingPhoneNumber === null || $this->pendingPhoneNumber === '') {
            $this->attributes['phone_encrypted'] = null;
            return;
        }

        $service = app(AesGcmEncryptionService::class);
        $aad = (string) ($this->attributes['user_id'] ?? '');
        $this->attributes['phone_encrypted'] = $service->encrypt($this->pendingPhoneNumber, $aad);
    }

    /**
     * Accessor otomatis untuk nomor telepon (SEC-03).
     * Mendekripsi isi kolom phone_encrypted ke plaintext secara transparan.
     */
    public function getPhoneNumberAttribute(): ?string
    {
        if (empty($this->attributes['phone_encrypted'])) {
            return null;
        }

        $service = app(AesGcmEncryptionService::class);
        $aad = (string) ($this->attributes['user_id'] ?? '');

        $decrypted = $service->decrypt($this->attributes['phone_encrypted'], $aad);

        // Fallback jika dienkripsi tanpa user_id (misal sebelum user_id terpasang)
        if ($decrypted === null && $aad !== '') {
            $decrypted = $service->decrypt($this->attributes['phone_encrypted'], '');
        }

        return $decrypted ?? '[Data Tidak Dapat Didekripsi]';
    }
}
