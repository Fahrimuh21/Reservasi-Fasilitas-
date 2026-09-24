<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AdminUserController – Kelola akun pengguna oleh Admin.
 *
 * Sesuai:
 * - DESIGN.md Bagian 4 (Struktur: app/Http/Controllers/Admin/UserController.php)
 * - DESIGN.md Bagian 11 (Admin Routes)
 * - PRD FR-4 (Verifikasi Akun), FR-5 (Tambah Petugas), FR-6 (Tambah User), FR-7 (Toggle Status)
 * - PRD Bagian 8 (Desain API) – seluruh endpoint /api/admin/*
 *
 * Semua endpoint dilindungi middleware: auth:sanctum + role:admin (di routes/api.php)
 */
class UserController extends Controller
{
    /**
     * FR-4 – Daftar semua akun (opsional filter berdasarkan role/status).
     *
     * GET /api/admin/users?role=user&status=active
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderByDesc('created_at');

        // Filter opsional – sesuai PRD Bagian 8 kolom query
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('account_status', $request->status);
        }

        $users = $query->get([
            'id', 'name', 'email', 'role', 'account_status',
            'user_type', 'registration_source', 'verified_at', 'created_at',
        ]);

        return response()->json([
            'data'  => $users,
            'total' => $users->count(),
        ]);
    }

    /**
     * FR-4 – Daftar akun dengan status 'pending' (antrian verifikasi).
     *
     * GET /api/admin/accounts/pending
     */
    public function pending(): JsonResponse
    {
        $users = User::where('account_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'name', 'email', 'user_type', 'registration_source', 'created_at']);

        return response()->json([
            'data'  => $users,
            'total' => $users->count(),
        ]);
    }

    /**
     * FR-4 – Admin approve akun pending → status menjadi 'active'.
     *
     * POST /api/admin/accounts/{id}/approve
     */
    public function approve(Request $request, User $user): JsonResponse
    {
        if ($user->account_status !== 'pending') {
            return response()->json([
                'message' => "Akun ini tidak dalam status pending (status saat ini: {$user->account_status}).",
            ], 422);
        }

        $user->update([
            'account_status' => 'active',
            'verified_by'    => $request->user()->id,
            'verified_at'    => now(),
        ]);

        return response()->json([
            'message' => "Akun {$user->name} berhasil diverifikasi dan diaktifkan.",
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'account_status' => $user->account_status,
                'verified_at'    => $user->verified_at,
            ],
        ]);
    }

    /**
     * FR-4 – Admin reject akun pending → status menjadi 'rejected'.
     *
     * POST /api/admin/accounts/{id}/reject
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        if ($user->account_status !== 'pending') {
            return response()->json([
                'message' => "Akun ini tidak dalam status pending (status saat ini: {$user->account_status}).",
            ], 422);
        }

        $user->update([
            'account_status' => 'rejected',
            'verified_by'    => $request->user()->id,
            'verified_at'    => now(),
        ]);

        return response()->json([
            'message' => "Registrasi akun {$user->name} berhasil ditolak.",
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'account_status' => $user->account_status,
            ],
        ]);
    }

    /**
     * FR-5 & FR-6 – Admin membuat akun User atau Officer langsung (tanpa pending).
     *
     * POST /api/admin/users
     * Body: name, email, password, role (user|officer), user_type (nullable)
     *
     * Akun langsung 'active' karena admin yang menjamin validitasnya.
     * Officer TIDAK pernah self-register (US-13 SRS).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $admin = $request->user();

        $user = User::forceCreate([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'role'                => $request->role,
            'account_status'      => 'active',            // Langsung aktif – dibuat oleh admin
            'user_type'           => $request->user_type,
            'registration_source' => 'admin_created',
            'created_by'          => $admin->id,
            'verified_by'         => $admin->id,
            'verified_at'         => now(),
        ]);

        return response()->json([
            'message' => "Akun {$user->name} dengan role {$user->role} berhasil dibuat dan langsung aktif.",
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'account_status' => $user->account_status,
                'user_type'      => $user->user_type,
            ],
        ], 201);
    }

    /**
     * FR-7 – Toggle status active ↔ suspended untuk akun yang sudah pernah 'active'.
     *
     * PATCH /api/admin/users/{id}/toggle-status
     *
     * Saat dinonaktifkan: token Sanctum user tersebut langsung dicabut semua
     * agar akses tidak berlanjut meski token lama belum expired.
     * Sesuai PRD FR-7 & Test Matrix skenario "Admin nonaktifkan akun yang sedang login".
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        // Tidak boleh menonaktifkan diri sendiri
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Anda tidak dapat mengubah status akun Anda sendiri.',
            ], 422);
        }

        // Hanya akun yang pernah 'active' atau 'suspended' yang bisa di-toggle
        if (!in_array($user->account_status, ['active', 'suspended'])) {
            return response()->json([
                'message' => "Akun dengan status '{$user->account_status}' tidak dapat di-toggle. Hanya status active/suspended.",
            ], 422);
        }

        if ($user->account_status === 'active') {
            // Nonaktifkan: cabut SEMUA token Sanctum user tersebut segera
            $user->tokens()->delete();
            $user->update(['account_status' => 'suspended']);
            $message = "Akun {$user->name} berhasil dinonaktifkan (suspended). Semua sesi aktif telah dicabut.";
        } else {
            // Aktifkan kembali
            $user->update(['account_status' => 'active']);
            $message = "Akun {$user->name} berhasil diaktifkan kembali.";
        }

        return response()->json([
            'message'        => $message,
            'user'           => [
                'id'             => $user->id,
                'name'           => $user->name,
                'account_status' => $user->fresh()->account_status,
            ],
        ]);
    }
}
