@extends('vulnerable.layout')

@section('content')
<div class="space-y-6">
    {{-- DETAIL BUKU --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-800">
                    Syarat 9 (SEC-09): Demonstrasi Stored XSS
                </span>
                <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ $book->title }}</h1>
                <p class="text-sm text-slate-600 mt-1">
                    Penulis: <span class="font-semibold">{{ $book->author }}</span> | Penerbit: {{ $book->publisher }} ({{ $book->year }})
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500 font-mono">Buku ID: #{{ $book->id }}</span>
            </div>
        </div>

        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
            <strong>Kerentanan Sengaja:</strong> Seluruh ulasan pembaca di bawah dirender menggunakan sintaks Blade unescaped 
            <code>@verbatim {!! $review->comment !!} @endverbatim</code> tanpa melalui <code>e()</code> atau <code>htmlspecialchars()</code>.
            Jika ulasan memuat tag <code>&lt;script&gt;</code> atau event handler JavaScript, skrip akan langsung dieksekusi oleh peramban pengguna.
        </div>
    </div>

    {{-- DAFTAR ULASAN DENGAN UNESCAPED RENDERING --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">
            Ulasan Pembaca (Rendered via <span class="text-red-600 font-mono">@verbatim {!! $review->comment !!} @endverbatim</span>)
        </h2>

        <div class="space-y-4">
            @forelse ($book->reviews as $review)
                <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm text-slate-900">{{ $review->user->name ?? 'Anonim' }}</span>
                            <span class="text-xs text-amber-600 font-bold">★ {{ $review->rating }} / 5</span>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">{{ $review->created_at->diffForHumans() }}</span>
                    </div>

                    {{-- CELAH KERENTANAN SENGAJA (UNESCAPED RENDERING) --}}
                    <div class="text-sm text-slate-800 vuln-comment-container">
                        {!! $review->comment !!}
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-slate-400 text-sm">
                    Belum ada ulasan untuk buku ini. Silakan tulis ulasan pertama di bawah!
                </div>
            @endforelse
        </div>
    </div>

    {{-- FORMULIR TULIS ULASAN RENTAN --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
        <h3 class="text-md font-bold text-slate-900 mb-3">Tulis Ulasan (Versi Rentan - Payload XSS Diizinkan)</h3>
        
        <form method="POST" action="{{ route('vuln.reviews.store', $book->id) }}" class="space-y-4">
            @csrf
            <div>
                <label for="rating" class="block text-xs font-semibold text-slate-700 mb-1">Rating Bintang (1-5)</label>
                <select name="rating" id="rating" class="rounded-lg border-slate-300 text-sm px-3 py-2 border w-32">
                    <option value="5">★ 5 - Sangat Bagus</option>
                    <option value="4">★ 4 - Bagus</option>
                    <option value="3">★ 3 - Cukup</option>
                    <option value="2">★ 2 - Kurang</option>
                    <option value="1">★ 1 - Buruk</option>
                </select>
            </div>

            <div>
                <label for="comment" class="block text-xs font-semibold text-slate-700 mb-1">Komentar / Payload XSS</label>
                <textarea name="comment" 
                          id="comment" 
                          rows="3" 
                          placeholder="Tulis ulasan biasa atau masukkan payload: <script>alert('XSS-Vuln')</script>"
                          class="w-full rounded-lg border-slate-300 text-sm px-3 py-2 border font-mono"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex gap-2">
                    <button type="button" 
                            onclick="document.getElementById('comment').value = '<script>alert(\'XSS-Vuln-Nurul0187\')<\/script>'"
                            class="text-xs px-2.5 py-1 rounded bg-amber-100 text-amber-800 hover:bg-amber-200">
                        Isi Payload Script Alert
                    </button>
                    <button type="button" 
                            onclick="document.getElementById('comment').value = '<img src=x onerror=\'alert(\"XSS-Cookie: \" + document.cookie)\'>'"
                            class="text-xs px-2.5 py-1 rounded bg-amber-100 text-amber-800 hover:bg-amber-200">
                        Isi Payload Img Onerror
                    </button>
                </div>

                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    Kirim Ulasan Rentan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
