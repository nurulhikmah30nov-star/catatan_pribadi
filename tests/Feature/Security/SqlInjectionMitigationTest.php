<?php

namespace Tests\Feature\Security;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SqlInjectionMitigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->user = User::where('email', 'nurul@catatan.local')->first();
        $this->otherUser = User::where('email', 'budi@catatan.local')->first();
        $this->admin = User::where('email', 'admin@catatan.local')->first();
    }

    /**
     * Uji Fungsional CRUD: Pengguna dapat membuat catatan baru.
     */
    public function test_user_can_create_new_note(): void
    {
        $response = $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'Catatan Baru Uji CRUD',
            'content' => 'Isi catatan uji yang akan dienkripsi dengan AES-256-GCM.',
            'category' => 'Pribadi',
            'public_comment' => 'Komentar catatan uji.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'title' => 'Catatan Baru Uji CRUD',
            'category' => 'Pribadi',
        ]);
    }

    /**
     * Uji Fungsional CRUD: Pengguna dapat membaca detail catatan miliknya.
     */
    public function test_user_can_view_own_note(): void
    {
        $note = $this->user->notes()->first();

        $response = $this->actingAs($this->user)->get(route('notes.show', $note));

        $response->assertStatus(200);
        $response->assertSee($note->title);
    }

    /**
     * Uji Fungsional CRUD: Pengguna dapat memperbarui catatan miliknya.
     */
    public function test_user_can_update_own_note(): void
    {
        $note = $this->user->notes()->first();

        $response = $this->actingAs($this->user)->put(route('notes.update', $note), [
            'title' => 'Judul Catatan Telah Diperbarui',
            'content' => 'Isi catatan baru yang telah direvisi.',
            'category' => 'Kuliah',
        ]);

        $response->assertRedirect(route('notes.show', $note));
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Judul Catatan Telah Diperbarui',
            'category' => 'Kuliah',
        ]);
    }

    /**
     * Uji Fungsional CRUD: Pengguna dapat menghapus catatan miliknya.
     */
    public function test_user_can_delete_own_note(): void
    {
        $note = $this->user->notes()->first();

        $response = $this->actingAs($this->user)->delete(route('notes.destroy', $note));

        $response->assertRedirect(route('notes.index'));
        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    /**
     * Syarat 8 (SEC-08): Memastikan payload klasik ' OR '1'='1' -- diperlakukan sebagai string literal.
     * Tidak membypass klausul WHERE dan tidak menampilkan seluruh data catatan tanpa filter.
     */
    public function test_classic_or_one_equals_one_sqli_payload_returns_zero_results(): void
    {
        $payload = "' OR '1'='1' -- ";

        $response = $this->actingAs($this->user)->get(route('notes.search', [
            'q' => $payload,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada catatan ditemukan');
        // Tidak membocorkan catatan milik Budi
        $response->assertDontSee('Agenda Kerja Mingguan');
        $response->assertDontSee('Ide Proyek Sampingan');
    }

    /**
     * Syarat 8 (SEC-08): Memastikan payload variasi tautologi SQLi ' OR ''=' diperlakukan sebagai teks biasa.
     */
    public function test_tautology_single_quotes_sqli_payload_returns_zero_results(): void
    {
        $payload = "' OR ''='";

        $response = $this->actingAs($this->user)->get(route('notes.search', [
            'q' => $payload,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada catatan ditemukan');
    }

    /**
     * Syarat 8 (SEC-08): Memastikan payload UNION-based SQLi tidak membocorkan data pengguna atau password hash.
     */
    public function test_union_based_sqli_payload_does_not_leak_user_credentials(): void
    {
        $payload = "' UNION SELECT id, name, email, password, role, null, null, null FROM users -- ";

        $response = $this->actingAs($this->user)->get(route('notes.search', [
            'q' => $payload,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada catatan ditemukan');

        // Pastikan tidak ada data sensitif akun yang bocor ke output HTML
        $response->assertDontSee('admin@catatan.local');
        $response->assertDontSee('budi@catatan.local');
        $response->assertDontSee('$2y$12$');
    }

    /**
     * Syarat 8 (SEC-08): Percobaan injeksi stacked query / DROP TABLE pada parameter pencarian.
     */
    public function test_stacked_query_and_drop_table_sqli_attempt_does_not_damage_database(): void
    {
        $initialNoteCount = Note::count();
        $this->assertGreaterThan(0, $initialNoteCount);

        // Percobaan injeksi stacked query / DROP TABLE melalui parameter q
        $response = $this->actingAs($this->user)->get(route('notes.index', [
            'q' => "Catatan; DROP TABLE notes; --",
        ]));

        $response->assertStatus(200);

        // Memastikan tabel 'notes' tetap ada dan seluruh data utuh
        $this->assertEquals($initialNoteCount, Note::count(), 'Tabel notes dan seluruh datanya wajib tetap utuh tanpa terpengaruh upaya injeksi.');
    }

    /**
     * Syarat 8 (SEC-08): Memastikan karakter khusus SQL LIKE seperti '%' dan '_' diperlakukan secara aman.
     */
    public function test_sql_wildcard_characters_do_not_cause_query_exceptions(): void
    {
        $response1 = $this->actingAs($this->user)->get(route('notes.search', ['q' => '%']));
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->user)->get(route('notes.search', ['q' => '_']));
        $response2->assertStatus(200);

        $response3 = $this->actingAs($this->user)->get(route('notes.search', ['q' => "'; --"]));
        $response3->assertStatus(200);
    }

    /**
     * Syarat 8 (SEC-08): Pencarian dengan kata kunci valid menemukan catatan yang sesuai.
     */
    public function test_legitimate_search_finds_matching_notes(): void
    {
        $response = $this->actingAs($this->user)->get(route('notes.search', [
            'q' => 'Kuliah',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Catatan Kuliah Keamanan Sistem');
    }
}
