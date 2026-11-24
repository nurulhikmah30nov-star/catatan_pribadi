<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * Kategori catatan yang didukung aplikasi.
     *
     * @var list<string>
     */
    public const CATEGORIES = ['Pribadi', 'Kuliah', 'Pekerjaan', 'Rahasia', 'Lainnya'];

    /**
     * Menampilkan daftar catatan milik pengguna yang sedang login.
     * Fitur pencarian menggunakan parameterized queries (SEC-08) kebal SQL Injection.
     */
    public function index(Request $request): View
    {
        $query = Auth::user()->notes();

        // Pencarian aman berbasis parameter binding (SEC-08)
        if ($request->filled('q')) {
            $searchTerm = trim($request->input('q'));
            $query->where(function ($sub) use ($searchTerm) {
                $sub->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('category', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter berdasarkan kategori jika dipilih
        if ($request->filled('category') && in_array($request->input('category'), self::CATEGORIES, true)) {
            $query->where('category', $request->input('category'));
        }

        // Filter status arsip
        if ($request->filled('status')) {
            if ($request->input('status') === 'archived') {
                $query->where('is_archived', true);
            } elseif ($request->input('status') === 'active') {
                $query->where('is_archived', false);
            }
        }

        $notes = $query->latest()->paginate(9)->withQueryString();

        return view('notes.index', [
            'notes' => $notes,
            'categories' => self::CATEGORIES,
            'currentQuery' => $request->input('q', ''),
            'currentCategory' => $request->input('category', ''),
            'currentStatus' => $request->input('status', ''),
        ]);
    }

    /**
     * Endpoint alternatif pencarian catatan (SEC-08).
     */
    public function search(Request $request): View|RedirectResponse
    {
        return $this->index($request);
    }

    /**
     * Menampilkan form pembuatan catatan baru.
     */
    public function create(): View
    {
        return view('notes.create', [
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Menyimpan catatan baru ke database.
     * Isi catatan otomatis dienkripsi dengan AES-256-GCM melalui mutator Note (SEC-03).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'public_comment' => ['nullable', 'string', 'max:1000'],
            'is_archived' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul catatan wajib diisi.',
            'title.max' => 'Judul catatan tidak boleh lebih dari 255 karakter.',
            'content.required' => 'Isi catatan wajib diisi.',
            'category.required' => 'Kategori catatan wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
        ]);

        $note = Auth::user()->notes()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'public_comment' => $validated['public_comment'] ?? null,
            'is_archived' => $request->boolean('is_archived'),
        ]);

        return redirect()
            ->route('notes.show', $note)
            ->with('success', 'Catatan pribadi berhasil dibuat dan dienkripsi.');
    }

    /**
     * Menampilkan detail catatan tertentu.
     * Validasi kepemilikan anti-IDOR melalui Policy (SEC-10).
     */
    public function show(Note $note): View
    {
        Gate::authorize('view', $note);

        return view('notes.show', [
            'note' => $note,
        ]);
    }

    /**
     * Menampilkan form edit catatan.
     */
    public function edit(Note $note): View
    {
        Gate::authorize('update', $note);

        return view('notes.edit', [
            'note' => $note,
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Memperbarui catatan di database.
     */
    public function update(Request $request, Note $note): RedirectResponse
    {
        Gate::authorize('update', $note);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'public_comment' => ['nullable', 'string', 'max:1000'],
            'is_archived' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul catatan wajib diisi.',
            'title.max' => 'Judul catatan tidak boleh lebih dari 255 karakter.',
            'content.required' => 'Isi catatan wajib diisi.',
            'category.required' => 'Kategori catatan wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
        ]);

        $note->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'public_comment' => $validated['public_comment'] ?? null,
            'is_archived' => $request->boolean('is_archived'),
        ]);

        return redirect()
            ->route('notes.show', $note)
            ->with('success', 'Catatan berhasil diperbarui.');
    }

    /**
     * Menghapus catatan dari database.
     */
    public function destroy(Note $note): RedirectResponse
    {
        Gate::authorize('delete', $note);

        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Catatan berhasil dihapus.');
    }
}
