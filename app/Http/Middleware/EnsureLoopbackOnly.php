<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoopbackOnly
{
    /**
     * Memastikan akses ke rute demonstrasi versi rentan HANYA berasal dari antarmuka loopback (127.0.0.1 / ::1).
     * Mencegah akses dari IP eksternal atau publik (Ketentuan Wajib Dosen Nomor 3 & DEC-05).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = ['127.0.0.1', '::1'];
        $clientIp = $request->ip();

        if (!in_array($clientIp, $allowedIps, true)) {
            abort(403, 'Akses Ditolak: Lingkungan demonstrasi versi rentan hanya diizinkan melalui antarmuka loopback localhost (127.0.0.1:8001).');
        }

        return $next($request);
    }
}
