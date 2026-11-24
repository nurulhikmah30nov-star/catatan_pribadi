<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * AuthController — Autentikasi Manual Murni (TKT-003 / CatatanSecure)
 *
 * Implementasi autentikasi manual tanpa Breeze/Jetstream/Fortify sesuai
 * spesifikasi PRD SEC-01 (Autentikasi), SEC-10 (Sesi aman):
 *   - Regenerasi ID sesi setelah login (anti-session fixation)
 *   - Invalidasi dan rotasi CSRF token saat logout
 *   - Rate limiting 5 percobaan/menit (di-apply via route middleware throttle:5,1)
 *   - Validasi password 8–72 byte (batas kriptografi bcrypt)
 *   - Peran default 'user' — admin hanya bisa dibuat via seeder (TKT-006)
 */
class AuthController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // LOGIN
    // ──────────────────────────────────────────────────────────────

    /**
     * Menampilkan formulir login pengguna.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses autentikasi pengguna secara manual.
     *
     * Proteksi session fixation: Session::regenerate() dipanggil segera
     * setelah Auth::attempt() berhasil (SEC-10).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // SEC-10: Regenerasi ID sesi untuk mencegah serangan session fixation
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan tidak valid.',
        ])->onlyInput('email');
    }

    // ──────────────────────────────────────────────────────────────
    // REGISTER
    // ──────────────────────────────────────────────────────────────

    /**
     * Menampilkan formulir pendaftaran pengguna baru.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Memproses pendaftaran akun pengguna baru.
     *
     * - Peran default: 'user' (pengguna biasa). Admin hanya dibuat via Seeder (TKT-006).
     * - Validasi password: minimal 8 karakter, maksimal 72 karakter (batas bcrypt, SEC-02).
     * - Regenerasi sesi setelah login otomatis pasca-registrasi (SEC-10).
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.unique'       => 'Alamat email sudah terdaftar. Silakan gunakan email lain.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 8 karakter (standar keamanan SEC-02).',
            'password.max'       => 'Kata sandi tidak boleh melebihi 72 karakter (batas kriptografi bcrypt).',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user', // Peran default: pengguna biasa
        ]);

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);

        // SEC-10: Regenerasi sesi saat login otomatis pasca-registrasi
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Pendaftaran berhasil! Selamat datang di CatatanSecure — catatan pribadi Anda kini aman terenkripsi.');
    }

    // ──────────────────────────────────────────────────────────────
    // LOGOUT
    // ──────────────────────────────────────────────────────────────

    /**
     * Memproses logout dengan pembersihan sesi total dan rotasi CSRF token.
     *
     * Urutan wajib (SEC-10):
     *   1. Auth::logout() — hapus kredensial autentikasi dari guard
     *   2. Session::invalidate() — hapus seluruh data sesi (termasuk flash data)
     *   3. Session::regenerateToken() — rotasi token CSRF baru
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // SEC-10: Invalidasi seluruh data sesi dan rotasi token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Anda telah berhasil keluar.');
    }
}
