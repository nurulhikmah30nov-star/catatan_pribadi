<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan halaman login dapat dimuat dan memuat token CSRF.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    /**
     * Memastikan halaman registrasi dapat dimuat dan memuat token CSRF.
     */
    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    /**
     * Memastikan pengguna dapat mendaftar akun baru dan sesi otomatis terbentuk.
     */
    public function test_user_can_register_and_is_authenticated(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nurul Hikmah',
            'email' => 'nurul@catatan.local',
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Nurul Hikmah',
            'email' => 'nurul@catatan.local',
        ]);
        $response->assertRedirect('/dashboard');
    }

    /**
     * Memastikan pengguna dapat login dengan kredensial valid dan ID sesi diregenerasi (Anti-Session Fixation).
     */
    public function test_user_can_login_with_valid_credentials_and_session_is_regenerated(): void
    {
        $user = User::factory()->create([
            'email' => 'budi@catatan.local',
            'password' => Hash::make('PasswordAman123!'),
        ]);

        $this->get('/login');
        $oldSessionId = session()->getId();

        $response = $this->post('/login', [
            'email' => 'budi@catatan.local',
            'password' => 'PasswordAman123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');

        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId, 'Session ID wajib diregenerasi setelah login berhasil.');
    }

    /**
     * Memastikan login dengan password salah ditolak dan pengguna tetap berstatus guest.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'nurul@catatan.local',
            'password' => Hash::make('PasswordBenar123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'nurul@catatan.local',
            'password' => 'PasswordSalah123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Memastikan rate limiter membatasi percobaan login (throttle:5,1) saat terjadi brute force.
     */
    public function test_login_attempts_are_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'target@catatan.local',
            'password' => Hash::make('PasswordBenar123!'),
        ]);

        // Kirim 5 percobaan login gagal
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'target@catatan.local',
                'password' => 'PasswordSalah!',
            ]);
            $this->assertGuest();
        }

        // Percobaan ke-6 harus ditolak oleh middleware throttle (HTTP 429 Too Many Requests)
        $response = $this->post('/login', [
            'email' => 'target@catatan.local',
            'password' => 'PasswordSalah!',
        ]);

        $response->assertStatus(429);
    }

    /**
     * Memastikan logout membatalkan sesi dan merotasi token CSRF.
     */
    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $oldCsrfToken = csrf_token();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'Anda telah berhasil keluar.');
    }

    /**
     * Memastikan halaman dashboard hanya dapat diakses oleh pengguna yang sudah terotentikasi.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Memastikan pengguna terotentikasi dapat mengakses halaman dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Nurul Hikmah',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Nurul Hikmah');
        $response->assertSee('Dashboard Catatan');
    }
}
