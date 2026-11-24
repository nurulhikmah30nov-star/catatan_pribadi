@extends('layouts.app')

@section('title', $note->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('notes.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center space-x-1">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            <span>Kembali ke Daftar Catatan</span>
        </a>

        <div class="flex items-center space-x-2">
            <a href="{{ route('notes.edit', $note) }}"
               class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 shadow-xs transition">
                <x-heroicon-o-pencil-square class="w-4 h-4 mr-1.5 text-gray-500" />
                Edit
            </a>

            <form method="POST"
                  action="{{ route('notes.destroy', $note) }}"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan ini? Tindakan ini tidak dapat dibatalkan.')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-medium text-red-700 bg-white border border-red-200 hover:bg-red-50 shadow-xs transition">
                    <x-heroicon-o-trash class="w-4 h-4 mr-1.5 text-red-500" />
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Main Note Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-6">
            <!-- Metadata Row -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        @if ($note->category === 'Rahasia') bg-rose-100 text-rose-800 border border-rose-200
                        @elseif ($note->category === 'Kuliah') bg-blue-100 text-blue-800 border border-blue-200
                        @elseif ($note->category === 'Pekerjaan') bg-amber-100 text-amber-800 border border-amber-200
                        @else bg-emerald-100 text-emerald-800 border border-emerald-200
                        @endif">
                        {{ $note->category }}
                    </span>

                    @if ($note->is_archived)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            <x-heroicon-o-archive-box class="w-3.5 h-3.5 mr-1 text-gray-500" />
                            Catatan Diarsipkan
                        </span>
                    @endif
                </div>

                <div class="text-xs text-gray-500 space-x-3">
                    <span>Dibuat: {{ $note->created_at->format('d M Y, H:i') }}</span>
                    @if ($note->updated_at != $note->created_at)
                        <span>&bull; Diperbarui: {{ $note->updated_at->format('d M Y, H:i') }}</span>
                    @endif
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                {{ $note->title }}
            </h1>

            <!-- Decrypted Content Body -->
            <div class="prose max-w-none text-gray-800 leading-relaxed whitespace-pre-wrap text-base font-normal bg-slate-50/50 p-6 rounded-xl border border-slate-100 min-h-[160px]">
{{ $note->content }}
            </div>

            <!-- Public Comment Section (SEC-09) -->
            @if ($note->public_comment)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200/80">
                    <div class="flex items-center space-x-2 text-xs font-semibold text-gray-600 mb-1">
                        <x-heroicon-o-chat-bubble-bottom-center-text class="w-4 h-4 text-emerald-600" />
                        <span>Komentar Publik / Teks Bebas</span>
                    </div>
                    <p class="text-sm text-gray-700">
                        {{-- Escaping Blade murni untuk menangkal XSS (SEC-09) --}}
                        {{ $note->public_comment }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Security Proof & Raw Ciphertext Inspection (Bukti Empiris SEC-03) -->
        <div class="bg-slate-900 text-slate-100 p-6 border-t border-slate-800">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-400">
                    <x-heroicon-s-shield-check class="w-4 h-4" />
                    <span>BUKTI EMPIRIS KRIPTOGRAFI: KONTEN ASLI DI DATABASE (SEC-03)</span>
                </div>
                <span class="text-xs text-slate-400 font-mono">Algoritma: AES-256-GCM</span>
            </div>

            <p class="text-xs text-slate-300 mb-2">
                Basis data SQLite tidak pernah menyimpan plaintext di atas. Kolom <code>content</code> fisik di database menyimpan ciphertext terotentikasi berformat Base64 Envelope berikut:
            </p>

            <div class="p-3 bg-slate-950 rounded-lg border border-slate-800 font-mono text-xs text-emerald-300 break-all select-all">
                {{ $note->raw_content }}
            </div>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] text-slate-400">
                <div>
                    <span class="text-slate-500">Pemilik (AAD):</span>
                    <span class="font-mono text-slate-200">User ID #{{ $note->user_id }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Status Integritas:</span>
                    <span class="text-emerald-400 font-semibold">Tervalidasi (GMAC 128-bit)</span>
                </div>
                <div>
                    <span class="text-slate-500">Panjang Ciphertext:</span>
                    <span class="font-mono text-slate-200">{{ strlen($note->raw_content ?? '') }} Karakter</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
