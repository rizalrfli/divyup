<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Batasi akses ke peran tertentu. Akun nonaktif selalu ditolak,
     * termasuk sesi yang dibuat sebelum akun dinonaktifkan.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! in_array($user->role->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
