@extends('layouts.app')

@section('title', 'Katalog Buku')
@section('container-class', 'max-w-6xl')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Katalog Buku Perpustakaan</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar koleksi buku yang tersedia dalam sistem (SEC-08 Anti-SQLi)</p>
        </div>
        @if (Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('books.create') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Tambah Buku Baru
            </a>
        @endif
    </div>

    <!-- Search & Filter Form (SEC-08: Protected by Parameter Binding) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('books.search') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-6">
                <label for="q" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Cari Buku (Judul / Pengarang / Kategori)</label>
                <div class="relative">
                    <input type="text" id="q" name="q" value="{{ $query }}"
                           placeholder="Ketik kata kunci pencarian..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                    </div>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="sort" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Urutkan</label>
                <select id="sort" name="sort"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="title" {{ $sortBy === 'title' ? 'selected' : '' }}>Judul Buku</option>
                    <option value="author" {{ $sortBy === 'author' ? 'selected' : '' }}>Pengarang</option>
                    <option value="year" {{ $sortBy === 'year' ? 'selected' : '' }}>Tahun Terbit</option>
                    <option value="stock" {{ $sortBy === 'stock' ? 'selected' : '' }}>Jumlah Stok</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="dir" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Arah</label>
                <select id="dir" name="dir"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>A-Z / Terlama</option>
                    <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>Z-A / Terbaru</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-2">
                <button type="submit"
                        class="w-full inline-flex justify-center items-center py-2 px-3 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cari
                </button>
            </div>
        </form>

        @if ($query !== '')
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-600">
                <span>Hasil pencarian untuk kata kunci: <strong class="text-gray-900">"{{ $query }}"</strong></span>
                <a href="{{ route('books.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Reset Pencarian</a>
            </div>
        @endif
    </div>

    <!-- Table of Books -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengarang</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($books as $book)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    <a href="{{ route('books.show', $book) }}" class="hover:text-indigo-600">
                                        {{ $book->title }}
                                    </a>
                                </div>
                                @if ($book->is_archived)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Diarsipkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $book->author }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $book->year }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $book->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($book->stock > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ $book->stock }} Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('books.show', $book) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                @if (Auth::check() && Auth::user()->role === 'admin')
                                    <a href="{{ route('books.edit', $book) }}" class="text-amber-600 hover:text-amber-900">Ubah</a>
                                    <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan buku ini dari katalog?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Arsipkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-book-open class="w-12 h-12 text-slate-300 mb-3" />
                                    <p class="font-medium text-gray-900 text-base">Tidak ada buku yang ditemukan</p>
                                    <p class="text-gray-500 mt-1">Coba gunakan kata kunci pencarian yang lain atau reset filter.</p>
                                    @if ($query !== '')
                                        <a href="{{ route('books.index') }}" class="mt-4 inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            Kembali ke Semua Buku
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($books->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
