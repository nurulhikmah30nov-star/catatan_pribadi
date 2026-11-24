<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar catatan miliknya.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah pengguna dapat membuat catatan baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah pengguna berhak melihat catatan spesifik (SEC-10 Anti-IDOR).
     * Hanya pemilik catatan yang sah atau Administrator yang diizinkan.
     */
    public function view(User $user, Note $note): bool
    {
        return $user->id === $note->user_id || $user->isAdmin();
    }

    /**
     * Tentukan apakah pengguna berhak memperbarui catatan spesifik (SEC-10 Anti-IDOR).
     * Hanya pemilik catatan yang sah atau Administrator yang diizinkan.
     */
    public function update(User $user, Note $note): bool
    {
        return $user->id === $note->user_id || $user->isAdmin();
    }

    /**
     * Tentukan apakah pengguna berhak menghapus catatan spesifik (SEC-10 Anti-IDOR).
     * Hanya pemilik catatan yang sah atau Administrator yang diizinkan.
     */
    public function delete(User $user, Note $note): bool
    {
        return $user->id === $note->user_id || $user->isAdmin();
    }
}
