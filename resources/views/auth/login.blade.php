@extends('layouts.guest')

@section('title', 'Masuk Akun')
@section('heading', 'Masuk ke CatatanSecure')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
            Alamat Email <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               required autofocus autocomplete="email"
               placeholder="nama@email.com"
               aria-required="true"
               aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
               class="block w-full px-3 py-2 border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        @error('email')
            <p id="email-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
            Kata Sandi <span class="text-red-500" aria-hidden="true">*</span>
        </label>
        <input type="password" id="password" name="password"
               required autocomplete="current-password"
               placeholder="••••••••"
               aria-required="true"
               class="block w-full px-3 py-2 border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        @error('password')
            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox"
                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-xs text-gray-700">Ingat Sesi Saya</label>
        </div>
    </div>

    <div>
        <button type="submit"
                class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 mr-2" />
            Masuk ke Sistem
        </button>
    </div>
</form>

<div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-600">
    Belum memiliki akun?
    <a href="{{ route('register') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">
        Daftar Pengguna Baru
    </a>
</div>
@endsection
