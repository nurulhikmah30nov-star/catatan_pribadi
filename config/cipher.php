<?php

/**
 * Konfigurasi Layanan Enkripsi Data Sensitif
 * CatatanSecure — TKT-005 (SEC-03: AES-256-GCM AEAD)
 *
 * Kunci wajib disimpan di .env sebagai APP_CIPHER_KEY (64 karakter hex = 32 byte = 256 bit).
 * JANGAN pernah menyimpan nilai kunci langsung di file ini.
 *
 * @see App\Services\Security\AesGcmEncryptionService
 * @see NIST SP 800-38D (spesifikasi GCM)
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Algoritma Cipher
    |--------------------------------------------------------------------------
    |
    | Algoritma yang digunakan oleh AesGcmEncryptionService.
    | Harus selalu 'aes-256-gcm'. Jangan ganti ke CBC atau ECB.
    |
    */
    'algorithm' => 'aes-256-gcm',

    /*
    |--------------------------------------------------------------------------
    | Kunci Enkripsi 256-bit (APP_CIPHER_KEY)
    |--------------------------------------------------------------------------
    |
    | Kunci rahasia 256-bit dalam format heksadesimal 64 karakter.
    | Buat kunci baru dengan: php artisan cipher:generate-key
    | Simpan di .env sebagai: APP_CIPHER_KEY=<64-char-hex>
    |
    | PERINGATAN: Jika kunci ini diubah, seluruh data terenkripsi yang
    | ada di database TIDAK DAPAT didekripsi menggunakan kunci baru.
    |
    */
    'key' => env('APP_CIPHER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Panjang Nonce (IV)
    |--------------------------------------------------------------------------
    |
    | Panjang nonce dalam byte. NIST SP 800-38D merekomendasikan 96 bit (12 byte)
    | sebagai ukuran nonce default untuk GCM karena efisiensi komputasinya.
    |
    */
    'nonce_length' => 12,

    /*
    |--------------------------------------------------------------------------
    | Panjang Authentication Tag
    |--------------------------------------------------------------------------
    |
    | Panjang tag autentikasi GCM dalam byte. 128 bit (16 byte) adalah
    | ukuran tag maksimum dan direkomendasikan untuk keamanan tertinggi.
    |
    */
    'tag_length' => 16,

];
