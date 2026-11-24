<?php

namespace App\Providers;

use App\Models\Note;
use App\Policies\NotePolicy;
use App\Services\Security\AesGcmEncryptionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Mendaftarkan AesGcmEncryptionService sebagai singleton agar kunci
     * hanya diinisialisasi sekali per request siklus hidup (TKT-005 / SEC-03).
     */
    public function register(): void
    {
        $this->app->singleton(AesGcmEncryptionService::class, function () {
            return new AesGcmEncryptionService(config('cipher.key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Note::class, NotePolicy::class);
    }
}
