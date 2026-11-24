<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab SQL Injection (Syarat 8) - CatatanSecure</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Header Banner -->
    <header class="bg-red-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-heroicon-s-code-bracket-square class="w-7 h-7 text-yellow-300" />
                <div>
                    <h1 class="text-lg font-bold uppercase tracking-wide">
                        DEMONSTRASI SYARAT 8 (SEC-08): SQL INJECTION RENTAN
                    </h1>
                    <p class="text-xs text-red-200 font-mono">
                        Loopback Localhost (127.0.0.1:8001) | String Concatenation Kueri Mentah
                    </p>
                </div>
            </div>
            <a href="{{ route('vuln.index') }}"
               class="px-3 py-1.5 rounded bg-red-900 hover:bg-red-950 text-xs font-semibold text-white border border-red-700">
                &larr; Kembali ke Dashboard Lab
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Kotak Penjelasan Kerentanan -->
        <div class="bg-white rounded-lg border border-red-200 p-6 shadow-sm">
            <h2 class="text-base font-bold text-red-900 mb-2">Penjelasan Celah SQL Injection</h2>
            <p class="text-xs text-gray-600 leading-relaxed mb-3">
                Pada modul versi rentan ini, parameter pencarian <code class="font-mono text-red-700 font-bold">$request->query('q')</code> disisipkan secara langsung ke dalam string SQL tanpa menggunakan <em>Prepared Statements</em> (parameter binding <code class="font-mono">?</code> atau <code class="font-mono">:param</code>).
            </p>
            <div class="bg-gray-900 text-green-400 p-3 rounded font-mono text-xs overflow-x-auto">
                <span class="text-gray-400">// Kode Controller Rentan:</span><br>
                $rawSql = "SELECT * FROM notes WHERE title LIKE '%" . $query . "%'";<br>
                $results = DB::select($rawSql);
            </div>
        </div>

        <!-- Form Pencarian & Rekomendasi Payload Uji -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900">Form Pencarian Catatan (Input Rentan)</h3>
            <form action="{{ route('vuln.notes.search') }}" method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ $query }}"
                       placeholder="Cari judul catatan, misal: Kuliah atau masukkan payload SQLi..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded focus:ring-red-500 focus:border-red-500 text-sm font-mono">
                <button type="submit"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded shadow">
                    Jalankan Kueri
                </button>
                @if($query !== '')
                    <a href="{{ route('vuln.notes.search') }}"
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium text-sm rounded">
                        Reset
                    </a>
                @endif
            </form>

            <!-- Contoh Payload untuk Asistensi & Pengujian Dosen -->
            <div class="pt-2">
                <span class="text-xs font-semibold text-gray-700 block mb-2">Contoh Payload Uji (Klik untuk salin / pelajari):</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs font-mono">
                    <div class="p-2.5 bg-yellow-50 border border-yellow-200 rounded">
                        <span class="font-bold text-yellow-900 block mb-1">1. Tautologi Bypass (Bocorkan Semua Catatan):</span>
                        <code class="text-red-700 bg-white px-2 py-0.5 rounded border border-yellow-300 select-all block">
                            ' OR '1'='1' --
                        </code>
                        <span class="text-2xs text-gray-500 mt-1 block">Menghasilkan klausa WHERE selalu TRUE sehingga seluruh catatan bocor.</span>
                    </div>

                    <div class="p-2.5 bg-yellow-50 border border-yellow-200 rounded">
                        <span class="font-bold text-yellow-900 block mb-1">2. UNION-based SQLi (Ekstraksi Kredensial Users):</span>
                        <code class="text-red-700 bg-white px-2 py-0.5 rounded border border-yellow-300 select-all block">
                            ' UNION SELECT id, id, name, email, 'User', password, 0, created_at, updated_at FROM users -- 
                        </code>
                        <span class="text-2xs text-gray-500 mt-1 block">Mengekstrak hash password dan email seluruh pengguna ke tabel catatan.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tampilan Kueri Mentah yang Dieksekusi -->
        <div class="bg-gray-900 text-white rounded-lg p-4 font-mono text-xs shadow">
            <div class="text-gray-400 mb-1 flex items-center justify-between">
                <span>KUERI SQL MENTAH YANG DIJALANKAN DI DATABASE:</span>
                <span class="text-yellow-400">SQLite Engine</span>
            </div>
            <div class="text-green-400 font-bold break-all bg-black bg-opacity-40 p-2.5 rounded border border-gray-700">
                {{ $rawSql }}
            </div>
        </div>

        <!-- Box Pesan Error SQL jika Sintaks Salah -->
        @if($sqlError)
            <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r shadow-sm">
                <div class="flex items-start">
                    <x-heroicon-s-x-circle class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" />
                    <div>
                        <h4 class="text-xs font-bold text-red-800 uppercase">Database Syntax Error (SQL Error):</h4>
                        <p class="text-xs text-red-700 font-mono mt-1 break-all">{{ $sqlError }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Hasil Kueri -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Hasil Kueri SQL ({{ count($results) }} Baris Ditemukan)</h3>
                <span class="text-xs font-mono text-gray-500">Query: "{{ $query }}"</span>
            </div>

            @if(count($results) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                        <thead class="bg-gray-100 font-mono text-gray-600">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">User ID</th>
                                <th class="px-4 py-3">Judul (Title)</th>
                                <th class="px-4 py-3">Konten (Content)</th>
                                <th class="px-4 py-3">Komentar Publik</th>
                                <th class="px-4 py-3">Created At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 font-mono">
                            @foreach($results as $row)
                                <tr class="hover:bg-red-50">
                                    <td class="px-4 py-3 font-bold">{{ $row->id ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $row->user_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-bold font-sans">{{ $row->title ?? '-' }}</td>
                                    <td class="px-4 py-3 truncate max-w-xs text-gray-600">{{ $row->content ?? '-' }}</td>
                                    <td class="px-4 py-3 truncate max-w-xs text-gray-600">{{ $row->public_comment ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $row->created_at ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-xs text-gray-500">
                    Tidak ada baris data yang cocok dengan kueri tersebut.
                </div>
            @endif
        </div>

    </main>

    <footer class="mt-12 py-6 bg-gray-200 border-t border-gray-300 text-center text-xs text-gray-600 font-mono">
        CatatanSecure Lab Rentan &copy; 2026 - Demonstrasi SQL Injection (Syarat 8)
    </footer>

</body>
</html>
