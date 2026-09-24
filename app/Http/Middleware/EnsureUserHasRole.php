<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Cek dulu: user udah login belum?
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        // 2. User udah login, tapi apakah role-nya termasuk yang diizinkan?
        if (! in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden. Anda tidak memiliki akses untuk resource ini.',
            ], 403);
        }

        // 3. Lolos kedua pengecekan -> lanjutkan request ke controller
        return $next($request);
    }
}