<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class LoanController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan riwayat peminjaman pribadi milik pengguna yang sedang login (FR-009 / SEC-10).
     * Membatasi kueri hanya untuk ID pengguna yang sedang aktif (Auth::id()).
     */
    public function myLoans(): View
    {
        $loans = Loan::with('book')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(10);

        return view('loans.index', compact('loans'));
    }

    /**
     * Menampilkan rincian transaksi peminjaman buku dengan proteksi otorisasi anti-IDOR (SEC-10).
     * Server memverifikasi LoanPolicy@view sebelum merender data peminjaman.
     */
    public function show(Loan $loan): View
    {
        // Otorisasi server-side: abort(403) jika bukan pemilik atau administrator
        $this->authorize('view', $loan);

        $loan->load(['book', 'user']);

        return view('loans.show', compact('loan'));
    }

    /**
     * Memproses transaksi peminjaman buku dengan pengurangan stok atomik (FR-006 / FR-007).
     * Mencegah kondisi lomba (race condition / overselling) pada buku stok terakhir.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
        ], [
            'book_id.required' => 'Buku yang akan dipinjam wajib dipilih.',
            'book_id.exists' => 'Buku tidak ditemukan dalam katalog.',
        ]);

        $bookId = (int) $validated['book_id'];
        $user = Auth::user();

        return DB::transaction(function () use ($user, $bookId) {
            // Pengurangan stok atomik kondisional: hanya jika stock > 0 dan tidak diarsipkan
            $affected = DB::update(
                'UPDATE books SET stock = stock - 1 WHERE id = ? AND stock > 0 AND is_archived = 0',
                [$bookId]
            );

            if ($affected === 0) {
                return back()->with('error', 'Peminjaman gagal: Stok buku telah habis atau buku sedang tidak tersedia.');
            }

            $loan = Loan::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'loan_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'borrowed',
            ]);

            $redirectUrl = Route::has('loans.my') ? route('loans.my') : route('books.show', $bookId);

            return redirect($redirectUrl)
                ->with('success', 'Buku berhasil dipinjam. Batas pengembalian adalah 7 hari ke depan.');
        });
    }

    /**
     * Memproses pengembalian buku secara idempoten (FR-008).
     * Memastikan request berulang tidak menambah stok buku lebih dari satu kali.
     */
    public function returnBook(Request $request, Loan $loan): RedirectResponse
    {
        // Otorisasi melalui policy: hanya peminjam atau admin yang berhak memproses pengembalian
        $this->authorize('returnBook', $loan);

        return DB::transaction(function () use ($loan) {
            // Pembaruan status bersyarat: hanya jika status saat ini 'borrowed'
            $affected = DB::update(
                "UPDATE loans SET status = 'returned', returned_at = ? WHERE id = ? AND status = 'borrowed'",
                [now(), $loan->id]
            );

            if ($affected > 0) {
                // Tambah stok kembali HANYA saat transisi status pertama kali berhasil
                DB::update('UPDATE books SET stock = stock + 1 WHERE id = ?', [$loan->book_id]);

                return back()->with('success', 'Buku telah berhasil dicatat dikembalikan. Terima kasih.');
            }

            // Respons idempoten jika permintaan dikirimkan berulang kali
            return back()->with('notice', 'Buku ini sudah tercatat telah dikembalikan sebelumnya.');
        });
    }
}
