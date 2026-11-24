@extends('layouts.app')

@section('title', 'Dashboard')
@section('container-class', 'max-w-7xl')

@section('content')
<div class="space-y-6">
    <!-- Hero Banner (Kartu Gelap: #0F172A) Sesuai Spesifikasi design.md -->
    <div class="bg-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg border border-slate-800 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-xs font-medium text-emerald-400 mb-4">
                <x-heroicon-s-shield-check class="w-4 h-4 text-emerald-400" />
                <span>Dashboard Catatan &bull; Sesi Terenkripsi Aktif &bull; {{ Auth::user()->role === 'admin' ? 'Hak Akses Administrator' : 'Hak Akses Pengguna' }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Selamat Datang, {{ Auth::user()->name }}!
            </h1>
            <p class="mt-2 text-sm text-slate-300 leading-relaxed">
                CatatanSecure — Sistem Catatan Pribadi Terenkripsi menggunakan AES-256-GCM AEAD, dilindungi dari SQLi, XSS, dan IDOR. Semua catatan pribadi Anda tersimpan aman dan hanya dapat diakses oleh Anda sendiri.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('notes.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                    <x-heroicon-o-document-text class="w-4 h-4 mr-2" />
                    Kelola Catatan
                </a>
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white shadow-sm transition">
                        <x-heroicon-o-shield-check class="w-4 h-4 mr-2" />
                        Panel Administrator
                    </a>
                @endif
            </div>
        </div>

        <!-- Background Decorative Shield Pattern -->
        <div class="absolute right-0 bottom-0 translate-x-8 translate-y-8 opacity-10 pointer-events-none">
            <x-heroicon-s-shield-check class="w-72 h-72 text-white" />
        </div>
    </div>

    <!-- Kartu Statistik Pastel (Mint, Peach, Sky) Sesuai Bab 5.1 design.md -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <x-stat-card variant="mint"
                     title="Catatan Pribadi"
                     :value="$notesCount ?? 0"
                     caption="Total catatan yang Anda miliki">
            <x-slot:icon>
                <x-heroicon-o-document-text class="w-6 h-6 text-emerald-700" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card variant="peach"
                     title="Catatan Terenkripsi"
                     :value="$encryptedCount ?? 0"
                     caption="Catatan dengan isi terenkripsi AES-256-GCM">
            <x-slot:icon>
                <x-heroicon-o-lock-closed class="w-6 h-6 text-orange-700" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card variant="sky"
                     title="Catatan Diarsipkan"
                     :value="$archivedCount ?? 0"
                     caption="Catatan yang telah diarsipkan">
            <x-slot:icon>
                <x-heroicon-o-archive-box class="w-6 h-6 text-blue-700" />
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Profile & Security Summary Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Account Details Card -->
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                    <x-heroicon-o-user class="w-5 h-5 text-emerald-600" />
                    <span>Profil Pengguna & Status Akun</span>
                </h2>
                <span class="text-xs text-gray-400">ID Akun: #{{ Auth::user()->id }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</span>
                    <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat Email</span>
                    <span class="font-semibold text-gray-800">{{ Auth::user()->email }}</span>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Peran Otorisasi</span>
                    <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-semibold {{ Auth::user()->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung Sejak</span>
                    <span class="font-semibold text-gray-800">
                        {{ Auth::user()->created_at ? Auth::user()->created_at->format('d M Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Security Hardening Badges Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                <x-heroicon-s-shield-check class="w-5 h-5 text-emerald-600" />
                <span>Spesifikasi Keamanan</span>
            </h2>

            <ul class="space-y-3 text-xs text-gray-700">
                <li class="flex items-start space-x-2.5">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <span><strong>Bcrypt 12 Rounds:</strong> Hash kata sandi dengan salt acak bawaan (SEC-02).</span>
                </li>
                <li class="flex items-start space-x-2.5">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <span><strong>AES-256-GCM AEAD:</strong> Enkripsi isi catatan dengan nonce 12 byte &amp; tag 16 byte (SEC-03).</span>
                </li>
                <li class="flex items-start space-x-2.5">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <span><strong>Anti-SQLi &amp; XSS:</strong> Parameter binding dan escaping kontekstual Blade (SEC-08, SEC-09).</span>
                </li>
                <li class="flex items-start space-x-2.5">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <span><strong>Anti-IDOR Policy:</strong> Validasi kepemilikan catatan sisi server — 403 Forbidden (SEC-10).</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
