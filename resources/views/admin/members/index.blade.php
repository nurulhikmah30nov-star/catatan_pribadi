@extends('layouts.app')

@section('title', 'Manajemen Anggota Perpustakaan')
@section('container-class', 'max-w-7xl')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Anggota & Akun Pengguna</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar akun terdaftar dalam sistem dengan proteksi administrator aktif terakhir (FR-012)</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Area Khusus Administrator
            </span>
        </div>
    </div>

    <!-- Security Information Box: FR-012 & SEC-03 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-xs text-indigo-900 flex items-start space-x-3">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div>
                <span class="font-semibold">Proteksi Administrator Aktif Terakhir (FR-012):</span>
                Sistem secara otomatis menolak penghapusan akun administrator jika hanya tersisa 1 akun administrator di database guna mencegah terjadinya penguncian sistem (<em>system lockout</em>). Penghapusan akun yang sedang aktif login juga dicegah secara ketat.
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-xs text-emerald-900 flex items-start space-x-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
                <span class="font-semibold">Dekripsi Data Sensitif Fisik (SEC-03):</span>
                Nomor telepon anggota yang tersimpan terenkripsi AES-256-GCM AEAD di basis data didekripsi secara transparan di tabel ini hanya untuk keperluan verifikasi identitas fisik perpustakaan oleh petugas resmi.
            </div>
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama & Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Peran (Role)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Telepon (Decrypted)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alamat Fisik</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pinjaman Aktif</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($members as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5">ID: #{{ $user->id }} &bull; Terdaftar: {{ $user->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($user->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Member
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if ($user->member && $user->member->phone_number)
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded border border-gray-200 text-gray-800">
                                        {{ $user->member->phone_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum Diatur</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate" title="{{ $user->member?->address ?? '-' }}">
                                {{ $user->member?->address ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $activeLoans = $user->loans->where('status', 'borrowed')->count();
                                @endphp
                                @if ($activeLoans > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $activeLoans }} Buku
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">0 Buku</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if ($user->id === Auth::id())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200" title="Akun Anda yang sedang aktif tidak dapat dihapus">
                                        Sesi Aktif
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }} ({{ $user->email }})? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded text-xs font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="w-3.5 h-3.5 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus Akun
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
