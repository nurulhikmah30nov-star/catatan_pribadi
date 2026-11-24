<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Versi Rentan (Loopback Only) - CatatanSecure</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Banner Peringatan Keras Isolasi Loopback -->
    <header class="bg-red-700 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <x-heroicon-s-exclamation-triangle class="w-8 h-8 text-yellow-300 flex-shrink-0" />
                    <div>
                        <h1 class="text-xl font-bold uppercase tracking-wide">
                            LINGKUNGAN DEMONSTRASI VERSI RENTAN (VULNERABLE LAB)
                        </h1>
                        <p class="text-xs text-red-100 font-mono mt-0.5">
                            Isolasi Wajib: Loopback Localhost (127.0.0.1:8001) | Sesi Terpisah: catatan_vuln_session
                        </p>
                    </div>
                </div>
                <div class="text-xs bg-red-900 border border-red-500 rounded px-3 py-1.5 font-mono">
                    Tugas KSK: Syarat 8 (SQLi), Syarat 9 (IDOR), Syarat 10 (XSS)
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        @if(session('status'))
            <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-900 rounded-r shadow-sm">
                <p class="font-medium text-sm">{{ session('status') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-900 rounded-r shadow-sm">
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Panel Status Sesi Pengguna & Switcher Skenario -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                <x-heroicon-o-user class="w-5 h-5 mr-2 text-indigo-600" />
                Simulasi Sesi Pengguna (Untuk Pengujian IDOR)
            </h2>
            <p class="text-sm text-gray-600 mb-4">
                Pilih pengguna untuk login instan guna menguji pembuktian IDOR (membaca catatan milik pengguna lain).
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-gray-700">Sesi Aktif:</span>
                @if(Auth::check())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        {{ Auth::user()->name }} ({{ Auth::user()->email }}) [{{ Auth::user()->role }}]
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        Belum Login (Guest)
                    </span>
                @endif

                <div class="ml-auto flex flex-wrap gap-2">
                    @foreach($users as $u)
                        <a href="{{ route('vuln.login.as', $u->email) }}"
                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs font-medium bg-gray-50 text-gray-700 hover:bg-gray-100">
                            Switch ke {{ $u->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tiga Modul Celah Keamanan Sengaja -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card 1: SQL Injection (Syarat 8) -->
            <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-red-100 text-red-800">
                            Syarat 8 (SEC-08)
                        </span>
                        <x-heroicon-o-code-bracket-square class="w-6 h-6 text-red-600" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">SQL Injection (SQLi)</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Pencarian judul catatan menggunakan string concatenation langsung tanpa prepared statement:
                        <code class="block bg-gray-100 p-1.5 rounded text-red-700 font-mono text-2xs mt-1">
                            "SELECT * FROM notes WHERE title LIKE '%" . $query . "%'"
                        </code>
                    </p>
                </div>
                <a href="{{ route('vuln.notes.search') }}"
                   class="w-full text-center py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow">
                    Buka Lab Uji SQL Injection &rarr;
                </a>
            </div>

            <!-- Card 2: Insecure Direct Object Reference (Syarat 9) -->
            <div class="bg-white rounded-lg shadow-sm border border-orange-200 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-orange-100 text-orange-800">
                            Syarat 9 (SEC-09)
                        </span>
                        <x-heroicon-o-lock-open class="w-6 h-6 text-orange-600" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">IDOR (Broken Object Authorization)</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Catatan diakses langsung via parameter URL <code class="font-mono text-orange-700">/notes/{id}</code> menggunakan <code class="font-mono">findOrFail($id)</code> tanpa pemeriksaan kepemilikan pengguna (<code class="font-mono">user_id == Auth::id()</code>).
                    </p>
                </div>
                <a href="#daftar-catatan-idor"
                   class="w-full text-center py-2 px-4 rounded bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow">
                    Lihat Daftar Catatan Terbuka &rarr;
                </a>
            </div>

            <!-- Card 3: Cross-Site Scripting (Syarat 10) -->
            <div class="bg-white rounded-lg shadow-sm border border-yellow-200 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-yellow-100 text-yellow-800">
                            Syarat 10 (SEC-10)
                        </span>
                        <x-heroicon-o-document-text class="w-6 h-6 text-yellow-600" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Cross-Site Scripting (Stored XSS)</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Kolom komentar publik dirender tanpa pengamanan kontekstual menggunakan sintaks Blade unescaped:
                        <code class="block bg-gray-100 p-1.5 rounded text-yellow-800 font-mono text-2xs mt-1">
                            &#123;&#33;&#33; $note-&gt;public_comment &#33;&#33;&#125;
                        </code>
                        Script berbahaya dieksekusi langsung di browser.
                    </p>
                </div>
                <a href="#daftar-catatan-idor"
                   class="w-full text-center py-2 px-4 rounded bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-semibold shadow">
                    Buka Catatan untuk Uji XSS &rarr;
                </a>
            </div>

        </div>

        <!-- Bagian Daftar Catatan Versi Rentan (Untuk Verifikasi IDOR dan XSS) -->
        <div id="daftar-catatan-idor" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Daftar Semua Catatan (Tanpa Filter Kepemilikan)</h3>
                    <p class="text-xs text-gray-500">Versi rentan mengizinkan akses ke ID catatan manapun tanpa pemeriksaan otorisasi.</p>
                </div>
                <span class="text-xs font-mono text-gray-500">Total: {{ $notes->count() }} Catatan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-100 text-gray-600 font-mono text-xs">
                        <tr>
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Pemilik (User)</th>
                            <th class="px-6 py-3">Judul Catatan</th>
                            <th class="px-6 py-3">Komentar Publik (Pratinjau)</th>
                            <th class="px-6 py-3 text-right">Aksi Rentan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($notes as $note)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-gray-700">#{{ $note->id }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $note->user_id === 2 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $note->user->name ?? 'User ID '.$note->user_id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $note->title }}</td>
                                <td class="px-6 py-4 text-xs text-gray-500 font-mono truncate max-w-xs">
                                    {{ Str::limit($note->public_comment, 40) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('vuln.notes.show', $note->id) }}"
                                       class="inline-flex items-center px-3 py-1 rounded bg-red-100 hover:bg-red-200 text-red-800 text-xs font-medium">
                                        Eksploitasi IDOR / XSS &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Belum ada catatan pada database rentan. Jalankan seeder.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="mt-12 py-6 bg-gray-200 border-t border-gray-300 text-center text-xs text-gray-600 font-mono">
        CatatanSecure Lab Rentan &copy; 2026 - Tugas Keamanan Sistem Komputer (NIM: 230170187, Digit 7)
    </footer>

</body>
</html>
