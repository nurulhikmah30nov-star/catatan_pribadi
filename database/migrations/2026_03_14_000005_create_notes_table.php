<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tema: Catatan Pribadi (NIM 230170187, Digit 7)
     * Kolom public_comment digunakan untuk simulasi pengujian XSS (Syarat 9).
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            // content menyimpan ciphertext Base64 AES-256-GCM (SEC-03)
            $table->text('content');
            $table->string('category')->default('Pribadi');
            // public_comment: teks bebas untuk simulasi pengujian XSS (Syarat 9)
            $table->text('public_comment')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
