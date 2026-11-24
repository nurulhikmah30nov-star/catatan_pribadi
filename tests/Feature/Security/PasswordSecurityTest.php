<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Syarat 2 (SEC-02): Memastikan kata sandi di database tersimpan dalam format hash bcrypt 60 karakter dengan cost/rounds 12.
     */
    public function test_password_is_hashed_using_bcrypt_with_rounds_12(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nurul Hikmah',
            'email' => 'nurul@catatan.local',
            'password' => 'SamaPersis123!',
            'password_confirmation' => 'SamaPersis123!',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'nurul@catatan.local')->firstOrFail();
        $hash = $user->password;

        // 1. Validasi panjang hash tepat 60 karakter
        $this->assertEquals(60, strlen($hash), 'Panjang hash bcrypt wajib tepat 60 karakter.');

        // 2. Validasi prefix $2y$12$ (bcrypt cost 12)
        $this->assertStringStartsWith('$2y$12$', $hash, 'Hash wajib menggunakan algoritma bcrypt ($2y$) dengan work factor/rounds 12.');

        // 3. Validasi metadata password via password_get_info PHP
        $info = password_get_info($hash);
        $this->assertEquals('bcrypt', $info['algoName'], 'Algoritma harus terdeteksi sebagai bcrypt.');
        $this->assertEquals(12, $info['options']['cost'], 'Cost factor bcrypt harus bernilai 12.');

        // 4. Validasi keabsahan verifikasi kata sandi asli melalui Hash::check
        $this->assertTrue(Hash::check('SamaPersis123!', $hash), 'Hash harus cocok dengan kata sandi asli via Hash::check.');
    }

    /**
     * Syarat 2 (SEC-02): Memastikan dua pengguna dengan kata sandi identik menghasilkan hash yang berbeda karena salt acak unik per record.
     */
    public function test_two_users_with_identical_passwords_produce_different_hashes(): void
    {
        // 1. Daftarkan Pengguna A (Nurul)
        $this->post('/register', [
            'name' => 'Nurul Hikmah',
            'email' => 'nurul@catatan.local',
            'password' => 'SamaPersis123!',
            'password_confirmation' => 'SamaPersis123!',
        ]);
        $this->post('/logout');

        // 2. Daftarkan Pengguna B (Budi) dengan password yang sama persis
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@catatan.local',
            'password' => 'SamaPersis123!',
            'password_confirmation' => 'SamaPersis123!',
        ]);

        $userA = User::where('email', 'nurul@catatan.local')->firstOrFail();
        $userB = User::where('email', 'budi@catatan.local')->firstOrFail();

        // 3. Verifikasi kedua hash berbeda total (salt acak unik)
        $this->assertNotEquals(
            $userA->password,
            $userB->password,
            'Dua pengguna dengan kata sandi sama persis wajib menghasilkan hash berbeda (salt acak unik bekerja).'
        );

        // 4. Verifikasi kedua hash valid terhadap password yang sama via Hash::check
        $this->assertTrue(Hash::check('SamaPersis123!', $userA->password));
        $this->assertTrue(Hash::check('SamaPersis123!', $userB->password));
    }

    /**
     * Syarat 2 (SEC-02): Memastikan kata sandi kurang dari 8 karakter ditolak oleh sistem validasi.
     */
    public function test_password_under_eight_characters_is_rejected(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Pengguna Pendek',
            'email' => 'pendek@catatan.local',
            'password' => '1234567', // 7 karakter
            'password_confirmation' => '1234567',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'pendek@catatan.local']);
    }

    /**
     * Syarat 2 (SEC-02): Memastikan kata sandi melebihi 72 byte ditolak untuk mencegah silent truncation pada bcrypt.
     */
    public function test_password_exceeding_seventy_two_bytes_is_rejected(): void
    {
        // 73 karakter huruf 'A' (73 byte UTF-8)
        $password73 = str_repeat('A', 73);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Pengguna Panjang',
            'email' => 'panjang@catatan.local',
            'password' => $password73,
            'password_confirmation' => $password73,
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'panjang@catatan.local']);
    }

    /**
     * Syarat 2 (SEC-02): Memastikan batas tepat 8 karakter dan 72 karakter diterima dengan sukses.
     */
    public function test_password_at_exact_boundaries_are_accepted(): void
    {
        // Tepat 8 karakter
        $response8 = $this->post('/register', [
            'name' => 'Batas Min',
            'email' => 'min8@catatan.local',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $response8->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'min8@catatan.local']);
        $this->post('/logout');

        // Tepat 72 karakter
        $password72 = str_repeat('B', 72);
        $response72 = $this->post('/register', [
            'name' => 'Batas Max',
            'email' => 'max72@catatan.local',
            'password' => $password72,
            'password_confirmation' => $password72,
        ]);
        $response72->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'max72@catatan.local']);
    }

    /**
     * Kepatuhan NFR-006: Memastikan password disembunyikan saat serialisasi model ke Array atau JSON.
     */
    public function test_password_is_hidden_from_model_serialization(): void
    {
        $user = User::factory()->create([
            'password' => 'RahasiaBesar123!',
        ]);

        $userArray = $user->toArray();
        $this->assertArrayNotHasKey('password', $userArray, 'Password tidak boleh muncul pada toArray() model.');

        $userJson = $user->toJson();
        $this->assertStringNotContainsString('password', $userJson, 'Password tidak boleh muncul pada toJson() model.');
    }

    /**
     * Kepatuhan NFR-006: Memastikan kata sandi tidak pernah disimpan kembali ke sesi (session flashing) saat terjadi error validasi.
     */
    public function test_password_is_never_flashed_to_session_on_validation_failure(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Flashing',
            'email' => 'email-tidak-valid',
            'password' => 'SamaPersis123!',
            'password_confirmation' => 'SamaPersis123!',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionMissing('_old_input.password');
        $response->assertSessionMissing('_old_input.password_confirmation');
    }
}
