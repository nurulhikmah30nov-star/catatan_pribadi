<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSchemaAndSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memverifikasi keberadaan 3 akun uji fiktif resmi CatatanSecure (1 Admin, 2 Pengguna Biasa).
     */
    public function test_seeder_populates_required_official_accounts(): void
    {
        $admin = User::where('email', 'admin@catatan.local')->first();
        $userA = User::where('email', 'nurul@catatan.local')->first();
        $userB = User::where('email', 'budi@catatan.local')->first();

        $this->assertNotNull($admin);
        $this->assertNotNull($userA);
        $this->assertNotNull($userB);

        $this->assertEquals('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertEquals('user', $userA->role);
        $this->assertFalse($userA->isAdmin());
        $this->assertEquals('user', $userB->role);
        $this->assertFalse($userB->isAdmin());

        // Verifikasi password valid
        $this->assertTrue(Hash::check('AdminRahasia123!', $admin->password));
        $this->assertTrue(Hash::check('SamaPersis123!', $userA->password));
        $this->assertTrue(Hash::check('SamaPersis123!', $userB->password));
    }

    /**
     * Syarat 2 (SEC-02): Dua user dengan password sama persis menghasilkan hash bcrypt yang berbeda.
     */
    public function test_identical_passwords_produce_different_bcrypt_hashes(): void
    {
        $userA = User::where('email', 'nurul@catatan.local')->first();
        $userB = User::where('email', 'budi@catatan.local')->first();

        $this->assertNotEquals(
            $userA->password,
            $userB->password,
            'Dua pengguna dengan kata sandi identik harus menghasilkan hash bcrypt berbeda karena salt otomatis.'
        );

        $this->assertEquals(60, strlen($userA->password));
        $this->assertEquals(60, strlen($userB->password));
    }

    /**
     * Memverifikasi bahwa seeder menghasilkan catatan awal yang terenkripsi untuk User A dan User B.
     */
    public function test_notes_seeder_populates_encrypted_notes_for_users(): void
    {
        $userA = User::where('email', 'nurul@catatan.local')->first();
        $userB = User::where('email', 'budi@catatan.local')->first();

        $this->assertGreaterThanOrEqual(3, $userA->notes()->count());
        $this->assertGreaterThanOrEqual(3, $userB->notes()->count());

        // Pastikan catatan di SQLite berbentuk ciphertext Base64
        $rawNote = DB::table('notes')->where('user_id', $userA->id)->first();
        $this->assertNotNull($rawNote);
        $binary = base64_decode($rawNote->content, true);
        $this->assertNotFalse($binary);
        $this->assertEquals(1, ord($binary[0]));
    }

    /**
     * Memverifikasi foreign key cascade: menghapus pengguna menghapus seluruh catatannya.
     */
    public function test_deleting_user_cascades_to_all_user_notes(): void
    {
        $userB = User::where('email', 'budi@catatan.local')->first();
        $budiId = $userB->id;

        $notesCount = Note::where('user_id', $budiId)->count();
        $this->assertGreaterThan(0, $notesCount);

        $userB->delete();

        $this->assertEquals(0, Note::where('user_id', $budiId)->count(), 'Seluruh catatan harus terhapus cascade saat user dihapus.');
    }

    /**
     * Memverifikasi batasan foreign key SQLite mencegah pembuatan catatan tanpa user sah.
     */
    public function test_foreign_key_constraint_prevents_orphan_note(): void
    {
        $this->expectException(QueryException::class);

        DB::table('notes')->insert([
            'user_id' => 999999, // User ID tidak ada
            'title' => 'Catatan Yatim Piatu',
            'content' => 'Data tidak sah',
            'category' => 'Pribadi',
            'is_archived' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
