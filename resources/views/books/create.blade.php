@extends('layouts.app')

@section('title', 'Tambah Buku Baru')
@section('container-class', 'max-w-2xl')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('books.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Katalog
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm border border-gray-200">
        <div class="mb-6 pb-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Buku Baru</h1>
            <p class="text-sm text-gray-600 mt-1">Form penambahan data koleksi buku ke katalog perpustakaan (Khusus Admin)</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200" role="alert">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('books.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Judul Buku <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required autofocus
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="author" class="block text-sm font-medium text-gray-700">Pengarang / Penulis <span class="text-red-500">*</span></label>
                <input type="text" id="author" name="author" value="{{ old('author') }}" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Tahun Terbit <span class="text-red-500">*</span></label>
                    <input type="number" id="year" name="year" value="{{ old('year', date('Y')) }}" required min="1800" max="{{ date('Y') + 1 }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                    <input type="text" id="category" name="category" value="{{ old('category') }}" required placeholder="Contoh: Teknologi"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Jumlah Stok <span class="text-red-500">*</span></label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', 1) }}" required min="0" max="1000"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('books.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Simpan Buku
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
