<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BladeIconsAndWcagUiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@catatan.local')->first();
        $this->user = User::where('email', 'nurul@catatan.local')->first();
    }

    /**
     * Verifikasi bahwa paket blade-heroicons terpasang dan me-render inline SVG secara benar tanpa CDN.
     */
    public function test_blade_heroicons_render_inline_svg_components(): void
    {
        $renderedDocIcon = Blade::render('<x-heroicon-o-document-text class="w-6 h-6 text-emerald-600" />');
        $this->assertStringContainsString('<svg', $renderedDocIcon);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $renderedDocIcon);
        $this->assertStringContainsString('text-emerald-600', $renderedDocIcon);

        $renderedShieldIcon = Blade::render('<x-heroicon-s-shield-check class="w-5 h-5 text-emerald-600" />');
        $this->assertStringContainsString('<svg', $renderedShieldIcon);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $renderedShieldIcon);

        $renderedLockIcon = Blade::render('<x-heroicon-o-lock-closed class="w-4 h-4 text-emerald-600" />');
        $this->assertStringContainsString('<svg', $renderedLockIcon);
    }

    /**
     * Verifikasi struktur semantik layout induk (WCAG 2.1 AA):
     * Memiliki header, main, footer, meta viewport, dan navigasi berlabel.
     */
    public function test_app_layout_contains_semantic_wcag_landmarks(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('<header', false);
        $response->assertSee('<main', false);
        $response->assertSee('<footer', false);
        $response->assertSee('aria-label="Navigasi Utama"', false);
        $response->assertSee('viewport', false);
    }

    /**
     * Verifikasi bahwa seluruh form auth memiliki label eksplisit untuk screen reader (WCAG 2.1 AA).
     */
    public function test_auth_forms_have_explicit_accessible_labels(): void
    {
        // Halaman Login
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('<label for="email"', false);
        $loginResponse->assertSee('<label for="password"', false);
        $loginResponse->assertSee('focus:ring-emerald-500', false);

        // Halaman Register
        $regResponse = $this->get(route('register'));
        $regResponse->assertStatus(200);
        $regResponse->assertSee('<label for="name"', false);
        $regResponse->assertSee('<label for="email"', false);
        $regResponse->assertSee('<label for="password"', false);
        $regResponse->assertSee('<label for="password_confirmation"', false);
    }

    /**
     * Verifikasi bahwa form pembuatan catatan memiliki label eksplisit (WCAG 2.1 AA).
     */
    public function test_note_creation_form_has_explicit_accessible_labels(): void
    {
        $response = $this->actingAs($this->user)->get(route('notes.create'));

        $response->assertStatus(200);
        $response->assertSee('<label for="title"', false);
        $response->assertSee('<label for="category"', false);
        $response->assertSee('<label for="content"', false);
        $response->assertSee('<label for="public_comment"', false);
        $response->assertSee('focus:ring-emerald-500', false);
    }

    /**
     * Verifikasi kartu statistik pastel (design.md) dan komponen reusable stat-card.
     */
    public function test_dashboard_renders_hero_card_and_pastel_stat_cards(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Hero Card (#0F172A)
        $response->assertSee('bg-slate-900', false);
        $response->assertSee('Selamat Datang, Nurul Hikmah!');

        // Kartu Statistik Pastel (Mint, Peach, Sky)
        $response->assertSee('Catatan Pribadi');
        $response->assertSee('Catatan Terenkripsi');
        $response->assertSee('Catatan Diarsipkan');
    }

    /**
     * Verifikasi komponen Alert reusable pada berbagai session status/error.
     */
    public function test_alert_component_renders_accessible_icons_and_messages(): void
    {
        session()->flash('success', 'Catatan berhasil disimpan dan dienkripsi!');

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('role="alert"', false);
        $response->assertSee('Catatan berhasil disimpan dan dienkripsi!');
    }

    /**
     * Verifikasi bahwa daftar catatan menampilkan Heroicons pencarian dan tombol aksi.
     */
    public function test_notes_index_uses_heroicons_and_accessible_search(): void
    {
        $response = $this->actingAs($this->user)->get(route('notes.index'));

        $response->assertStatus(200);
        $response->assertSee('<svg', false); // SVG icon rendered
        $response->assertSee('Catatan Pribadi');
        $response->assertSee('focus:ring-emerald-500', false);
    }

    /**
     * Verifikasi Empty State dengan Heroicon saat pencarian tidak menemukan hasil.
     */
    public function test_notes_search_empty_state_renders_heroicon_and_helpful_message(): void
    {
        $response = $this->actingAs($this->user)->get(route('notes.index', [
            'q' => 'KataKunciPalsuTidakAdaSamaSekali123',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada catatan ditemukan');
        $response->assertSee('<svg', false); // Large empty state SVG
        $response->assertSee('Reset Pencarian');
    }

    /**
     * Verifikasi token warna sistem desain dan aturan fokus WCAG AA pada berkas CSS.
     */
    public function test_css_contains_wcag_compliant_tokens_and_focus_visible(): void
    {
        $cssContent = file_get_contents(resource_path('css/app.css'));

        // Token warna HEX sesuai spesifikasi design.md
        $this->assertStringContainsString('#0F172A', $cssContent); // Slate 900
        $this->assertStringContainsString('#F8FAFC', $cssContent); // Slate 50
        $this->assertStringContainsString('#ECFDF5', $cssContent); // Mint bg
        $this->assertStringContainsString('#047857', $cssContent); // Mint text
        $this->assertStringContainsString('#FFF7ED', $cssContent); // Peach bg
        $this->assertStringContainsString('#EFF6FF', $cssContent); // Sky bg

        // Focus-visible indicator untuk navigasi keyboard
        $this->assertStringContainsString(':focus-visible', $cssContent);
        $this->assertStringContainsString('#10B981', $cssContent); // Emerald focus ring
    }
}
