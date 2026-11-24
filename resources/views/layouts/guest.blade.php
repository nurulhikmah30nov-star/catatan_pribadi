<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CatatanSecure') }} - @yield('title', 'Autentikasi Aman')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-900 bg-slate-50 antialiased font-sans">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center space-x-2 text-2xl font-bold tracking-tight text-gray-900">
            <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                <x-heroicon-o-document-text class="w-8 h-8" />
            </span>
            <span>{{ config('app.name', 'CatatanSecure') }}</span>
        </a>
        <h2 class="mt-4 text-xl font-bold text-gray-900">@yield('heading', 'Catatan Pribadi Terenkripsi')</h2>
        <p class="mt-1 text-xs text-gray-500">Tugas Kuliah Keamanan Sistem Komputer - Universitas Malikussaleh</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-md rounded-2xl border border-gray-100 sm:px-10">
            @include('components.alert')

            @yield('content')
        </div>
    </div>

    <div class="mt-8 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ config('app.name', 'CatatanSecure') }} &bull; Nurul Hikmah (230170187)
    </div>
</body>
</html>
