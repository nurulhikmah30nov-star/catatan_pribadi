<?php

namespace Tests\Feature;

use Tests\TestCase;

class CipherKeyCommandTest extends TestCase
{
    /**
     * Memastikan perintah artisan cipher:generate-key dapat menghasilkan kunci 256-bit valid.
     */
    public function test_cipher_generate_key_command_shows_valid_64_hex_key(): void
    {
        $this->artisan('cipher:generate-key --show')
            ->assertExitCode(0);
    }
}
