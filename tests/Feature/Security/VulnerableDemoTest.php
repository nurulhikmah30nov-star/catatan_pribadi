<?php

namespace Tests\Feature\Security;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes Keamanan: Pembuktian Lingkungan Demo Versi Rentan Terisolasi
 * Memvalidasi Syarat 8 (SQLi), Syarat 9 (IDOR), Syarat 10 (XSS)
 * serta Isolasi Loopback (DEC-05 / Aturan Dosen No. 3).
 */
class VulnerableDemoTest extends TestCase
{
    use RefreshDatabase;

    private User $userNurul;
    private User $userBudi;
    private Note $noteNurul;
    private Note $noteBudi;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Pengguna A (Nurul)
        $this->userNurul = User::create([
            'name'     => 'Nurul Hikmah',
            'email'    => 'nurul@catatan.local',
            'password' => 'SamaPersis123!',
            'role'     => 'user',
        ]);

        // 2. Pengguna B (Budi)
        $this->userBudi = User::create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@catatan.local',
            'password' => 'SamaPersis123!',
            'role'     => 'user',
        ]);

        // 3. Catatan Nurul
        $this->noteNurul = Note::create([
            'user_id'        => $this->userNurul->id,
            'title'          => 'Rencana Skripsi Nurul',
            'content'        => 'Topik penelitian tentang kriptografi modern AES-GCM.',
            'public_comment' => 'Catatan ini dibuat untuk diskusi tugas akhir.',
            'is_archived'    => false,
        ]);

        // 4. Catatan Budi (Rahasia)
        $this->noteBudi = Note::create([
            'user_id'        => $this->userBudi->id,
            'title'          => 'Rahasia Bisnis Budi',
            'content'        => 'Data finansial dan strategi startup rahasia Budi.',
            'public_comment' => 'Komentar awal catatan rahasia Budi.',
            'is_archived'    => false,
        ]);
    }

    /**
     * Uji Isolasi Loopback: Akses dari IP eksternal selain 127.0.0.1 / ::1 wajib DITOLAK (HTTP 403).
     */
    public function test_external_ip_is_blocked_by_loopback_only_middleware(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->get('/vuln');

        $response->assertStatus(403);
    }

    /**
     * Uji Isolasi Loopback: Akses dari antarmuka loopback 127.0.0.1 diizinkan (HTTP 200).
     */
    public function test_loopback_ip_can_access_vulnerable_dashboard(): void
    {
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/vuln');

        $response->assertStatus(200);
        $response->assertSee('LINGKUNGAN DEMONSTRASI VERSI RENTAN');
        $response->assertSee('Rencana Skripsi Nurul');
        $response->assertSee('Rahasia Bisnis Budi');
    }

    /**
     * Syarat 8 (SEC-08): Pembuktian SQL Injection Tautologi (' OR '1'='1' -- ).
     * Membuktikan query mentah membocorkan semua data catatan tanpa filter yang sah.
     */
    public function test_vulnerable_search_is_susceptible_to_tautology_sql_injection(): void
    {
        $payload = "' OR '1'='1' -- ";

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/vuln/notes/search?q=' . urlencode($payload));

        $response->assertStatus(200);
        // Kedua catatan (Nurul dan Budi) wajib muncul karena klausa WHERE dipaksa TRUE
        $response->assertSee('Rencana Skripsi Nurul');
        $response->assertSee('Rahasia Bisnis Budi');
        $response->assertSee('SELECT * FROM notes WHERE title LIKE', false);
        $response->assertSee("OR '1'='1'", false);
    }

    /**
     * Syarat 8 (SEC-08): Pembuktian SQL Injection UNION-based.
     * Membuktikan penyerang dapat mengekstrak data dari tabel users melalui celah SQLi notes.
     */
    public function test_vulnerable_search_is_susceptible_to_union_sql_injection(): void
    {
        // Menyuntikkan kueri UNION (9 kolom sesuai skema tabel notes) untuk mengekstrak email dan nama user dari tabel users
        $payload = "' UNION SELECT id, id, name, email, 'User', password, 0, created_at, updated_at FROM users -- ";

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/vuln/notes/search?q=' . urlencode($payload));

        $response->assertStatus(200);
        // Memastikan data tabel users terekstraksi ke dalam hasil
        $response->assertSee('nurul@catatan.local');
        $response->assertSee('budi@catatan.local');
    }

    /**
     * Syarat 9 (SEC-09): Pembuktian Celah IDOR (Insecure Direct Object Reference).
     * Membuktikan User A (Nurul) dapat membaca catatan rahasia milik User B (Budi) via ID (HTTP 200 bukan 403).
     */
    public function test_vulnerable_detail_allows_idor_access_across_different_users(): void
    {
        // Login sebagai Nurul
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->actingAs($this->userNurul)
            ->get('/vuln/notes/' . $this->noteBudi->id);

        // Pada versi rentan, TIDAK ADA otorisasi (403), melainkan HTTP 200 OK
        $response->assertStatus(200);
        $response->assertSee('Rahasia Bisnis Budi');
        $response->assertSee('Data finansial dan strategi startup rahasia Budi');
        $response->assertSee('BUKTI IDOR BERHASIL TERJADI');
    }

    /**
     * Syarat 10 (SEC-10): Pembuktian Celah Stored XSS via Unescaped Blade Render.
     * Membuktikan script tag HTML/JS disuntikkan dan dirender mentah {!! ... !!}.
     */
    public function test_vulnerable_view_renders_stored_xss_payload_unescaped(): void
    {
        $xssPayload = "<script>alert('VULNERABLE-XSS-EXECUTION')</script>";

        // 1. Simpan komentar berisi payload XSS berbahaya
        $updateResponse = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/vuln/notes/' . $this->noteNurul->id . '/comment', [
                'public_comment' => $xssPayload,
            ]);

        $updateResponse->assertRedirect('/vuln/notes/' . $this->noteNurul->id);

        // 2. Akses halaman catatan dan pastikan script tag dirender UNESCAPED secara literal
        $showResponse = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/vuln/notes/' . $this->noteNurul->id);

        $showResponse->assertStatus(200);
        // Menggunakan assertSee dengan false agar script tag tidak di-HTML-entities
        $showResponse->assertSee($xssPayload, false);
    }
}
