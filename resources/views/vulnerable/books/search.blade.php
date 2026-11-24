@extends('vulnerable.layout')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">
                    Syarat 8 (SEC-08): Demonstrasi Pencarian Rentan SQL Injection
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Input pencarian dirangkai secara langsung ke dalam string kueri SQL SQLite tanpa parameter binding.
                </p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded bg-red-100 text-red-800 border border-red-200">
                RAW QUERY CONCATENATION
            </span>
        </div>

        {{-- FORM PENCARIAN --}}
        <form method="GET" action="{{ route('vuln.search') }}" class="flex gap-2">
            <input type="text" 
                   name="q" 
                   value="{{ $query }}" 
                   placeholder="Ketik judul buku atau suntikkan payload SQLi..." 
                   class="flex-1 rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500 text-sm px-4 py-2 border font-mono">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                Cari (Rentan)
            </button>
        </form>

        {{-- DEBUG QUERY BOX --}}
        <div class="mt-4 p-3 bg-slate-900 rounded-lg text-xs font-mono text-slate-200">
            <div class="text-slate-400 mb-1">// Kueri SQL yang dieksekusi ke SQLite:</div>
            <code class="text-amber-300">{{ $rawSql }}</code>
        </div>

        @if ($sqlError)
            <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-xs font-mono">
                <strong>SQLite Exception:</strong> {{ $sqlError }}
            </div>
        @endif

        {{-- QUICK PAYLOAD SHORTCUTS --}}
        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="text-slate-500 py-1">Payload Uji Cepat:</span>
            <a href="{{ route('vuln.search', ['q' => "' OR '1'='1' -- "]) }}" class="px-2.5 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 font-mono">
                ' OR '1'='1' -- 
            </a>
            <a href="{{ route('vuln.search', ['q' => "' UNION SELECT id, name, email, password, role, null, null FROM users -- "]) }}" class="px-2.5 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 font-mono">
                UNION SELECT users
            </a>
            <a href="{{ route('vuln.search', ['q' => 'Laravel']) }}" class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 font-mono">
                Normal: Laravel
            </a>
        </div>
    </div>

    {{-- HASIL PENCARIAN --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Hasil Eksekusi Kueri ({{ count($results) }} Baris Data)</span>
            <span class="text-xs font-normal text-slate-500">Kondisi SEBELUM: Seluruh data terekspos jika klausul WHERE dibypass</span>
        </h2>

        @if (count($results) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b">
                        <tr>
                            <th class="py-2.5 px-4">ID</th>
                            <th class="py-2.5 px-4">Judul / Kolom 2</th>
                            <th class="py-2.5 px-4">Penulis / Kolom 3</th>
                            <th class="py-2.5 px-4">Penerbit / Kolom 4</th>
                            <th class="py-2.5 px-4">Tahun / Kolom 5</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @foreach ($results as $row)
                            <tr class="hover:bg-red-50/50">
                                <td class="py-2.5 px-4">{{ $row->id ?? '-' }}</td>
                                <td class="py-2.5 px-4 font-semibold text-slate-900">{{ $row->title ?? ($row->name ?? '-') }}</td>
                                <td class="py-2.5 px-4 text-red-800">{{ $row->author ?? ($row->email ?? '-') }}</td>
                                <td class="py-2.5 px-4">{{ $row->publisher ?? ($row->password ?? '-') }}</td>
                                <td class="py-2.5 px-4">{{ $row->year ?? ($row->role ?? '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400 text-sm">
                Tidak ada data yang cocok dengan kueri.
            </div>
        @endif
    </div>
</div>
@endsection
