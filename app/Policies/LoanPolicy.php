<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat riwayat peminjaman tertentu (Anti-IDOR / SEC-10).
     * Pengguna hanya boleh melihat jika $user->id === $loan->user_id ATAU $user->role === 'admin'.
     */
    public function view(User $user, Loan $loan): bool
    {
        return (int) $user->id === (int) $loan->user_id || $user->role === 'admin';
    }

    /**
     * Menentukan apakah pengguna berhak memproses pengembalian buku pinjaman.
     */
    public function returnBook(User $user, Loan $loan): bool
    {
        return (int) $user->id === (int) $loan->user_id || $user->role === 'admin';
    }
}
