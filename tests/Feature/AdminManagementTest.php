<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@catatan.local')->first();
        $this->userA = User::where('email', 'nurul@catatan.local')->first();
        $this->userB = User::where('email', 'budi@catatan.local')->first();
    }

    /**
     * Uji Otorisasi: Tamu tanpa login ditolak dan dialihkan ke login saat mengakses rute admin.
     */
    public function test_guest_cannot_access_admin_routes(): void
    {
        $response1 = $this->get(route('admin.dashboard'));
        $response1->assertRedirect(route('login'));

        $response2 = $this->get(route('admin.notes.index'));
        $response2->assertRedirect(route('login'));

        $response3 = $this->get(route('admin.users.index'));
        $response3->assertRedirect(route('login'));

        $response4 = $this->delete(route('admin.users.destroy', $this->userA));
        $response4->assertRedirect(route('login'));
    }

    /**
     * Uji Otorisasi: Pengguna biasa (role === 'user') DITOLAK dengan HTTP 403 Forbidden saat mengakses area admin.
     */
    public function test_regular_user_cannot_access_admin_routes_returns_403(): void
    {
        $response1 = $this->actingAs($this->userA)->get(route('admin.dashboard'));
        $response1->assertStatus(403);

        $response2 = $this->actingAs($this->userA)->get(route('admin.notes.index'));
        $response2->assertStatus(403);

        $response3 = $this->actingAs($this->userA)->get(route('admin.users.index'));
        $response3->assertStatus(403);

        $response4 = $this->actingAs($this->userA)->delete(route('admin.users.destroy', $this->userB));
        $response4->assertStatus(403);
    }

    /**
     * Uji Fungsional: Administrator berhasil mengakses dashboard analitik admin.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Administrator');
        $response->assertSee('Total Pengguna');
        $response->assertSee('Total Catatan');
    }

    /**
     * Uji Fungsional: Administrator dapat melihat seluruh catatan dari semua pengguna.
     */
    public function test_admin_can_view_all_notes_across_all_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.notes.index'));

        $response->assertStatus(200);
        $response->assertSee('Semua Catatan Pengguna');

        // Memastikan catatan milik User A dan User B tampak di halaman admin
        $response->assertSee($this->userA->notes()->first()->title);
        $response->assertSee($this->userB->notes()->first()->title);
    }

    /**
     * Uji Fungsional: Administrator dapat mencari catatan lintas pengguna.
     */
    public function test_admin_can_search_notes_across_all_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.notes.index', [
            'q' => 'Kuliah',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Catatan Kuliah Keamanan Sistem');
    }

    /**
     * Uji Fungsional: Administrator dapat melihat daftar seluruh pengguna terdaftar.
     */
    public function test_admin_can_view_all_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna Sistem');
        $response->assertSee($this->userA->name);
        $response->assertSee($this->userB->name);
    }

    /**
     * Uji Fungsional: Administrator dapat menghapus akun pengguna biasa, dan seluruh catatannya terhapus cascade.
     */
    public function test_admin_can_delete_regular_user_and_notes_cascade(): void
    {
        $userBId = $this->userB->id;
        $noteCountBefore = Note::where('user_id', $userBId)->count();
        $this->assertGreaterThan(0, $noteCountBefore);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->userB));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Memastikan akun user terhapus
        $this->assertDatabaseMissing('users', [
            'id' => $userBId,
        ]);

        // Memastikan seluruh catatan milik user terhapus secara cascade
        $this->assertEquals(0, Note::where('user_id', $userBId)->count(), 'Seluruh catatan milik user terhapus secara cascade.');
    }

    /**
     * Aturan Bisnis 1: Admin dilarang menghapus akun dirinya sendiri saat sedang aktif login.
     */
    public function test_admin_cannot_delete_self_while_logged_in(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Memastikan akun admin tidak terhapus
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => 'admin',
        ]);
    }

    /**
     * Aturan Bisnis 2: Dilarang menghapus satu-satunya akun administrator aktif yang tersisa di sistem.
     */
    public function test_cannot_delete_last_remaining_admin_in_system(): void
    {
        // Sistem hanya memiliki 1 admin
        $this->assertEquals(1, User::where('role', 'admin')->count());

        // Buat admin kedua untuk mengeksekusi penghapusan
        $secondAdmin = User::create([
            'name' => 'Admin Kedua',
            'email' => 'admin2@catatan.local',
            'password' => 'Admin12345!',
            'role' => 'admin',
        ]);

        $this->assertEquals(2, User::where('role', 'admin')->count());

        // Admin kedua menghapus admin pertama -> BERHASIL karena masih ada admin kedua
        $res1 = $this->actingAs($secondAdmin)->delete(route('admin.users.destroy', $this->admin));
        $res1->assertSessionHas('success');
        $this->assertEquals(1, User::where('role', 'admin')->count());

        // Sekarang tersisa 1 admin (secondAdmin). Coba hapus secondAdmin oleh siapa pun -> DITOLAK
        // Simulasi jika ada admin lain atau coba hapus secondAdmin
        $res2 = $this->actingAs($secondAdmin)->delete(route('admin.users.destroy', $secondAdmin));
        // Ditolak karena aturan 1 (akun sendiri) dan aturan 2 (admin terakhir)
        $this->assertDatabaseHas('users', [
            'id' => $secondAdmin->id,
            'role' => 'admin',
        ]);
    }
}
