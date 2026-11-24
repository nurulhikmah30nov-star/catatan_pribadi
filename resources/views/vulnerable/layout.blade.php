<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Pustaka Demo Rentan' }} - LOCALHOST ONLY</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col font-sans">

    {{-- WARNING BANNER (DEC-05) --}}
    <div class="bg-red-600 text-white px-4 py-3 text-center shadow-md">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-2 text-sm font-semibold">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚠️</span>
                <span>PERINGATAN: LINGKUNGAN DEMONSTRASI RENTAN (LOCALHOST ONLY: 127.0.0.1:8001)</span>
            </div>
            <div class="text-xs bg-red-800/80 px-3 py-1 rounded font-mono">
                Cookie: {{ config('session.cookie') }} | DB: database_vulnerable.sqlite
            </div>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <header class="bg-slate-900 text-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('vuln.index') }}" class="font-bold text-lg text-emerald-400 hover:text-emerald-300">
                    PustakaSecure <span class="text-xs px-2 py-0.5 rounded bg-red-500/20 text-red-300 border border-red-500/30">VULN DEMO</span>
                </a>
                <nav class="flex items-center gap-2 text-sm">
                    <a href="{{ route('vuln.search') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        SEC-08: SQLi Search
                    </a>
                    <a href="{{ route('vuln.books.show', 1) }}" class="px-3 py-1.5 rounded hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        SEC-09: Stored XSS
                    </a>
                    <a href="{{ route('vuln.loans.show', 1) }}" class="px-3 py-1.5 rounded hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        SEC-10: IDOR Loan #1
                    </a>
                    <a href="{{ route('vuln.loans.show', 2) }}" class="px-3 py-1.5 rounded hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        SEC-10: IDOR Loan #2
                    </a>
                </nav>
            </div>

            {{-- SESSION SWITCHER --}}
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400">Login Cepat Demo:</span>
                <a href="{{ route('vuln.login.as', 'nurul@pustaka.local') }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-slate-700">
                    User A (Nurul)
                </a>
                <a href="{{ route('vuln.login.as', 'budi@pustaka.local') }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-sky-300 border border-slate-700">
                    User B (Budi)
                </a>
                <a href="{{ route('vuln.login.as', 'admin@pustaka.local') }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-700">
                    Admin
                </a>
            </div>
        </div>
    </header>

    {{-- FLASH MESSAGES --}}
    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-2.5 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-slate-900 text-slate-400 text-xs py-4 text-center border-t border-slate-800">
        <p>Lingkungan Demonstrasi Khusus Tugas Keamanan Sistem Komputer - Universitas Malikussaleh</p>
        <p class="mt-1 text-slate-500 font-mono">DILARANG MEMBUKA KE JARINGAN EKSTERNAL / 0.0.0.0 (DEC-05)</p>
    </footer>

</body>
</html>
