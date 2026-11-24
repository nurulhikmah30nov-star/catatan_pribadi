<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CatatanSecure') }} - @yield('title', 'Aplikasi Catatan Pribadi Terenkripsi')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between text-slate-900 bg-slate-50 antialiased font-sans">
    <div>
        @include('layouts.navigation')

        <main class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="@yield('container-class', 'max-w-7xl') mx-auto">
                @include('components.alert')

                @yield('content')
            </div>
        </main>
    </div>

    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div class="flex items-center space-x-2 text-emerald-700 font-medium">
                <x-heroicon-s-shield-check class="w-4 h-4 text-emerald-600" />
                <span>Terlindungi Protokol Keamanan TLS 1.3 &bull; Enkripsi AES-256-GCM (SEC-03)</span>
            </div>
            <div>
                &copy; {{ date('Y') }} {{ config('app.name', 'CatatanSecure') }} &bull; Tugas Keamanan Sistem Komputer (Nurul Hikmah / 230170187)
            </div>
        </div>
    </footer>
</body>
</html>
