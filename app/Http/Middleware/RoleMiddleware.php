<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login, dan apakah rolenya ada di dalam daftar yang diizinkan
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            
            // Jika tidak punya akses, lempar error 403 (Forbidden)
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
