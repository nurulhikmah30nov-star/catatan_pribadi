@extends('layouts.app')

@section('title', 'Riwayat Pinjaman Saya')
@section('container-class', 'max-w-6xl')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Pinjaman Saya</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar buku yang sedang Anda pinjam atau pernah dipinjam sebelumnya (SEC-10 Otorisasi Server-Side)</p>
        </div>
        <div>
            <a href="{{ route('books.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Jelajahi Katalog Buku
            </a>
        </div>
    </div>

    <!-- Loans Table -->
    @if ($loans->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Riwayat Peminjaman</h3>
            <p class="mt-1 text-sm text-gray-500">Anda belum pernah meminjam buku apa pun dari katalog perpustakaan.</p>
            <div class="mt-6">
                <a href="{{ route('books.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Pinjam Buku Sekarang
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Buku</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batas Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($loans as $loan)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('loans.show', $loan) }}" class="hover:text-indigo-600">
                                            {{ $loan->book->title ?? 'Buku Tidak Tersedia' }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $loan->book->author ?? '-' }} ({{ $loan->book->year ?? '-' }})
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $loan->loan_date ? \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($loan->status === 'borrowed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                            Dipinjam
                                        </span>
                                    @elseif ($loan->status === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Dikembalikan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('loans.show', $loan) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                        Detail
                                    </a>

                                    @if ($loan->status === 'borrowed')
                                        <form method="POST" action="{{ route('loans.return', $loan) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Apakah Anda yakin ingin mengembalikan buku ini?')"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent rounded text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                                Kembalikan
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($loans->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
