<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Memastikan hanya pengguna dengan peran administrator yang dapat mengakses rute ini.
     * Mengembalikan HTTP 403 Forbidden jika pengguna biasa mencoba mengakses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Akses Terbatas: Hanya administrator yang berhak mengakses area ini.');
        }

        return $next($request);
    }
}
