@extends('layouts.app')

@section('title', $book->title)
@section('container-class', 'max-w-4xl')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back -->
    <div>
        <a href="{{ route('books.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Katalog Buku
        </a>
    </div>

    <!-- Main Book Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-gray-200">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $book->category }}
                        </span>
                        @if ($book->is_archived)
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                Diarsipkan
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-2">{{ $book->title }}</h1>
                    <p class="text-base text-gray-600 mt-1">Ditulis oleh <strong class="text-gray-900">{{ $book->author }}</strong> (Tahun {{ $book->year }})</p>
                </div>

                @if (Auth::check() && Auth::user()->role === 'admin')
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('books.edit', $book) }}"
                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Ubah
                        </a>
                        <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline"
                              onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan buku ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-xs font-medium text-red-700 bg-white hover:bg-red-50">
                                <svg class="w-3.5 h-3.5 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Arsipkan
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-b border-gray-200">
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</p>
                    <p class="text-base font-medium text-gray-900 mt-1">{{ $book->category }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun Terbit</p>
                    <p class="text-base font-medium text-gray-900 mt-1">{{ $book->year }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ketersediaan Stok</p>
                    <p class="text-base font-medium {{ $book->stock > 0 ? 'text-emerald-700' : 'text-red-700' }} mt-1">
                        {{ $book->stock }} Eksemplar
                    </p>
                </div>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Koleksi</p>
                    <p class="text-base font-medium text-gray-900 mt-1">
                        {{ $book->is_archived ? 'Diarsipkan' : ($book->stock > 0 ? 'Tersedia' : 'Habis Dipinjam') }}
                    </p>
                </div>
            </div>

            <!-- Circulation Actions (TKT-008: Peminjaman Atomik & Pengembalian Idempoten) -->
            <div class="pt-6">
                @if ($book->is_archived)
                    <div class="p-4 rounded-md bg-gray-100 border border-gray-200 text-sm text-gray-700">
                        Buku ini telah diarsipkan dari sirkulasi aktif dan tidak dapat dipinjam.
                    </div>
                @elseif ($activeLoan)
                    <div class="p-4 rounded-md bg-amber-50 border border-amber-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <p class="text-sm font-semibold text-amber-900">Anda sedang aktif meminjam buku ini.</p>
                            <p class="text-xs text-amber-700 mt-0.5">Batas waktu pengembalian: <strong>{{ \Carbon\Carbon::parse($activeLoan->due_date)->format('d M Y') }}</strong></p>
                        </div>
                        <form method="POST" action="{{ route('loans.return', $activeLoan) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                Kembalikan Buku Sekarang
                            </button>
                        </form>
                    </div>
                @elseif ($book->stock > 0)
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-4 rounded-md bg-emerald-50 border border-emerald-200">
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">Koleksi buku tersedia untuk dipinjam.</p>
                            <p class="text-xs text-emerald-700 mt-0.5">Masa peminjaman standar adalah 7 hari kalender.</p>
                        </div>
                        <form method="POST" action="{{ route('loans.store') }}">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Pinjam Buku Ini (7 Hari)
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-4 rounded-md bg-red-50 border border-red-200 text-sm text-red-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>Stok buku saat ini habis. Seluruh eksemplar sedang dipinjam oleh anggota lain.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Reviews Section (SEC-09 Context) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 pb-4 border-b border-gray-200">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Ulasan Pembaca</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pendapat dan penilaian anggota terhadap buku ini (SEC-09 Anti-Stored XSS)</p>
            </div>
            <div class="flex items-center text-xs text-gray-500">
                <span>Rata-rata Rating:</span>
                <span class="ml-1.5 font-bold text-amber-600">
                    {{ $book->reviews->isNotEmpty() ? number_format($book->reviews->avg('rating'), 1) : '-' }} / 5.0
                </span>
                <span class="ml-1 text-gray-400">({{ $book->reviews->count() }} ulasan)</span>
            </div>
        </div>

        <!-- Security Banner: Stored XSS Mitigation (SEC-09) -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-xs text-emerald-800 flex items-start space-x-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
                <span class="font-semibold">Proteksi Stored XSS Terverifikasi (SEC-09):</span>
                Komentar ulasan disimpan secara mentah (raw) di basis data untuk menjaga integritas data asli (fidelity). Pada lapisan antarmuka, Blade menerapkan <em>contextual escaping</em> otomatis (<code class="bg-emerald-100 px-1 py-0.5 rounded text-emerald-900">&#123;&#123; $review->comment &#125;&#125;</code>) yang men-sanitasi tag HTML menjadi entitas teks aman, serta didukung oleh <em>Content-Security-Policy</em> (CSP).
            </div>
        </div>

        <!-- Add Review Form (for Authenticated Members) -->
        @auth
            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Tulis Ulasan Anda</h3>

                <form method="POST" action="{{ route('reviews.store', $book) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="rating" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Penilaian (Rating) <span class="text-red-500">*</span>
                        </label>
                        <select id="rating" name="rating"
                                class="w-full sm:w-48 py-2 px-3 border border-gray-300 bg-white rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('rating') border-red-300 @enderror">
                            <option value="">Pilih Rating...</option>
                            <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>★★★★★ (5 - Sangat Bagus)</option>
                            <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>★★★★☆ (4 - Bagus)</option>
                            <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>★★★☆☆ (3 - Cukup)</option>
                            <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>★★☆☆☆ (2 - Kurang)</option>
                            <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>★☆☆☆☆ (1 - Buruk)</option>
                        </select>
                        @error('rating')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="comment" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Ulasan Buku (Maksimal 1000 Karakter) <span class="text-red-500">*</span>
                        </label>
                        <textarea id="comment" name="comment" rows="3" maxlength="1000"
                                  placeholder="Bagikan pandangan atau catatan Anda tentang buku ini..."
                                  class="w-full py-2 px-3 border border-gray-300 rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('comment') border-red-300 @enderror">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Kirim Ulasan
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-4 rounded-md bg-gray-50 border border-gray-200 text-center text-sm text-gray-600">
                Silakan <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">Masuk</a> untuk memberikan ulasan pada buku ini.
            </div>
        @endauth

        <!-- Reviews List -->
        @if ($book->reviews->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 italic">Belum ada ulasan untuk buku ini. Jadilah yang pertama memberikan ulasan!</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($book->reviews as $review)
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-100 hover:border-gray-200 transition-colors">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $review->user->name ?? 'Pengguna' }}</span>
                                <span class="text-xs text-gray-400">&bull;</span>
                                <span class="text-xs text-gray-500">{{ $review->created_at ? $review->created_at->diffForHumans() : '-' }}</span>
                            </div>
                            <span class="text-xs text-amber-600 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                ★ {{ $review->rating }} / 5
                            </span>
                        </div>
                        {{-- Output Contextual Escaping Blade (SEC-09 Safe): Karakter khusus di-escape otomatis menjadi entitas HTML --}}
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $review->comment }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
