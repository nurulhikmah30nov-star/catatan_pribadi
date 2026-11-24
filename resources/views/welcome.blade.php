<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CatatanSecure') }} — Catatan Pribadi Terenkripsi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased font-sans flex flex-col">

    <!-- Header / Navbar -->
    <header class="bg-white border-b border-gray-200 shadow-xs sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex items-center space-x-2.5">
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <x-heroicon-o-document-text class="w-6 h-6" />
                </span>
                <span class="text-lg font-bold tracking-tight text-gray-900">
                    {{ config('app.name', 'CatatanSecure') }}
                </span>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-emerald-700 hover:bg-gray-50 border border-gray-200 hover:border-emerald-200 transition">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white transition focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                Daftar Sekarang
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="bg-slate-900 py-20 px-4 text-white relative overflow-hidden">
            <div class="max-w-4xl mx-auto relative z-10 text-center">
                <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-xs font-medium text-emerald-400 mb-6">
                    <x-heroicon-s-shield-check class="w-4 h-4" />
                    <span>Aplikasi Catatan Pribadi Terenkripsi &bull; Tugas Keamanan Sistem Komputer</span>
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                    <span class="text-white">Catatan Pribadi</span><br>
                    <span class="text-emerald-400">Aman &amp; Terenkripsi</span>
                </h1>
                <p class="mt-6 text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    CatatanSecure mengamankan setiap catatan pribadi Anda dengan enkripsi <strong class="text-white">AES-256-GCM</strong>, hash password <strong class="text-white">bcrypt</strong> 12 rounds, dan proteksi berlapis terhadap SQLi, XSS, dan IDOR.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center px-6 py-3 rounded-xl text-sm font-semibold bg-emerald-500 hover:bg-emerald-400 text-white transition shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            <x-heroicon-o-document-text class="w-5 h-5 mr-2" />
                            Buka Dashboard Saya
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center px-6 py-3 rounded-xl text-sm font-semibold bg-emerald-500 hover:bg-emerald-400 text-white transition shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            <x-heroicon-o-user-plus class="w-5 h-5 mr-2" />
                            Mulai Gratis
                        </a>
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center px-6 py-3 rounded-xl text-sm font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 mr-2" />
                            Masuk ke Akun
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Decorative background pattern -->
            <div class="absolute right-0 top-0 translate-x-16 -translate-y-16 opacity-5 pointer-events-none">
                <x-heroicon-s-shield-check class="w-96 h-96 text-emerald-400" />
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-16 px-4 bg-white">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-center text-2xl font-bold text-gray-900 mb-10">
                    10 Syarat Keamanan Terpenuhi
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div class="p-5 rounded-xl bg-emerald-50 border border-emerald-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <x-heroicon-o-lock-closed class="w-5 h-5 text-emerald-700" />
                            <span class="font-semibold text-emerald-900 text-sm">Enkripsi AES-256-GCM</span>
                        </div>
                        <p class="text-xs text-emerald-800 leading-relaxed">Isi catatan pribadi dienkripsi dengan nonce 12 byte unik per enkripsi dan tag autentikasi 16 byte (SEC-03).</p>
                    </div>

                    <div class="p-5 rounded-xl bg-orange-50 border border-orange-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <x-heroicon-o-key class="w-5 h-5 text-orange-700" />
                            <span class="font-semibold text-orange-900 text-sm">Hash Bcrypt 12 Rounds</span>
                        </div>
                        <p class="text-xs text-orange-800 leading-relaxed">Kata sandi di-hash dengan bcrypt dan salt acak bawaan — tidak pernah disimpan sebagai plaintext (SEC-02).</p>
                    </div>

                    <div class="p-5 rounded-xl bg-blue-50 border border-blue-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <x-heroicon-o-shield-check class="w-5 h-5 text-blue-700" />
                            <span class="font-semibold text-blue-900 text-sm">Anti-IDOR (SEC-10)</span>
                        </div>
                        <p class="text-xs text-blue-800 leading-relaxed">Setiap akses ke catatan divalidasi kepemilikannya di sisi server — akses catatan orang lain menghasilkan 403 Forbidden.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-purple-50 border border-purple-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 text-purple-700" />
                            <span class="font-semibold text-purple-900 text-sm">Anti-SQLi (SEC-08)</span>
                        </div>
                        <p class="text-xs text-purple-800 leading-relaxed">Pencarian catatan menggunakan parameter binding Laravel — tidak ada interpolasi string SQL mentah.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-rose-50 border border-rose-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <x-heroicon-o-code-bracket class="w-5 h-5 text-rose-700" />
                            <span class="font-semibold text-rose-900 text-sm">Anti-XSS (SEC-09)</span>
                        </div>
                        <p class="text-xs text-rose-800 leading-relaxed">Output encoding kontekstual Blade otomatis — payload <code class="font-mono bg-rose-100 px-1 rounded">&lt;script&gt;</code> ditampilkan sebagai teks biasa.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-teal-50 border border-teal-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <x-heroicon-o-users class="w-5 h-5 text-teal-700" />
                            <span class="font-semibold text-teal-900 text-sm">Dua Peran Pengguna</span>
                        </div>
                        <p class="text-xs text-teal-800 leading-relaxed">Admin dapat melihat &amp; mengelola semua catatan; Pengguna Biasa hanya akses catatan miliknya sendiri.</p>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500">
        <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div class="flex items-center space-x-2 text-emerald-700 font-medium">
                <x-heroicon-s-shield-check class="w-4 h-4 text-emerald-600" />
                <span>Terlindungi Enkripsi AES-256-GCM (SEC-03) &bull; Proteksi IDOR, SQLi, XSS</span>
            </div>
            <div>
                &copy; {{ date('Y') }} {{ config('app.name', 'CatatanSecure') }} &bull;
                Tugas Keamanan Sistem Komputer (Nurul Hikmah / 230170187)
            </div>
        </div>
    </footer>

</body>
</html>
