@extends('layouts.app')

@section('title', 'Daftar Catatan Pribadi')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center space-x-2">
                <x-heroicon-o-document-text class="w-7 h-7 text-emerald-600" />
                <span>Catatan Pribadi</span>
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Semua catatan disimpan terenkripsi dengan AES-256-GCM. Hanya Anda yang memiliki akses ke catatan ini.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('notes.create') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                <x-heroicon-o-plus class="w-4 h-4 mr-1.5" />
                Catatan Baru
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar (SEC-08: Parameterized Query) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('notes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Search Keyword -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                </div>
                <input type="text"
                       name="q"
                       value="{{ $currentQuery }}"
                       placeholder="Cari judul catatan..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Category Filter -->
            <div class="sm:col-span-3">
                <select name="category"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $currentCategory === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="archived" {{ $currentStatus === 'archived' ? 'selected' : '' }}>Arsip</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 transition">
                    Cari
                </button>
                @if ($currentQuery || $currentCategory || $currentStatus)
                    <a href="{{ route('notes.index') }}"
                       title="Reset filter"
                       class="p-2 border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Notes List / Grid -->
    @if ($notes->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto text-gray-400 mb-3" />
            <h3 class="text-base font-semibold text-gray-900">Tidak ada catatan ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if ($currentQuery || $currentCategory || $currentStatus)
                    Pencarian dengan kata kunci atau filter tersebut tidak menghasilkan catatan apapun.
                @else
                    Anda belum membuat catatan pribadi. Mulai dengan membuat catatan pertama Anda!
                @endif
            </p>
            <div class="mt-4">
                @if ($currentQuery || $currentCategory || $currentStatus)
                    <a href="{{ route('notes.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Reset Pencarian
                    </a>
                @else
                    <a href="{{ route('notes.create') }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">
                        <x-heroicon-o-plus class="w-4 h-4 mr-1.5" />
                        Tambah Catatan
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($notes as $note)
                <div class="bg-white rounded-xl shadow-xs border border-gray-200 hover:border-emerald-300 hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if ($note->category === 'Rahasia') bg-rose-100 text-rose-800 border border-rose-200
                                @elseif ($note->category === 'Kuliah') bg-blue-100 text-blue-800 border border-blue-200
                                @elseif ($note->category === 'Pekerjaan') bg-amber-100 text-amber-800 border border-amber-200
                                @else bg-emerald-100 text-emerald-800 border border-emerald-200
                                @endif">
                                {{ $note->category }}
                            </span>

                            @if ($note->is_archived)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    <x-heroicon-o-archive-box class="w-3.5 h-3.5 mr-1" />
                                    Arsip
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-bold text-gray-900 line-clamp-1 mb-2 hover:text-emerald-700">
                            <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                        </h3>

                        <p class="text-sm text-gray-600 line-clamp-3 mb-3">
                            {{ Str::limit($note->content, 120) }}
                        </p>

                        @if ($note->public_comment)
                            <div class="text-xs bg-slate-50 text-slate-500 rounded p-2 mb-2 border border-slate-100 flex items-start space-x-1.5">
                                <x-heroicon-o-chat-bubble-bottom-center-text class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" />
                                <span class="line-clamp-1">{{ $note->public_comment }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="px-5 py-3 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                        <span class="flex items-center">
                            <x-heroicon-o-lock-closed class="w-3.5 h-3.5 text-emerald-600 mr-1" />
                            AES-256-GCM
                        </span>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('notes.show', $note) }}"
                               class="text-gray-600 hover:text-emerald-700 p-1"
                               title="Lihat Detail">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </a>
                            <a href="{{ route('notes.edit', $note) }}"
                               class="text-gray-600 hover:text-blue-700 p-1"
                               title="Edit Catatan">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </a>
                            <form method="POST"
                                  action="{{ route('notes.destroy', $note) }}"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan ini?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-gray-600 hover:text-red-700 p-1"
                                        title="Hapus Catatan">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notes->links() }}
        </div>
    @endif
</div>
@endsection
