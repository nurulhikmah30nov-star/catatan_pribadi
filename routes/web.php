<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\VulnerableNoteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — CatatanSecure
| Aplikasi Catatan Pribadi Terenkripsi (NIM 230170187, Digit 7)
|--------------------------------------------------------------------------
|
| Rute-rute dikelompokkan sesuai tiket implementasi:
|   - Autentikasi (TKT-003)
|   - CRUD Catatan + Pencarian Anti-SQLi (TKT-007)
|   - Enkripsi Transparan Catatan (TKT-008)
|   - Otorisasi Anti-IDOR (TKT-009)
|   - Teks Bebas Anti-XSS (TKT-010)
|   - Admin Panel (TKT-011)
|   - Demo Versi Rentan Terisolasi (TKT-014)
|
*/

// ─────────────────────────────────────────────────────────
// Halaman Sambutan (Welcome)
// ─────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ─────────────────────────────────────────────────────────
// Autentikasi Manual (TKT-003) — Hanya untuk tamu (guest)
// ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ─────────────────────────────────────────────────────────
// Area Terotentikasi — Hanya untuk pengguna yang login
// ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard Utama
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $notesCount     = $user->notes()->count();
        $encryptedCount = $user->notes()->where('content', 'like', 'v1:%')->count();
        $archivedCount  = $user->notes()->where('is_archived', true)->count();

        return view('dashboard', compact('notesCount', 'encryptedCount', 'archivedCount'));
    })->name('dashboard');

    // ── CRUD Catatan Pribadi & Pencarian Anti-SQLi (TKT-007 / SEC-08) ──────
    Route::get('/notes/search', [NoteController::class, 'search'])->name('notes.search');
    Route::resource('notes', NoteController::class);

    // ── Admin Panel: Kelola Catatan & Pengguna (TKT-011) ───────────────────
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/notes', [AdminController::class, 'allNotes'])->name('notes.index');
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ─────────────────────────────────────────────────────────
// Demo Versi Rentan Terisolasi (TKT-014 / DEC-05)
// HANYA LOOPBACK 127.0.0.1:8001 — DILARANG bind 0.0.0.0
// ─────────────────────────────────────────────────────────
Route::middleware(['loopback.only'])->prefix('vuln')->name('vuln.')->group(function () {
    Route::get('/', [VulnerableNoteController::class, 'index'])->name('index');
    Route::get('/notes/search', [VulnerableNoteController::class, 'search'])->name('notes.search');
    Route::get('/notes/{id}', [VulnerableNoteController::class, 'show'])->name('notes.show');
    Route::post('/notes/{id}/comment', [VulnerableNoteController::class, 'updateComment'])->name('notes.comment');
    Route::get('/login-as/{email}', [VulnerableNoteController::class, 'loginAs'])->name('login.as');
});
