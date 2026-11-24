<?php

namespace App\Console\Commands;

use App\Services\Security\AesGcmEncryptionService;
use Illuminate\Console\Command;

class GenerateCipherKeyCommand extends Command
{
    /**
     * Nama dan signature perintah artisan.
     *
     * @var string
     */
    protected $signature = 'cipher:generate-key {--show : Tampilkan kunci saja tanpa mengubah file .env}';

    /**
     * Deskripsi perintah artisan.
     *
     * @var string
     */
    protected $description = 'Menghasilkan kunci 256-bit baru untuk layanan enkripsi AES-256-GCM (SEC-03)';

    /**
     * Eksekusi perintah console.
     */
    public function handle(): int
    {
        $key = AesGcmEncryptionService::generateKey();

        if ($this->option('show')) {
            $this->line('<comment>' . $key . '</comment>');
            return self::SUCCESS;
        }

        $envFile = base_path('.env');

        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            if (preg_match('/^APP_CIPHER_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_CIPHER_KEY=.*$/m', 'APP_CIPHER_KEY=' . $key, $envContent);
            } else {
                $envContent .= PHP_EOL . 'APP_CIPHER_KEY=' . $key . PHP_EOL;
            }

            file_put_contents($envFile, $envContent);
            $this->info('Kunci enkripsi AES-256-GCM berhasil disimpan ke .env:');
            $this->line('<comment>APP_CIPHER_KEY=' . $key . '</comment>');
        } else {
            $this->warn('File .env tidak ditemukan. Kunci yang dihasilkan:');
            $this->line('<comment>' . $key . '</comment>');
        }

        return self::SUCCESS;
    }
}
