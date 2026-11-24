<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Menyimpan ulasan buku baru dari pengguna terautentikasi (FR-010 / SEC-09).
     * Input teks bebas disimpan secara mentah untuk integritas data asli (fidelity),
     * sedangkan mitigasi Stored XSS ditegakkan pada lapisan presentasi Blade
     * dan diperkuat oleh Content-Security-Policy (CSP).
     */
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ], [
            'comment.required' => 'Kolom ulasan buku wajib diisi.',
            'comment.max' => 'Panjang ulasan maksimal adalah 1000 karakter.',
            'rating.required' => 'Penilaian bintang wajib dipilih.',
            'rating.between' => 'Penilaian bintang harus antara 1 sampai 5.',
        ]);

        $book->reviews()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'rating' => (int) $validated['rating'],
        ]);

        return back()->with('success', 'Ulasan Anda berhasil ditambahkan.');
    }
}
