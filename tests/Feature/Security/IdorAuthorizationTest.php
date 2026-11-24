<?php

namespace Tests\Feature\Security;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private User $admin;
    private Note $noteA;
    private Note $noteB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->userA = User::where('email', 'nurul@catatan.local')->first();
        $this->userB = User::where('email', 'budi@catatan.local')->first();
        $this->admin = User::where('email', 'admin@catatan.local')->first();

        $this->noteA = $this->userA->notes()->first();
        $this->noteB = $this->userB->notes()->first();
    }

    /**
     * Syarat 10 (SEC-10): Pengguna sah (Nurul) berhasil membuka detail catatan miliknya sendiri.
     */
    public function test_user_can_view_own_note_detail(): void
    {
        $response = $this->actingAs($this->userA)->get(route('notes.show', $this->noteA));

        $response->assertStatus(200);
        $response->assertSee($this->noteA->title);
    }

    /**
     * Syarat 10 (SEC-10): Pengguna A (Nurul) DITOLAK saat mencoba mengakses detail catatan milik Pengguna B (Budi)
     * melalui manipulasi parameter ID URL (Insecure Direct Object Reference).
     * Sistem server wajib merespons dengan HTTP 403 Forbidden.
     */
    public function test_user_cannot_view_another_users_note_detail_returns_403(): void
    {
        $response = $this->actingAs($this->userA)->get(route('notes.show', $this->noteB));

        $response->assertStatus(403);
    }

    /**
     * Syarat 10 (SEC-10): Pengguna A DITOLAK saat mencoba membuka formulir edit catatan milik Pengguna B.
     */
    public function test_user_cannot_access_edit_page_of_another_users_note_returns_403(): void
    {
        $response = $this->actingAs($this->userA)->get(route('notes.edit', $this->noteB));

        $response->assertStatus(403);
    }

    /**
     * Syarat 10 (SEC-10): Pengguna A DITOLAK saat mencoba mengirim request PUT/PATCH untuk mengubah catatan milik Pengguna B.
     */
    public function test_user_cannot_update_another_users_note_returns_403(): void
    {
        $originalTitle = $this->noteB->title;

        $response = $this->actingAs($this->userA)->put(route('notes.update', $this->noteB), [
            'title' => 'Judul Dibajak Oleh User A',
            'content' => 'Konten telah disusupi secara ilegal.',
            'category' => 'Pribadi',
        ]);

        $response->assertStatus(403);

        // Memastikan isi catatan di database tidak berubah
        $this->assertDatabaseHas('notes', [
            'id' => $this->noteB->id,
            'title' => $originalTitle,
        ]);
    }

    /**
     * Syarat 10 (SEC-10): Pengguna A DITOLAK saat mencoba mengirim request DELETE untuk menghapus catatan milik Pengguna B.
     */
    public function test_user_cannot_delete_another_users_note_returns_403(): void
    {
        $response = $this->actingAs($this->userA)->delete(route('notes.destroy', $this->noteB));

        $response->assertStatus(403);

        // Memastikan catatan milik User B tetap utuh di database
        $this->assertDatabaseHas('notes', [
            'id' => $this->noteB->id,
        ]);
    }

    /**
     * Syarat 10 (SEC-10): Administrator berhak melihat catatan pengguna untuk keperluan operasional/audit.
     */
    public function test_admin_can_view_any_users_note_for_operational_purposes(): void
    {
        // Admin dapat melihat catatan milik User A
        $responseA = $this->actingAs($this->admin)->get(route('notes.show', $this->noteA));
        $responseA->assertStatus(200);

        // Admin dapat melihat catatan milik User B
        $responseB = $this->actingAs($this->admin)->get(route('notes.show', $this->noteB));
        $responseB->assertStatus(200);
    }

    /**
     * Syarat 10 (SEC-10): Tamu (guest) tanpa login yang mengakses rute catatan otomatis dialihkan (302) ke login.
     */
    public function test_guest_accessing_notes_routes_is_redirected_to_login(): void
    {
        $response1 = $this->get(route('notes.index'));
        $response1->assertRedirect(route('login'));

        $response2 = $this->get(route('notes.show', $this->noteA));
        $response2->assertRedirect(route('login'));

        $response3 = $this->get(route('notes.create'));
        $response3->assertRedirect(route('login'));
    }

    /**
     * Syarat 10 (SEC-10): Daftar catatan pada halaman index hanya memuat catatan milik pengguna yang sedang login.
     * Catatan milik pengguna lain terisolasi di sisi server (zero leakage).
     */
    public function test_notes_index_only_lists_authenticated_users_own_notes(): void
    {
        $response = $this->actingAs($this->userA)->get(route('notes.index'));

        $response->assertStatus(200);

        // Menampilkan catatan milik User A
        foreach ($this->userA->notes as $note) {
            $response->assertSee($note->title);
        }

        // Tidak boleh menampilkan catatan milik User B
        foreach ($this->userB->notes as $note) {
            $response->assertDontSee($note->title);
        }
    }

    /**
     * Syarat 10 (SEC-10): Konfigurasi cookie sesi peramban wajib mengaktifkan HttpOnly dan SameSite=Lax.
     */
    public function test_session_cookie_configuration_has_httponly_and_samesite_lax(): void
    {
        $httpOnly = config('session.http_only');
        $sameSite = config('session.same_site');

        $this->assertTrue($httpOnly, 'Cookie sesi wajib mengaktifkan flag HttpOnly untuk mitigasi pencurian sesi via XSS.');
        $this->assertEquals('lax', strtolower($sameSite), 'Cookie sesi wajib memiliki atribut SameSite=Lax untuk mitigasi CSRF.');
    }
}
