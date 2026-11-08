<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak akun yang dinonaktifkan pada setiap request, bukan hanya saat
 * login, supaya sesi lama tidak tetap memegang akses ke unduhan atau
 * notifikasi.
 */
class EnsureActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403);
        }

        return $next($request);
    }
}
