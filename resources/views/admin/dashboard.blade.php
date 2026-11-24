@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800 mb-2">
                <x-heroicon-s-shield-check class="w-4 h-4 text-amber-600" />
                <span>Panel Manajemen Terpusat &bull; Hak Akses Administrator</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                Dashboard Administrator
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Pantau seluruh catatan pengguna, statistik aktivitas sistem, dan kelola akun pengguna terdaftar.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.notes.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition">
                <x-heroicon-o-document-duplicate class="w-4 h-4 mr-1.5" />
                Semua Catatan
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white shadow-sm transition">
                <x-heroicon-o-users class="w-4 h-4 mr-1.5" />
                Kelola Pengguna
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card variant="mint"
                     title="Total Pengguna"
                     :value="$totalUsers"
                     caption="{{ $totalRegularUsers }} pengguna biasa, {{ $totalAdmins }} admin">
            <x-slot:icon>
                <x-heroicon-o-users class="w-6 h-6 text-emerald-700" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card variant="sky"
                     title="Total Catatan"
                     :value="$totalNotes"
                     caption="Semua catatan lintas pengguna">
            <x-slot:icon>
                <x-heroicon-o-document-text class="w-6 h-6 text-blue-700" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card variant="peach"
                     title="Catatan Terenkripsi"
                     :value="$totalNotes"
                     caption="100% catatan terlindungi AES-256-GCM">
            <x-slot:icon>
                <x-heroicon-o-lock-closed class="w-6 h-6 text-orange-700" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card variant="mint"
                     title="Catatan Diarsipkan"
                     :value="$archivedNotes"
                     caption="Status diarsipkan oleh pengguna">
            <x-slot:icon>
                <x-heroicon-o-archive-box class="w-6 h-6 text-emerald-700" />
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Recent Data Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Notes (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-emerald-600" />
                    <span>Catatan Terbaru (Lintas Pengguna)</span>
                </h2>
                <a href="{{ route('admin.notes.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($recentNotes as $n)
                    <div class="p-4 hover:bg-gray-50 transition flex items-center justify-between">
                        <div class="space-y-1 max-w-md">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-700">
                                    {{ $n->category }}
                                </span>
                                <a href="{{ route('notes.show', $n) }}" class="text-sm font-semibold text-gray-900 hover:text-emerald-600 line-clamp-1">
                                    {{ $n->title }}
                                </a>
                            </div>
                            <p class="text-xs text-gray-500">
                                Pemilik: <span class="font-medium text-gray-700">{{ $n->user->name ?? '-' }}</span> ({{ $n->user->email ?? '-' }})
                            </p>
                        </div>
                        <div class="flex items-center space-x-2 text-xs text-gray-400">
                            <span>{{ $n->created_at->diffForHumans() }}</span>
                            <a href="{{ route('notes.show', $n) }}" class="p-1 text-gray-500 hover:text-emerald-600">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">
                        Belum ada catatan di sistem.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Users (1 Col) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                    <x-heroicon-o-users class="w-5 h-5 text-amber-600" />
                    <span>Pengguna Terdaftar</span>
                </h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">
                    Kelola &rarr;
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($recentUsers as $u)
                    <div class="p-4 hover:bg-gray-50 transition flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="text-sm font-semibold text-gray-900">{{ $u->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold {{ $u->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $u->role }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $u->email }}</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $u->created_at ? $u->created_at->format('d M') : '-' }}</span>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">
                        Belum ada pengguna.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
