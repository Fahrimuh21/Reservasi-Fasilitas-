<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware – RBAC ringan untuk 3 role tetap (user/officer/admin).
 *
 * Sesuai DESIGN.md Bagian 19 (Middleware Role) dan PRD Bagian 10 (Implementation Plan Fase 2).
 * Middleware ini dipakai bersama oleh semua modul tim lain untuk melindungi endpoint mereka.
 * Nama alias di bootstrap/app.php: 'role'
 *
 * Contoh pemakaian di route:
 *   Route::middleware(['auth:sanctum', 'role:admin'])->group(...);
 *   Route::middleware(['auth:sanctum', 'role:officer,admin'])->group(...);
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  Role yang diizinkan (bisa lebih dari satu, dipisah koma)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Pastikan user sudah terautentikasi
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Fix SEC-2: Pastikan akun tidak di-suspend
        if ($user->account_status !== 'active') {
            return response()->json([
                'message' => 'Forbidden. Akun Anda tidak aktif.',
            ], 403);
        }

        // Cek role – menggunakan kolom 'role' di tabel users sesuai DESIGN.md
        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Forbidden. Anda tidak memiliki hak akses untuk resource ini.',
                'required_roles' => $roles,
                'your_role'      => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
