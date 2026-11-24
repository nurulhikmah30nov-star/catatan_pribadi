<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VulnerableNoteController extends Controller
{
    /**
     * Halaman Utama Demonstrasi Versi Rentan (Loopback Localhost: 127.0.0.1:8001).
     */
    public function index(): View
    {
        $notes = Note::with('user')->orderBy('id')->get();
        $users = User::all();

        return view('vulnerable.index', compact('notes', 'users'));
    }

    /**
     * Syarat 8 (SEC-08 KONDISI RENTAN): SQL Injection via Raw String Concatenation.
     * Celah keamanan sengaja: input pengguna disambung langsung ke kueri SQL tanpa prepared statements.
     */
    public function search(Request $request): View
    {
        $query = $request->query('q', '');
        $results = [];
        $rawSql = null;
        $sqlError = null;

        if ($query !== '') {
            // CELAH KERENTANAN SENGAJA: String concatenation mentah pada SQL kueri
            $rawSql = "SELECT * FROM notes WHERE title LIKE '%" . $query . "%'";

            try {
                $results = DB::select($rawSql);
            } catch (\Throwable $e) {
                $sqlError = $e->getMessage();
            }
        }

        return view('vulnerable.notes.search', [
            'query' => $query,
            'results' => $results,
            'rawSql' => $rawSql ?? "SELECT * FROM notes WHERE title LIKE '%...%'",
            'sqlError' => $sqlError,
        ]);
    }

    /**
     * Syarat 10 (SEC-10 KONDISI RENTAN) & Syarat 9 (SEC-09 KONDISI RENTAN):
     * 1. IDOR: Detail catatan diambil langsung via findOrFail($id) tanpa memeriksa kepemilikan Auth::id().
     * 2. XSS: Teks bebas dirender langsung tanpa escape {!! $note->public_comment !!}.
     */
    public function show(int $id): View
    {
        // CELAH KERENTANAN SENGAJA: findOrFail($id) tanpa memeriksa Auth::id() atau NotePolicy
        $note = Note::with('user')->findOrFail($id);

        return view('vulnerable.notes.show', compact('note'));
    }

    /**
     * Simpan komentar publik pada versi rentan tanpa sanitasi input (Stored XSS).
     */
    public function updateComment(Request $request, int $id): RedirectResponse
    {
        // CELAH KERENTANAN SENGAJA: IDOR (siapa pun bisa ubah catatan siapa pun)
        $note = Note::findOrFail($id);

        $request->validate([
            'public_comment' => 'required|string',
        ]);

        $note->public_comment = $request->input('public_comment');
        $note->save();

        return redirect()->route('vuln.notes.show', $note->id)
            ->with('success', 'Komentar catatan versi rentan berhasil disimpan (payload XSS akan dirender unescaped!).');
    }

    /**
     * Helper Simulasi Login Cepat untuk Demo Skenario User A vs User B.
     */
    public function loginAs(string $email): RedirectResponse
    {
        $user = User::where('email', $email)->firstOrFail();
        Auth::login($user);

        return back()->with('status', "Berhasil beralih sesi sebagai: {$user->name} ({$user->email})");
    }
}
