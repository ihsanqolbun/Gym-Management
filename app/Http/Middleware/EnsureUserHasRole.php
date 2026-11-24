<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            // BERUBAH: JSON untuk API, redirect ke login untuk halaman web
            return $request->expectsJson()
                ? response()->json([
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], 401)
                : redirect()->guest(route('login'));
        }

        if (! in_array($request->user()->role, $roles)) {
            // BERUBAH: JSON untuk API, halaman 403 untuk web
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Anda tidak memiliki akses untuk resource ini.',
                ], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}