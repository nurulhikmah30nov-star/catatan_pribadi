@extends('layouts.app')

@section('title', 'Tambah Catatan Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="pb-4 border-b border-gray-200">
        <a href="{{ route('notes.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center space-x-1 mb-2">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            <span>Kembali ke Daftar Catatan</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
            Tambah Catatan Baru
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Isi catatan Anda akan dienkripsi secara otomatis menggunakan algoritma AES-256-GCM sebelum disimpan ke database.
        </p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('notes.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf

        <!-- Title Field -->
        <div>
            <label for="title" class="block text-sm font-semibold text-gray-800">
                Judul Catatan <span class="text-red-500">*</span>
            </label>
            <div class="mt-1">
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title') }}"
                       required
                       maxlength="255"
                       placeholder="Misal: Rencana Kerja Pekan Ini"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-xs text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('title') border-red-500 @enderror">
            </div>
            @error('title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Category & Archived Field -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-sm font-semibold text-gray-800">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <select name="category"
                            id="category"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-xs text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('category') border-red-500 @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center pt-6">
                <label class="relative flex items-start cursor-pointer">
                    <input type="checkbox"
                           name="is_archived"
                           value="1"
                           {{ old('is_archived') ? 'checked' : '' }}
                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded mt-0.5">
                    <span class="ml-2 text-sm text-gray-700">
                        <span class="font-medium text-gray-900">Arsipkan Catatan</span>
                        <span class="block text-xs text-gray-500">Sembunyikan dari daftar utama aktif</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Content Field (Encrypted with AES-256-GCM) -->
        <div>
            <div class="flex items-center justify-between">
                <label for="content" class="block text-sm font-semibold text-gray-800">
                    Isi Catatan Pribadi <span class="text-red-500">*</span>
                </label>
                <span class="inline-flex items-center text-xs text-emerald-700 font-medium">
                    <x-heroicon-s-shield-check class="w-3.5 h-3.5 mr-1" />
                    Terenkripsi AES-256-GCM
                </span>
            </div>
            <div class="mt-1">
                <textarea name="content"
                          id="content"
                          rows="8"
                          required
                          placeholder="Tuliskan catatan rahasia atau pribadi Anda di sini..."
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-xs text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('content') border-red-500 @enderror">{{ old('content') }}</textarea>
            </div>
            @error('content')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-400">
                Isi catatan akan dienkripsi dengan authenticated cipher AES-256-GCM dan diverifikasi integritasnya menggunakan AAD ID Pengguna.
            </p>
        </div>

        <!-- Public Comment / Free Text Field (SEC-09: XSS Prevention) -->
        <div>
            <label for="public_comment" class="block text-sm font-semibold text-gray-800">
                Komentar Publik / Teks Bebas
            </label>
            <div class="mt-1">
                <textarea name="public_comment"
                          id="public_comment"
                          rows="3"
                          maxlength="1000"
                          placeholder="Catatan tambahan atau keterangan ringkas..."
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-xs text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('public_comment') border-red-500 @enderror">{{ old('public_comment') }}</textarea>
            </div>
            @error('public_comment')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-400">
                Kolom ini dievaluasi secara aman menggunakan sistem escaping Blade <code>@{{ $text }}</code> untuk mencegah serangan XSS.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
            <a href="{{ route('notes.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center px-5 py-2 rounded-lg text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                <x-heroicon-o-lock-closed class="w-4 h-4 mr-1.5" />
                Simpan &amp; Enkripsi Catatan
            </button>
        </div>
    </form>
</div>
@endsection
