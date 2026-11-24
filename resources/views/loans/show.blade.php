@extends('layouts.app')

@section('title', 'Detail Peminjaman #' . $loan->id)
@section('container-class', 'max-w-4xl')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb / Back button -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('loans.my') }}" class="hover:text-indigo-600">Riwayat Pinjaman</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Detail Transaksi #{{ $loan->id }}</span>
        </div>
        <a href="{{ route('loans.my') }}"
           class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Security Info Badge (SEC-10) -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-800 flex items-start space-x-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <div>
            <span class="font-semibold">Proteksi Anti-IDOR Terverifikasi (SEC-10):</span>
            Laman ini dilindungi oleh otorisasi server-side <code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">LoanPolicy@view</code>.
            Hanya pemilik transaksi (<code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">user_id: {{ $loan->user_id }}</code>) dan administrator yang diizinkan mengakses data ini.
        </div>
    </div>

    <!-- Loan Detail Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Transaksi Peminjaman #{{ $loan->id }}</h2>
                <p class="text-xs text-gray-500">Dibuat pada {{ $loan->created_at ? $loan->created_at->format('d M Y H:i') : '-' }}</p>
            </div>
            <div>
                @if ($loan->status === 'borrowed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                        Sedang Dipinjam
                    </span>
                @elseif ($loan->status === 'returned')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Sudah Dikembalikan
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                        {{ ucfirst($loan->status) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Book Information Section -->
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Buku</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <span class="block text-xs text-gray-500">Judul Buku</span>
                        <a href="{{ route('books.show', $loan->book_id) }}" class="text-base font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $loan->book->title ?? 'Buku Tidak Tersedia' }}
                        </a>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Pengarang</span>
                        <span class="text-sm font-medium text-gray-800">{{ $loan->book->author ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Kategori</span>
                        <span class="text-sm font-medium text-gray-800">{{ $loan->book->category ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500">Tahun Terbit / Penerbit</span>
                        <span class="text-sm font-medium text-gray-800">{{ $loan->book->year ?? '-' }} / {{ $loan->book->publisher ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Loan Schedule Section -->
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Jadwal & Status Sirkulasi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-lg border border-gray-200 bg-white">
                        <span class="block text-xs text-gray-500">Tanggal Peminjaman</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $loan->loan_date ? \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>

                    <div class="p-4 rounded-lg border border-gray-200 bg-white">
                        <span class="block text-xs text-gray-500">Batas Waktu Pengembalian</span>
                        <span class="text-sm font-semibold {{ $loan->status === 'borrowed' && \Carbon\Carbon::parse($loan->due_date)->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>

                    <div class="p-4 rounded-lg border border-gray-200 bg-white">
                        <span class="block text-xs text-gray-500">Tanggal Dikembalikan</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at)->translatedFormat('d F Y H:i') : 'Belum Dikembalikan' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Member / Borrower Info (visible if admin) -->
            @if (Auth::user()->role === 'admin')
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Peminjam (Administrator View)</h3>
                    <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 text-sm">
                        <p class="text-amber-900"><strong>User ID:</strong> {{ $loan->user_id }}</p>
                        <p class="text-amber-900"><strong>Nama Akun:</strong> {{ $loan->user->name ?? 'User #' . $loan->user_id }}</p>
                        <p class="text-amber-900"><strong>Email Akun:</strong> {{ $loan->user->email ?? '-' }}</p>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('loans.my') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Kembali
                </a>

                @if ($loan->status === 'borrowed')
                    <form method="POST" action="{{ route('loans.return', $loan) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin mengembalikan buku ini sekarang?')"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Kembalikan Buku Sekarang
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
