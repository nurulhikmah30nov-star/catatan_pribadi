<?php

namespace Tests\Feature\Security;

use App\Models\Note;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XssEscapingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->user = User::where('email', 'nurul@catatan.local')->first();
    }

    /**
     * Syarat 9 (SEC-09): Tag <script> di-escape secara kontekstual oleh Blade menjadi entitas teks aman (&lt;script&gt;).
     * Membuktikan data asli tersimpan di DB (fidelity) namun dirender secara aman di browser tanpa eksekusi alert.
     */
    public function test_stored_xss_script_payload_is_escaped_in_blade(): void
    {
        $payload = "<script>alert('XSS-Catatan-230170187')</script>";

        $response = $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'Catatan Uji XSS Script',
            'content' => 'Konten catatan aman.',
            'category' => 'Pribadi',
            'public_comment' => $payload,
        ]);

        $response->assertRedirect();

        // 1. Verifikasi integritas data asli (fidelity): di DB tersimpan string mentah tanpa modifikasi
        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'title' => 'Catatan Uji XSS Script',
            'public_comment' => $payload,
        ]);

        $note = Note::where('title', 'Catatan Uji XSS Script')->first();

        // 2. Verifikasi mitigasi XSS pada rendering Blade
        $detailResponse = $this->actingAs($this->user)->get(route('notes.show', $note));
        $detailResponse->assertStatus(200);

        // Raw tag script TIDAK BOLEH muncul tanpa escape
        $detailResponse->assertDontSee($payload, false);

        // Blade wajib meng-escape menjadi entitas karakter HTML yang aman (&lt;script&gt;...)
        $detailResponse->assertSee(htmlspecialchars($payload, ENT_QUOTES, 'UTF-8'), false);
    }

    /**
     * Syarat 9 (SEC-09): Tag <img> onerror di-escape secara kontekstual oleh Blade.
     */
    public function test_stored_xss_img_onerror_payload_is_escaped_in_blade(): void
    {
        $payload = '<img src=x onerror="alert(document.cookie)">';

        $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'Catatan Uji XSS Img',
            'content' => 'Konten catatan aman.',
            'category' => 'Kuliah',
            'public_comment' => $payload,
        ]);

        $note = Note::where('title', 'Catatan Uji XSS Img')->first();

        $detailResponse = $this->actingAs($this->user)->get(route('notes.show', $note));
        $detailResponse->assertStatus(200);

        // Raw tag img unescaped TIDAK BOLEH dirender
        $detailResponse->assertDontSee($payload, false);

        // Harus ter-escape sebagai entitas HTML aman
        $detailResponse->assertSee(htmlspecialchars($payload, ENT_QUOTES, 'UTF-8'), false);
    }

    /**
     * Syarat 9 (SEC-09): Tag <a> javascript: pseudo-protocol di-escape menjadi teks aman.
     */
    public function test_stored_xss_anchor_javascript_payload_is_escaped_in_blade(): void
    {
        $payload = '<a href="javascript:alert(1)">Klik untuk klaim hadiah</a>';

        $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'Catatan Uji XSS Anchor',
            'content' => 'Konten catatan aman.',
            'category' => 'Pribadi',
            'public_comment' => $payload,
        ]);

        $note = Note::where('title', 'Catatan Uji XSS Anchor')->first();

        $detailResponse = $this->actingAs($this->user)->get(route('notes.show', $note));
        $detailResponse->assertStatus(200);

        // Raw unescaped anchor TIDAK BOLEH dirender
        $detailResponse->assertDontSee($payload, false);

        // Harus ter-escape sebagai entitas HTML aman
        $detailResponse->assertSee(htmlspecialchars($payload, ENT_QUOTES, 'UTF-8'), false);
    }

    /**
     * Syarat 9 (SEC-09): Tag <svg onload=...> di-escape secara kontekstual oleh Blade.
     */
    public function test_stored_xss_svg_onload_payload_is_escaped_in_blade(): void
    {
        $payload = '<svg onload="alert(\'XSS-SVG\')">';

        $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'Catatan Uji XSS SVG',
            'content' => 'Konten catatan aman.',
            'category' => 'Pribadi',
            'public_comment' => $payload,
        ]);

        $note = Note::where('title', 'Catatan Uji XSS SVG')->first();

        $detailResponse = $this->actingAs($this->user)->get(route('notes.show', $note));
        $detailResponse->assertStatus(200);

        $detailResponse->assertDontSee($payload, false);
        $detailResponse->assertSee(htmlspecialchars($payload, ENT_QUOTES, 'UTF-8'), false);
    }

    /**
     * Syarat 9 (SEC-09): Memastikan karakter khusus HTML (<, >, ", ', &) tersimpan secara utuh di SQLite (data fidelity).
     */
    public function test_html_special_characters_stored_with_full_data_fidelity(): void
    {
        $specialText = "Karakter khusus: <tag>, 'single-quote', \"double-quote\", &ampersand.";

        $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'Catatan Fidelity',
            'content' => 'Konten aman.',
            'category' => 'Pribadi',
            'public_comment' => $specialText,
        ]);

        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'title' => 'Catatan Fidelity',
            'public_comment' => $specialText,
        ]);
    }

    /**
     * Syarat 9 (SEC-09): Respons HTTP menyertakan header Content-Security-Policy (CSP) dan header pengerasan keamanan.
     */
    public function test_security_headers_middleware_is_applied_to_responses(): void
    {
        $response = $this->actingAs($this->user)->get(route('notes.index'));

        $response->assertStatus(200);

        // Header Content-Security-Policy (CSP)
        $response->assertHeader('Content-Security-Policy');
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);

        // Header Anti-MIME-sniffing & Anti-Clickjacking
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
