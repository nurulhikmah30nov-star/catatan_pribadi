<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Dashboard ringkasan analitik dan metrik sistem untuk Administrator.
     */
    public function dashboard(): View
    {
        $totalUsers = User::count();
        $totalNotes = Note::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalRegularUsers = User::where('role', 'user')->count();
        $archivedNotes = Note::where('is_archived', true)->count();

        $recentNotes = Note::with('user')->latest()->take(6)->get();
        $recentUsers = User::latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalNotes',
            'totalAdmins',
            'totalRegularUsers',
            'archivedNotes',
            'recentNotes',
            'recentUsers'
        ));
    }

    /**
     * Menampilkan daftar seluruh catatan milik semua pengguna di sistem.
     * Dilengkapi fitur pencarian lintas pengguna berbasis parameterized binding (SEC-08).
     */
    public function allNotes(Request $request): View
    {
        $query = Note::with('user');

        if ($request->filled('q')) {
            $searchTerm = trim($request->input('q'));
            $query->where(function ($sub) use ($searchTerm) {
                $sub->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('category', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'like', '%' . $searchTerm . '%')
                           ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'archived') {
                $query->where('is_archived', true);
            } elseif ($request->input('status') === 'active') {
                $query->where('is_archived', false);
            }
        }

        $notes = $query->latest()->paginate(12)->withQueryString();

        return view('admin.notes', [
            'notes' => $notes,
            'categories' => NoteController::CATEGORIES,
            'currentQuery' => $request->input('q', ''),
            'currentCategory' => $request->input('category', ''),
            'currentStatus' => $request->input('status', ''),
        ]);
    }

    /**
     * Manajemen akun pengguna terdaftar.
     */
    public function users(Request $request): View
    {
        $query = User::withCount('notes');

        if ($request->filled('q')) {
            $searchTerm = trim($request->input('q'));
            $query->where(function ($sub) use ($searchTerm) {
                $sub->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'currentQuery' => $request->input('q', ''),
            'currentRole' => $request->input('role', ''),
        ]);
    }

    /**
     * Menghapus akun pengguna dari sistem dengan proteksi khusus (FR-012):
     * 1. Menolak jika admin menghapus akun dirinya sendiri saat sedang aktif login.
     * 2. Menolak jika akun yang dihapus adalah satu-satunya administrator yang tersisa.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        // Aturan 1: Dilarang menghapus akun sendiri saat sedang login
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tindakan Ditolak: Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        // Aturan 2: Dilarang menghapus admin jika ia adalah satu-satunya admin tersisa di sistem
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tindakan Kritis Ditolak: Tidak dapat menghapus satu-satunya akun administrator aktif yang tersisa di sistem.');
            }
        }

        $userName = $user->name;
        $notesCount = $user->notes()->count();

        // Penghapusan user akan memicu cascade delete pada seluruh catatan miliknya di SQLite
        $user->delete();

        return back()->with('success', "Akun pengguna '{$userName}' beserta {$notesCount} catatan miliknya berhasil dihapus dari sistem.");
    }
}
