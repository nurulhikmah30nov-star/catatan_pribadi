<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySecurityHeaders
{
    /**
     * Menangani permintaan HTTP dengan menyuntikkan header keamanan (SEC-09).
     * Menerapkan Content-Security-Policy (CSP) dan pertahanan berlapis terhadap XSS,
     * MIME-sniffing, dan clickjacking.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content-Security-Policy (SEC-09)
        $csp = "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none';";
        $response->headers->set('Content-Security-Policy', $csp);

        // Pertahanan tambahan XSS, clickjacking & MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
