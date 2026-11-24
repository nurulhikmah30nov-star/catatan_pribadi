@extends('vulnerable.layout')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-800">
                    Syarat 10 (SEC-10): Demonstrasi IDOR (Insecure Direct Object Reference)
                </span>
                <h1 class="text-2xl font-bold text-slate-900 mt-2">
                    Detail Peminjaman #{{ $loan->id }}
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Celah: Endpoint ini menampilkan data tanpa memverifikasi apakah <code>user_id</code> pinjaman cocok dengan pengguna yang sedang login.
                </p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $loan->status === 'borrowed' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    Status: {{ ucfirst($loan->status) }}
                </span>
            </div>
        </div>

        <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg text-xs text-purple-900 mb-6">
            <strong>Kerentanan Sengaja (IDOR):</strong> Sesi Anda saat ini adalah <strong>{{ Auth::user()->name ?? 'Tamu' }} (ID: {{ Auth::id() ?? '-' }})</strong>, 
            namun Anda dapat melihat catatan pribadi milik <strong>{{ $loan->user->name }} (ID: {{ $loan->user_id }})</strong> 
            hanya dengan mengganti angka ID pada bilah URL peramban (misal: <code>/vuln/loans/1</code> vs <code>/vuln/loans/2</code>).
        </div>

        {{-- RINCIAN PINJAMAN YANG BOCOR --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-lg border border-slate-200">
            <div>
                <h2 class="text-xs uppercase font-bold text-slate-500 mb-3 tracking-wider">Informasi Peminjam (Data Bocor)</h2>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-slate-500">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900 ml-2">{{ $loan->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Email:</span>
                        <span class="font-mono text-purple-800 ml-2">{{ $loan->user->email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">User ID Database:</span>
                        <span class="font-mono text-slate-800 ml-2">#{{ $loan->user_id }}</span>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xs uppercase font-bold text-slate-500 mb-3 tracking-wider">Informasi Buku & Tanggal</h2>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-slate-500">Judul Buku:</span>
                        <span class="font-bold text-slate-900 ml-2">{{ $loan->book->title ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Tanggal Pinjam:</span>
                        <span class="font-mono text-slate-800 ml-2">{{ $loan->loan_date ? $loan->loan_date->toDateString() : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Jatuh Tempo:</span>
                        <span class="font-mono text-slate-800 ml-2">{{ $loan->due_date ? $loan->due_date->toDateString() : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Tanggal Kembali:</span>
                        <span class="font-mono text-slate-800 ml-2">{{ $loan->returned_at ? $loan->returned_at->toDateTimeString() : 'Belum Dikembalikan' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- UJI ID LAIN --}}
        <div class="mt-6 flex items-center justify-between pt-4 border-t border-slate-200 text-xs">
            <span class="text-slate-500">Eksploitasi Cepat ID Transaksi:</span>
            <div class="flex gap-2">
                <a href="{{ route('vuln.loans.show', 1) }}" class="px-3 py-1.5 rounded {{ $loan->id == 1 ? 'bg-purple-700 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Loan #1 (User A)
                </a>
                <a href="{{ route('vuln.loans.show', 2) }}" class="px-3 py-1.5 rounded {{ $loan->id == 2 ? 'bg-purple-700 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Loan #2 (User B)
                </a>
                <a href="{{ route('vuln.loans.show', 3) }}" class="px-3 py-1.5 rounded {{ $loan->id == 3 ? 'bg-purple-700 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Loan #3
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
