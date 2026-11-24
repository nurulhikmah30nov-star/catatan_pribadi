@extends('layouts.guest')

@section('title', 'Daftar Akun Baru')
@section('heading', 'Buat Akun CatatanSecure')

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
            Nama Lengkap <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name') }}"
               required autofocus autocomplete="name"
               placeholder="Nama Anda"
               aria-required="true"
               class="block w-full px-3 py-2 border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        @error('name')
            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
            Alamat Email <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               required autocomplete="email"
               placeholder="nama@email.com"
               aria-required="true"
               class="block w-full px-3 py-2 border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        @error('email')
            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
            Kata Sandi (8–72 Karakter) <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input type="password" id="password" name="password"
               required autocomplete="new-password"
               maxlength="72"
               placeholder="Minimal 8 karakter"
               aria-required="true"
               class="block w-full px-3 py-2 border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        <p class="text-[11px] text-gray-400 mt-1">
            Dihash menggunakan bcrypt cost factor 12 — tidak disimpan sebagai teks biasa (SEC-02).
        </p>
        @error('password')
            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
            Konfirmasi Kata Sandi <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               required autocomplete="new-password"
               placeholder="Ulangi kata sandi"
               aria-required="true"
               class="block w-full px-3 py-2 border {{ $errors->has('password_confirmation') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        @error('password_confirmation')
            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    {{-- Info peran default --}}
    <div class="p-3 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-700 flex items-start space-x-2">
        <x-heroicon-o-information-circle class="w-4 h-4 flex-shrink-0 mt-0.5 text-blue-500" />
        <span>Akun baru terdaftar sebagai <strong>Pengguna Biasa</strong>. Akses Administrator hanya diberikan oleh sistem.</span>
    </div>

    <div>
        <button type="submit"
                class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
            <x-heroicon-o-user-plus class="w-4 h-4 mr-2" />
            Daftar Sekarang
        </button>
    </div>
</form>

<div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-600">
    Sudah memiliki akun?
    <a href="{{ route('login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">
        Masuk ke Sistem
    </a>
</div>
@endsection
