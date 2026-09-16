<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

/*
|--------------------------------------------------------------------------
| API Routes – Modul Auth & User Management (Orang 1 - Adam)
|--------------------------------------------------------------------------
|
| Arsitektur: API-only, Sanctum Bearer token.
| Sesuai DESIGN.md Bagian 8 (Authentication Routes) & PRD Bagian 8 (Desain API).
|
| Middleware yang dipakai:
|   - auth:sanctum  → memastikan user sudah login dan token valid
|   - role:admin    → RoleMiddleware, hanya admin yang bisa akses
|
| CATATAN TIM: Seluruh endpoint di bawah inilah kontrak API yang bisa
| dipakai oleh Vue frontend. Kirimkan file ini ke teman yang pegang frontend.
|
*/

// =============================================================================
// FR-1 – Registrasi Mandiri (publik, tidak perlu auth)
// POST /api/register
// Body: name, email, password, password_confirmation, user_type
// Response: { message, user: {id, name, email, user_type, status} }
// =============================================================================
Route::post('/register', [AuthController::class, 'register']);

// =============================================================================
// FR-2 – Login (publik, tidak perlu auth)
// POST /api/login
// Body: email, password
// Response: { token, user: {id, name, email, role, account_status, user_type} }
// Frontend Vue: redirect berdasarkan 'role' yang dikembalikan
// =============================================================================
Route::post('/login', [AuthController::class, 'login']);

// =============================================================================
// Endpoint yang butuh autentikasi (token Sanctum valid)
// =============================================================================
Route::middleware('auth:sanctum')->group(function () {

    // FR-3 – Logout: revoke token aktif
    // POST /api/logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // /me – Ambil data user yang sedang login (untuk hydrate Vue store saat reload)
    // GET /api/me
    Route::get('/me', [AuthController::class, 'me']);

    // =========================================================================
    // Endpoint Admin – dilindungi middleware role:admin
    // Sesuai DESIGN.md Bagian 11 (Admin Routes) & PRD Bagian 8 (Desain API)
    // =========================================================================
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // FR-4 – Daftar semua akun (filter opsional: role, status)
        // GET /api/admin/users?role=user&status=active
        Route::get('/users', [AdminUserController::class, 'index']);

        // FR-4 – Daftar akun dengan status 'pending'
        // GET /api/admin/accounts/pending
        Route::get('/accounts/pending', [AdminUserController::class, 'pending']);

        // FR-4 – Approve akun pending → active
        // POST /api/admin/accounts/{user}/approve
        Route::post('/accounts/{user}/approve', [AdminUserController::class, 'approve']);

        // FR-4 – Reject akun pending → rejected
        // POST /api/admin/accounts/{user}/reject
        Route::post('/accounts/{user}/reject', [AdminUserController::class, 'reject']);

        // FR-5 & FR-6 – Buat akun User atau Officer langsung (langsung active, skip pending)
        // POST /api/admin/users
        // Body: name, email, password, role (user|officer), user_type
        Route::post('/users', [AdminUserController::class, 'store']);

        // FR-7 – Toggle status active ↔ suspended + revoke token jika dinonaktifkan
        // PATCH /api/admin/users/{user}/toggle-status
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);

    });

});
