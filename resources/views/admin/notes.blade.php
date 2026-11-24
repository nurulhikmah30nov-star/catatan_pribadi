@extends('layouts.app')

@section('title', 'Semua Catatan Pengguna - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800 mb-2">
                <x-heroicon-s-shield-check class="w-4 h-4 text-amber-600" />
                <span>Panel Administrator &bull; Manajemen Data Terpusat</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                Semua Catatan Pengguna
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Melihat dan mengelola seluruh catatan yang tersimpan di sistem lintas akun pengguna.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700 flex items-center space-x-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                <span>Kembali ke Dashboard Admin</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar (SEC-08) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('admin.notes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                </div>
                <input type="text"
                       name="q"
                       value="{{ $currentQuery }}"
                       placeholder="Cari judul, kategori, atau nama/email pemilik..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div class="sm:col-span-3">
                <select name="category"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $currentCategory === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="archived" {{ $currentStatus === 'archived' ? 'selected' : '' }}>Arsip</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 transition">
                    Filter
                </button>
                @if ($currentQuery || $currentCategory || $currentStatus)
                    <a href="{{ route('admin.notes.index') }}"
                       title="Reset filter"
                       class="p-2 border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Notes Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 text-left">ID</th>
                        <th class="px-6 py-3.5 text-left">Judul Catatan</th>
                        <th class="px-6 py-3.5 text-left">Pemilik Akun</th>
                        <th class="px-6 py-3.5 text-left">Kategori</th>
                        <th class="px-6 py-3.5 text-left">Status</th>
                        <th class="px-6 py-3.5 text-left">Dibuat</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @forelse ($notes as $note)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $note->id }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('notes.show', $note) }}" class="font-semibold text-gray-900 hover:text-emerald-700">
                                    {{ $note->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $note->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $note->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    @if ($note->category === 'Rahasia') bg-rose-100 text-rose-800
                                    @elseif ($note->category === 'Kuliah') bg-blue-100 text-blue-800
                                    @elseif ($note->category === 'Pekerjaan') bg-amber-100 text-amber-800
                                    @else bg-emerald-100 text-emerald-800
                                    @endif">
                                    {{ $note->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($note->is_archived)
                                    <span class="inline-flex items-center text-xs text-gray-500">
                                        <x-heroicon-o-archive-box class="w-3.5 h-3.5 mr-1" />
                                        Arsip
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs text-emerald-600 font-medium">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5 mr-1" />
                                        Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $note->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('notes.show', $note) }}"
                                   class="inline-flex items-center p-1.5 rounded text-gray-600 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                   title="Buka Catatan">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </a>
                                <form method="POST"
                                      action="{{ route('notes.destroy', $note) }}"
                                      onsubmit="return confirm('Sebagai administrator, Anda akan menghapus catatan ini. Lanjutkan?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center p-1.5 rounded text-gray-600 hover:text-red-700 hover:bg-red-50 transition"
                                            title="Hapus Catatan">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada catatan yang cocok dengan filter atau kata kunci pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $notes->links() }}
        </div>
    </div>
</div>
@endsection
