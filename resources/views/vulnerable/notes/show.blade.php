<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Catatan Rentan (IDOR & XSS) - CatatanSecure</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Header Banner -->
    <header class="bg-red-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-heroicon-s-exclamation-triangle class="w-7 h-7 text-yellow-300" />
                <div>
                    <h1 class="text-lg font-bold uppercase tracking-wide">
                        DEMONSTRASI SYARAT 9 (IDOR) & SYARAT 10 (XSS)
                    </h1>
                    <p class="text-xs text-red-200 font-mono">
                        Loopback Localhost (127.0.0.1:8001) | Akses Tanpa Otorisasi & Render Unescaped
                    </p>
                </div>
            </div>
            <a href="{{ route('vuln.index') }}"
               class="px-3 py-1.5 rounded bg-red-900 hover:bg-red-950 text-xs font-semibold text-white border border-red-700">
                &larr; Kembali ke Dashboard Lab
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-600 text-green-900 rounded-r shadow-sm">
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Kotak Analisis IDOR (Syarat 9) -->
        <div class="bg-white rounded-lg border-2 {{ (Auth::id() && Auth::id() !== $note->user_id) ? 'border-red-500 bg-red-50' : 'border-orange-200' }} p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-orange-100 text-orange-800">
                    Syarat 9: Uji Celah IDOR
                </span>
                <span class="text-xs font-mono text-gray-500">Note ID: #{{ $note->id }}</span>
            </div>

            <h2 class="text-base font-bold text-gray-900 mb-2">Pemeriksaan Kepemilikan Catatan (Broken Object Level Authorization)</h2>
            <div class="text-xs space-y-1 font-mono mb-4">
                <p><span class="text-gray-500">ID Catatan:</span> <strong class="text-gray-900">#{{ $note->id }}</strong></p>
                <p><span class="text-gray-500">Pemilik Asli Catatan (user_id):</span> <strong class="text-indigo-700">User ID {{ $note->user_id }} ({{ $note->user->name ?? 'Unknown' }} - {{ $note->user->email ?? '' }})</strong></p>
                <p><span class="text-gray-500">Pengguna yang Sedang Login (Auth::id()):</span>
                    @if(Auth::check())
                        <strong class="{{ Auth::id() === $note->user_id ? 'text-green-700' : 'text-red-700 font-bold' }}">
                            User ID {{ Auth::id() }} ({{ Auth::user()->name }})
                        </strong>
                    @else
                        <strong class="text-gray-700">Tamu / Belum Login (Guest)</strong>
                    @endif
                </p>
            </div>

            @if(Auth::check() && Auth::id() !== $note->user_id)
                <div class="p-3 bg-red-100 border border-red-300 rounded text-red-900 text-xs">
                    <p class="font-bold flex items-center">
                        <x-heroicon-s-x-circle class="w-4 h-4 mr-1 text-red-600 inline" />
                        BUKTI IDOR BERHASIL TERJADI:
                    </p>
                    <p class="mt-1">
                        Pengguna <strong>{{ Auth::user()->name }} (ID: {{ Auth::id() }})</strong> berhasil mengakses dan melihat catatan milik <strong>{{ $note->user->name }} (ID: {{ $note->user_id }})</strong> secara langsung dengan status <strong>HTTP 200 OK</strong> tanpa menerima HTTP 403 Forbidden!
                    </p>
                </div>
            @else
                <div class="p-3 bg-gray-50 border border-gray-200 rounded text-gray-600 text-xs">
                    Gunakan tombol switch user di <a href="{{ route('vuln.index') }}" class="text-indigo-600 underline">Dashboard Lab</a> untuk berganti akun lain dan membuktikan celah IDOR.
                </div>
            @endif
        </div>

        <!-- Detail Catatan -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-4">
            <div>
                <span class="text-xs text-gray-400 uppercase font-mono">Judul Catatan</span>
                <h3 class="text-xl font-bold text-gray-900">{{ $note->title }}</h3>
            </div>

            <div>
                <span class="text-xs text-gray-400 uppercase font-mono">Isi Konten (Terenkripsi di DB Versi Aman, Teks di DB Rentan)</span>
                <div class="mt-1 p-4 bg-gray-50 rounded border border-gray-200 text-sm font-mono text-gray-800 break-words">
                    {{ $note->content }}
                </div>
            </div>

            <!-- Bagian Demonstrasi XSS (Syarat 10) -->
            <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-yellow-100 text-yellow-800">
                        Syarat 10: Uji Celah Stored XSS
                    </span>
                    <span class="text-2xs font-mono text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200">
                        Blade: &#123;&#33;&#33; $note-&gt;public_comment &#33;&#33;&#125;
                    </span>
                </div>

                <div class="mb-3 text-xs text-gray-600">
                    Komentar publik di bawah ini dirender menggunakan sintaks mentah unescaped <code class="font-mono text-red-700">&#123;&#33;&#33; ... &#33;&#33;&#125;</code>. Jika disuntik kode JavaScript atau HTML, browser akan mengeksekusinya langsung:
                </div>

                <!-- EKSEKUSI RAW XSS SENGAJA UNTUK DEMO -->
                <div class="p-4 bg-yellow-50 border-2 border-yellow-300 rounded shadow-inner min-h-[60px]">
                    <div id="unescaped-xss-container" class="text-sm">
                        {!! $note->public_comment !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Injeksi XSS / Edit Komentar Publik -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h4 class="text-sm font-bold text-gray-900 mb-2">Uji Suntik Payload XSS (Stored XSS Trigger)</h4>
            <p class="text-xs text-gray-600 mb-4">
                Kirim payload XSS untuk disimpan ke basis data dan dirender langsung di atas tanpa sanitasi:
            </p>

            <form action="{{ route('vuln.notes.comment', $note->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="public_comment" class="block text-xs font-medium text-gray-700 mb-1">
                        Komentar Publik (Input Bebas):
                    </label>
                    <textarea id="public_comment" name="public_comment" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded text-xs font-mono focus:ring-yellow-500 focus:border-yellow-500"
                              placeholder="Masukkan teks biasa atau payload seperti: <script>alert('XSS Terbuka!')</script> atau <b style='color:red'>Bold Red</b>">{{ $note->public_comment }}</textarea>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-2xs font-mono text-gray-500">
                        Payload rekomendasi: <code class="text-red-700">&lt;script&gt;console.log('XSS-Triggered')&lt;/script&gt;</code>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium text-xs rounded shadow">
                        Simpan Komentar Rentan
                    </button>
                </div>
            </form>
        </div>

    </main>

    <footer class="mt-12 py-6 bg-gray-200 border-t border-gray-300 text-center text-xs text-gray-600 font-mono">
        CatatanSecure Lab Rentan &copy; 2026 - Demonstrasi IDOR & XSS (Syarat 9 & Syarat 10)
    </footer>

</body>
</html>
