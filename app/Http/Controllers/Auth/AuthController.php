<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AuthController – Menangani registrasi, login, logout, dan endpoint /me.
 *
 * Sesuai:
 * - DESIGN.md Bagian 4 (Struktur Project: app/Http/Controllers/Auth/)
 * - PRD Bagian 8 (Desain API) – FR-1, FR-2, FR-3
 * - Arsitektur: API-only, Sanctum Bearer token
 */
class AuthController extends Controller
{
    /**
     * FR-1 – Registrasi Mandiri Pengguna
     *
     * POST /api/register
     * Body: name, email, password, password_confirmation, user_type
     *
     * Role di-set otomatis 'user' – tidak ada pilihan role di form registrasi.
     * Status awal: 'pending' – menunggu verifikasi admin.
     * Setelah sukses: tampilkan pesan "menunggu verifikasi", TIDAK langsung login.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'role'                => 'user',                  // Otomatis, tidak bisa dipilih user
            'account_status'      => 'pending',               // Wajib tunggu verifikasi admin
            'user_type'           => $request->user_type,
            'registration_source' => 'self_register',
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil. Akun Anda sedang menunggu verifikasi oleh Admin sebelum dapat digunakan untuk login.',
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'user_type'  => $user->user_type,
                'status'     => $user->account_status,
            ],
        ], 201);
    }

    /**
     * FR-2 – Login
     *
     * POST /api/login
     * Body: email, password
     *
     * Cek status akun sebelum mengeluarkan token:
     *   pending  → tolak, pesan "akun belum diverifikasi admin"
     *   rejected → tolak, pesan "registrasi ditolak"
     *   suspended → tolak, pesan "akun dinonaktifkan"
     *   active   → lanjut, keluarkan token, kembalikan data user termasuk role
     *
     * Response: { token, user: {id, name, email, role, account_status, user_type} }
     * Frontend Vue menggunakan 'role' untuk redirect ke dashboard yang tepat.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        // Cek kredensial terlebih dahulu
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password yang Anda masukkan salah.',
            ], 401);
        }

        // Cek status akun – sesuai state machine PRD Bagian 5 & DESIGN.md Bagian 5
        match ($user->account_status) {
            'pending'   => throw \Illuminate\Validation\ValidationException::withMessages([
                'account' => 'Akun Anda belum diverifikasi oleh Admin. Silakan tunggu konfirmasi.',
            ]),
            'rejected'  => throw \Illuminate\Validation\ValidationException::withMessages([
                'account' => 'Registrasi akun Anda telah ditolak. Hubungi Admin untuk informasi lebih lanjut.',
            ]),
            'suspended' => throw \Illuminate\Validation\ValidationException::withMessages([
                'account' => 'Akun Anda telah dinonaktifkan. Hubungi Admin untuk reaktivasi.',
            ]),
            default => null, // 'active' – lanjut
        };

        // Generate Sanctum Bearer token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'account_status' => $user->account_status,
                'user_type'      => $user->user_type,
            ],
        ]);
    }

    /**
     * FR-3 – Logout
     *
     * POST /api/logout  (auth:sanctum)
     *
     * Mencabut (revoke) token Sanctum yang sedang dipakai.
     * Token lama harus langsung invalid – bukan hanya hapus di localStorage frontend.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke token yang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout. Token Anda telah dicabut.',
        ]);
    }

    /**
     * GET /api/me  (auth:sanctum)
     *
     * Mengembalikan data user yang sedang login.
     * Digunakan frontend Vue untuk cek role saat reload halaman / hydrate store.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'role'           => $user->role,
            'account_status' => $user->account_status,
            'user_type'      => $user->user_type,
            'created_at'     => $user->created_at,
        ]);
    }
}
