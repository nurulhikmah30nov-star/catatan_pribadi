<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class VulnerableDemoController extends Controller
{
    /**
     * Halaman Utama Demonstrasi Versi Rentan (Loopback Localhost: 127.0.0.1:8001).
     */
    public function index(): View
    {
        $books = Book::where('is_archived', false)->orderBy('id')->get();
        $loans = Loan::with(['user', 'book'])->latest('id')->take(5)->get();

        return view('vulnerable.index', compact('books', 'loans'));
    }

    /**
     * Syarat 8 (SEC-08 KONDISI RENTAN): SQL Injection via Raw String Concatenation.
     * Celah keamanan terjadi karena input pengguna disambung langsung ke klausa SQL tanpa prepared statements.
     */
    public function search(Request $request): View
    {
        $query = $request->query('q', '');
        $results = [];

        if ($query !== '') {
            // CELAH KERENTANAN SENGAJA: String concatenation mentah pada SQL kueri
            $rawSql = "SELECT * FROM books WHERE title LIKE '%" . $query . "%'";

            try {
                $results = DB::select($rawSql);
            } catch (\Throwable $e) {
                // Menampilkan error SQL sintaks jika injeksi menyebabkan sintaks rusak
                return view('vulnerable.books.search', [
                    'query' => $query,
                    'results' => [],
                    'rawSql' => $rawSql,
                    'sqlError' => $e->getMessage(),
                ]);
            }
        }

        return view('vulnerable.books.search', [
            'query' => $query,
            'results' => $results,
            'rawSql' => $rawSql ?? "SELECT * FROM books WHERE title LIKE '%...%'",
            'sqlError' => null,
        ]);
    }

    /**
     * Syarat 9 (SEC-09 KONDISI RENTAN): Detail Buku & Ulasan Stored XSS.
     * Ulasan pengguna dirender langsung pada Blade menggunakan tag unescaped {!! $review->comment !!}.
     */
    public function showBook(int $id): View
    {
        $book = Book::with(['reviews.user'])->findOrFail($id);

        return view('vulnerable.books.show', compact('book'));
    }

    /**
     * Simpan ulasan pada versi rentan tanpa sanitasi input.
     */
    public function storeReview(Request $request, int $id)
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'rating' => 'required|integer',
            'comment' => 'required|string',
        ]);

        $userId = Auth::id() ?? User::where('role', 'user')->first()->id ?? 1;

        Review::create([
            'book_id' => $book->id,
            'user_id' => $userId,
            'rating' => (int) $request->input('rating'),
            'comment' => $request->input('comment'), // Disimpan mentah
        ]);

        return redirect()->route('vuln.books.show', $book->id)
            ->with('success', 'Ulasan versi rentan berhasil disimpan (payload XSS akan dirender langsung!).');
    }

    /**
     * Syarat 10 (SEC-10 KONDISI RENTAN): Detail Pinjaman Tanpa Pengecekan Kepemilikan (IDOR).
     * Endpoint mengambil ID pinjaman secara langsung tanpa memeriksa apakah peminjam adalah Auth::id().
     */
    public function showLoan(int $id): View
    {
        // CELAH KERENTANAN SENGAJA: findOrFail($id) tanpa memeriksa Auth::id() atau LoanPolicy
        $loan = Loan::with(['user', 'book'])->findOrFail($id);

        return view('vulnerable.loans.show', compact('loan'));
    }

    /**
     * Helper Simulasi Login Cepat untuk Demo Skenario User A vs User B.
     */
    public function loginAs(string $email)
    {
        $user = User::where('email', $email)->firstOrFail();
        Auth::login($user);

        return back()->with('status', "Berhasil beralih sesi sebagai: {$user->name} ({$user->email})");
    }
}
