<?php

namespace Tests\Feature\Security;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NoteEncryptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->user = User::where('email', 'nurul@catatan.local')->first();
        $this->otherUser = User::where('email', 'budi@catatan.local')->first();
    }

    /**
     * Syarat 3 (SEC-03): Kolom content pada SQLite tersimpan dalam format ciphertext biner Base64 envelope.
     * Plaintext tidak pernah tersimpan di database fisik.
     */
    public function test_note_content_is_stored_as_ciphertext_envelope_in_sqlite(): void
    {
        $plaintext = 'Ini adalah rahasia pribadi yang sangat penting!';

        $note = $this->user->notes()->create([
            'title' => 'Catatan Rahasia Tes',
            'content' => $plaintext,
            'category' => 'Rahasia',
        ]);

        // Periksa data mentah langsung dari kueri DB fisik SQLite (bypass Eloquent accessor)
        $rawRow = DB::table('notes')->where('id', $note->id)->first();

        $this->assertNotNull($rawRow);
        // Pastikan bukan plaintext
        $this->assertStringNotContainsString($plaintext, $rawRow->content);

        // Pastikan format Base64 envelope valid (versi 0x01, panjang minimal 29 byte)
        $binary = base64_decode($rawRow->content, true);
        $this->assertNotFalse($binary, 'Kolom content harus berupa Base64 valid.');
        $this->assertGreaterThanOrEqual(29, strlen($binary), 'Envelope biner harus berukuran minimal 29 byte.');
        $this->assertEquals(1, ord($binary[0]), 'Versi envelope biner pertama wajib bernilai 0x01.');
    }

    /**
     * Syarat 3 (SEC-03): Membuka halaman detail catatan oleh pemiliknya menampilkan isi terdekripsi utuh.
     */
    public function test_note_owner_can_view_decrypted_content_on_show_page(): void
    {
        $plaintext = 'Materi kuliah keamanan kriptografi lanjutan: AES-GCM 256-bit.';

        $note = $this->user->notes()->create([
            'title' => 'Catatan Kriptografi',
            'content' => $plaintext,
            'category' => 'Kuliah',
        ]);

        $response = $this->actingAs($this->user)->get(route('notes.show', $note));

        $response->assertStatus(200);
        $response->assertSee($plaintext);
        // Memastikan ciphertext mentah juga ditampilkan di kotak inspeksi bukti empiris
        $response->assertSee($note->raw_content);
    }

    /**
     * Syarat 3 (SEC-03): Menyimpan dua catatan dengan isi persis sama menghasilkan ciphertext berbeda (nonce acak unik).
     */
    public function test_identical_plaintexts_produce_different_ciphertexts_due_to_random_nonce(): void
    {
        $plaintext = 'Isi teks yang sama persis untuk dua catatan berbeda.';

        $note1 = $this->user->notes()->create([
            'title' => 'Catatan A',
            'content' => $plaintext,
            'category' => 'Pribadi',
        ]);

        $note2 = $this->user->notes()->create([
            'title' => 'Catatan B',
            'content' => $plaintext,
            'category' => 'Pribadi',
        ]);

        $raw1 = DB::table('notes')->where('id', $note1->id)->value('content');
        $raw2 = DB::table('notes')->where('id', $note2->id)->value('content');

        $this->assertNotEquals($raw1, $raw2, 'Dua catatan dengan teks sama wajib memiliki ciphertext berbeda karena nonce acak unik.');

        // Ekstrak nonce dari masing-masing envelope
        $nonce1 = substr(base64_decode($raw1), 1, 12);
        $nonce2 = substr(base64_decode($raw2), 1, 12);

        $this->assertEquals(12, strlen($nonce1));
        $this->assertEquals(12, strlen($nonce2));
        $this->assertNotEquals($nonce1, $nonce2, 'Nonce 12-byte acak harus berbeda untuk tiap operasi enkripsi.');
    }

    /**
     * Syarat 3 (SEC-03): Manipulasi bit ciphertext di database ditangani secara aman tanpa memicu crash/exception fatal.
     */
    public function test_tampered_ciphertext_handled_gracefully_without_fatal_crash(): void
    {
        $note = $this->user->notes()->create([
            'title' => 'Catatan Uji Integritas',
            'content' => 'Data sensitif sebelum dimanipulasi.',
            'category' => 'Pribadi',
        ]);

        $raw = DB::table('notes')->where('id', $note->id)->value('content');

        // Manipulasi (flip 1 karakter Base64 di posisi akhir ciphertext)
        $tampered = substr($raw, 0, -2) . ($raw[strlen($raw) - 2] === 'A' ? 'B' : 'A') . substr($raw, -1);
        DB::table('notes')->where('id', $note->id)->update(['content' => $tampered]);

        // Muat ulang model yang datanya dimanipulasi
        $freshNote = Note::find($note->id);

        // Mengakses accessor content tidak boleh melempar Exception fatal
        $content = $freshNote->content;
        $this->assertStringContainsString('Peringatan: Integritas Data Rusak', $content);

        // Halaman web tetap dapat dirender tanpa HTTP 500 error
        $response = $this->actingAs($this->user)->get(route('notes.show', $freshNote));
        $response->assertStatus(200);
        $response->assertSee('Peringatan: Integritas Data Rusak');
    }

    /**
     * Syarat 3 (SEC-03): Memperbarui isi catatan mengenkripsi ulang dengan nonce baru.
     */
    public function test_updating_note_content_re_encrypts_with_new_nonce(): void
    {
        $note = $this->user->notes()->create([
            'title' => 'Draf Awal',
            'content' => 'Teks awal sebelum revisi.',
            'category' => 'Pekerjaan',
        ]);

        $initialCiphertext = DB::table('notes')->where('id', $note->id)->value('content');

        // Update catatan
        $this->actingAs($this->user)->put(route('notes.update', $note), [
            'title' => 'Draf Direvisi',
            'content' => 'Teks baru yang sudah direvisi dan disimpan ulang.',
            'category' => 'Pekerjaan',
        ]);

        $updatedCiphertext = DB::table('notes')->where('id', $note->id)->value('content');

        $this->assertNotEquals($initialCiphertext, $updatedCiphertext);

        // Memastikan isi baru terdekripsi dengan benar
        $fresh = Note::find($note->id);
        $this->assertEquals('Teks baru yang sudah direvisi dan disimpan ulang.', $fresh->content);
    }
}
