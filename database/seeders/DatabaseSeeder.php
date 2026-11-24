<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Akun fiktif untuk pengujian CatatanSecure (Tugas Keamanan Sistem Komputer).
     * Tema: Catatan Pribadi (NIM 230170187, Digit 7)
     *
     * Akun:
     *  - admin@catatan.local  / AdminRahasia123!  (role: admin)
     *  - nurul@catatan.local  / SamaPersis123!    (role: user)
     *  - budi@catatan.local   / SamaPersis123!    (role: user)
     */
    public function run(): void
    {
        // ---------------------------------------------------------------
        // 1. Akun Admin
        // ---------------------------------------------------------------
        $admin = User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@catatan.local',
            'password' => Hash::make('AdminRahasia123!'),
            'role'     => 'admin',
        ]);

        // ---------------------------------------------------------------
        // 2. Akun Pengguna A — Nurul
        // ---------------------------------------------------------------
        $nurul = User::create([
            'name'     => 'Nurul Hikmah',
            'email'    => 'nurul@catatan.local',
            'password' => Hash::make('SamaPersis123!'),
            'role'     => 'user',
        ]);

        // ---------------------------------------------------------------
        // 3. Akun Pengguna B — Budi
        // ---------------------------------------------------------------
        $budi = User::create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@catatan.local',
            'password' => Hash::make('SamaPersis123!'),
            'role'     => 'user',
        ]);

        // ---------------------------------------------------------------
        // 4. Catatan Fiktif untuk Nurul (content otomatis dienkripsi oleh model)
        // ---------------------------------------------------------------
        Note::create([
            'user_id'        => $nurul->id,
            'title'          => 'Catatan Kuliah Keamanan Sistem',
            'content'        => 'Materi kriptografi: AES-256-GCM menggunakan nonce 12 byte dan tag 16 byte. Pastikan setiap enkripsi menggunakan nonce baru yang acak.',
            'category'       => 'Kuliah',
            'public_comment' => 'Catatan dari pertemuan ke-5, sangat penting!',
            'is_archived'    => false,
        ]);

        Note::create([
            'user_id'        => $nurul->id,
            'title'          => 'Rencana Skripsi',
            'content'        => 'Topik skripsi: Implementasi enkripsi end-to-end pada aplikasi pesan instan berbasis web. Pembimbing: Dr. Ahmad Fauzi.',
            'category'       => 'Pribadi',
            'public_comment' => 'Draft awal, belum final.',
            'is_archived'    => false,
        ]);

        Note::create([
            'user_id'        => $nurul->id,
            'title'          => 'Password Manager Darurat',
            'content'        => 'CATATAN RAHASIA: Kode brankas: 4-8-15-16-23-42. Hanya untuk pengujian IDOR — bukan password nyata.',
            'category'       => 'Rahasia',
            'public_comment' => null,
            'is_archived'    => false,
        ]);

        // ---------------------------------------------------------------
        // 5. Catatan Fiktif untuk Budi (content otomatis dienkripsi oleh model)
        // ---------------------------------------------------------------
        Note::create([
            'user_id'        => $budi->id,
            'title'          => 'Agenda Kerja Mingguan',
            'content'        => 'Senin: Review kode backend. Selasa: Meeting tim produk. Rabu: Deploy ke staging. Kamis: QA testing. Jumat: Retrospektif.',
            'category'       => 'Pekerjaan',
            'public_comment' => 'Update setiap Jumat malam.',
            'is_archived'    => false,
        ]);

        Note::create([
            'user_id'        => $budi->id,
            'title'          => 'Ide Proyek Sampingan',
            'content'        => 'Ide: Aplikasi manajemen keuangan keluarga dengan fitur enkripsi data sensitif. Stack: Laravel + SQLite + Tailwind CSS.',
            'category'       => 'Pribadi',
            'public_comment' => 'Masih konsep awal, perlu riset lebih lanjut.',
            'is_archived'    => false,
        ]);

        Note::create([
            'user_id'        => $budi->id,
            'title'          => 'Catatan Arsip Lama',
            'content'        => 'Catatan ini sudah diarsip. Berisi informasi lama yang sudah tidak relevan tetapi disimpan sebagai referensi historis.',
            'category'       => 'Pekerjaan',
            'public_comment' => 'Catatan lama dari proyek 2024.',
            'is_archived'    => true,
        ]);

        $this->command->info('✅ Seeder berhasil! Akun fiktif CatatanSecure:');
        $this->command->info('   admin@catatan.local  / AdminRahasia123!  (admin)');
        $this->command->info('   nurul@catatan.local  / SamaPersis123!    (user)');
        $this->command->info('   budi@catatan.local   / SamaPersis123!    (user)');
        $this->command->info('   Total catatan: 6 (3 Nurul + 3 Budi), semua otomatis terenkripsi AES-256-GCM');
    }
}
