@extends('layouts.app')

@section('title', 'Kelola Pengguna - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-800 mb-2">
                <x-heroicon-s-shield-check class="w-4 h-4 text-amber-600" />
                <span>Panel Administrator &bull; Manajemen Akun &amp; Proteksi Admin</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                Kelola Pengguna Sistem
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Manajemen akun pengguna dan penerapan proteksi agar akun admin terakhir tidak dapat dihapus.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700 flex items-center space-x-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                <span>Kembali ke Dashboard Admin</span>
            </a>
        </div>
    </div>

    <!-- Security Info Alert -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3 text-xs text-amber-900">
        <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
        <div class="space-y-1">
            <span class="font-bold">Aturan Keamanan Sistem (FR-012):</span>
            <ul class="list-disc list-inside space-y-0.5 text-amber-800">
                <li>Anda <strong>tidak dapat menghapus akun Anda sendiri</strong> saat sedang aktif login.</li>
                <li>Akun <strong>administrator terakhir yang tersisa di sistem tidak dapat dihapus</strong> untuk mencegah lockout sistem.</li>
                <li>Menghapus pengguna biasa akan menghapus seluruh catatan miliknya secara cascade di database SQLite.</li>
            </ul>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                </div>
                <input type="text"
                       name="q"
                       value="{{ $currentQuery }}"
                       placeholder="Cari berdasarkan nama atau email pengguna..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div class="sm:col-span-2">
                <select name="role"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">Semua Peran</option>
                    <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ $currentRole === 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 transition">
                    Cari
                </button>
                @if ($currentQuery || $currentRole)
                    <a href="{{ route('admin.users.index') }}"
                       title="Reset filter"
                       class="p-2 border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 text-left">ID</th>
                        <th class="px-6 py-3.5 text-left">Pengguna</th>
                        <th class="px-6 py-3.5 text-left">Peran</th>
                        <th class="px-6 py-3.5 text-left">Jumlah Catatan</th>
                        <th class="px-6 py-3.5 text-left">Bergabung</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $u->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900 flex items-center space-x-1.5">
                                    <span>{{ $u->name }}</span>
                                    @if ($u->id === Auth::id())
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-semibold">(Anda)</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $u->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $u->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium">
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-semibold">
                                    <x-heroicon-o-document-text class="w-3.5 h-3.5 mr-1 text-slate-500" />
                                    {{ $u->notes_count }} Catatan
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($u->id === Auth::id())
                                    <span class="text-xs text-gray-400 italic">Akun Aktif</span>
                                @else
                                    <form method="POST"
                                          action="{{ route('admin.users.destroy', $u) }}"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }} beserta seluruh catatannya? Tindakan ini tidak dapat dibatalkan.')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition"
                                                title="Hapus Akun Pengguna">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5 mr-1 text-red-600" />
                                            Hapus Pengguna
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada akun pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
