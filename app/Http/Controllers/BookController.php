<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Menampilkan daftar katalog buku aktif dengan paginasi dan pencarian aman (SEC-08).
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        
        // Allowlist ketat untuk parameter pengurutan demi mencegah SQLi berbasis ORDER BY
        $allowedSort = ['title', 'author', 'year', 'stock'];
        $sortBy = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'title';
        $direction = strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $booksQuery = Book::query();

        // Anggota umum hanya dapat melihat buku yang tidak diarsipkan
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        if (!$isAdmin || !$request->boolean('include_archived')) {
            $booksQuery->where('is_archived', false);
        }

        // Prepared statement dengan parameter binding otomatis melalui Eloquent
        if ($query !== '') {
            $booksQuery->where(function ($sub) use ($query) {
                $sub->where('title', 'like', '%' . $query . '%')
                    ->orWhere('author', 'like', '%' . $query . '%')
                    ->orWhere('category', 'like', '%' . $query . '%');
            });
        }

        $books = $booksQuery->orderBy($sortBy, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('books.index', compact('books', 'query', 'sortBy', 'direction'));
    }

    /**
     * Endpoint pencarian buku (FR-004 / SEC-08).
     * Menerima query q dan mengarahkan ke logika query terparameter yang identik.
     */
    public function search(Request $request): View
    {
        return $this->index($request);
    }

    /**
     * Menampilkan form penambahan buku baru (Khusus Admin).
     */
    public function create(): View
    {
        $this->ensureUserIsAdmin();

        return view('books.create');
    }

    /**
     * Menyimpan data buku baru ke basis data (Khusus Admin).
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $this->ensureUserIsAdmin();

        $book = Book::create($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', 'Buku "' . $book->title . '" berhasil ditambahkan ke katalog.');
    }

    /**
     * Menampilkan detail informasi satu buku beserta riwayat ulasan.
     */
    public function show(Book $book): View
    {
        // Jika buku diarsipkan dan bukan admin, tolak akses
        if ($book->is_archived && (!Auth::check() || Auth::user()->role !== 'admin')) {
            abort(404, 'Buku tidak ditemukan atau telah diarsipkan.');
        }

        $book->load(['reviews.user']);

        // Cek apakah pengguna saat ini sedang aktif meminjam buku ini
        $activeLoan = null;
        if (Auth::check()) {
            $activeLoan = $book->loans()
                ->where('user_id', Auth::id())
                ->where('status', 'borrowed')
                ->first();
        }

        return view('books.show', compact('book', 'activeLoan'));
    }

    /**
     * Menampilkan form pengubahan data buku (Khusus Admin).
     */
    public function edit(Book $book): View
    {
        $this->ensureUserIsAdmin();

        return view('books.edit', compact('book'));
    }

    /**
     * Memperbarui data buku pada basis data (Khusus Admin).
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->ensureUserIsAdmin();

        $book->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', 'Informasi buku "' . $book->title . '" berhasil diperbarui.');
    }

    /**
     * Mengarsipkan buku dari katalog aktif (Soft-Delete / Archive).
     * Mencegah pengarsipan bila buku masih memiliki eksemplar yang sedang dipinjam.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->ensureUserIsAdmin();

        // Validasi perlindungan peminjaman aktif
        if ($book->loans()->where('status', 'borrowed')->exists()) {
            return back()->with('error', 'Buku tidak dapat diarsipkan karena masih ada eksemplar yang sedang dipinjam.');
        }

        $book->update(['is_archived' => true]);

        return redirect()->route('books.index')
            ->with('success', 'Buku "' . $book->title . '" berhasil diarsipkan dari katalog.');
    }

    /**
     * Memastikan pengguna memiliki peran administrator aktif.
     */
    protected function ensureUserIsAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Hanya administrator yang diizinkan mengelola katalog buku.');
        }
    }
}
