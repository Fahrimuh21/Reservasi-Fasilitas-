<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Officer\FacilityController as OfficerFacilityController;
use App\Http\Controllers\Officer\ReservationController as OfficerReservationController;

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
// Modul Facility Management (Orang 2) – Endpoint Publik (tanpa auth)
// =============================================================================

// FR-1 & FR-2 – Daftar fasilitas aktif + search + filter + pagination
// GET /api/facilities?search=Lab&type_id=1&location_id=2&capacity_min=30&page=1&per_page=10
Route::get('/facilities', [FacilityController::class, 'index']);

// FR-4 – Detail fasilitas lengkap + ketersediaan hari ini
// GET /api/facilities/{facility}
Route::get('/facilities/{facility}', [FacilityController::class, 'show']);

// FR-3 – Ketersediaan fasilitas per slot 30 menit (07.00–20.00)
// GET /api/facilities/{facility}/availability?date=2026-09-20
Route::get('/facilities/{facility}/availability', [FacilityController::class, 'availability']);

// Dropdown data untuk filter frontend
// GET /api/facility-types
Route::get('/facility-types', [FacilityController::class, 'types']);

// GET /api/locations
Route::get('/locations', [FacilityController::class, 'locations']);

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
    // =========================================================================
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // --- Modul Auth & User Management (Orang 1) ---

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

        // --- Modul Facility Management (Orang 2) – Admin CRUD (US-16) ---

        // Ringkasan dashboard admin dari data aktual
        Route::get('/facilities/summary', [AdminFacilityController::class, 'summary']);

        // List semua fasilitas (termasuk inactive & maintenance)
        // GET /api/admin/facilities?search=&type_id=&location_id=&status=inactive&page=1&per_page=10
        Route::get('/facilities', [AdminFacilityController::class, 'index']);

        // Tambah fasilitas baru
        // POST /api/admin/facilities
        Route::post('/facilities', [AdminFacilityController::class, 'store']);

        // Edit data master fasilitas (tanpa ubah status)
        // PUT /api/admin/facilities/{facility}
        Route::put('/facilities/{facility}', [AdminFacilityController::class, 'update']);

        // Toggle status active ↔ inactive
        // PATCH /api/admin/facilities/{facility}/toggle-status
        Route::patch('/facilities/{facility}/toggle-status', [AdminFacilityController::class, 'toggleStatus']);

    });

    // =========================================================================
    // Endpoint Pengguna – dilindungi middleware role:user
    // Modul User Reservation (Orang 3)
    // =========================================================================
    Route::middleware('role:user')->group(function () {
        
        // List reservasi milik pengguna
        Route::get('/reservations', [\App\Http\Controllers\ReservationController::class, 'index']);

        // Buat reservasi baru
        Route::post('/reservations', [\App\Http\Controllers\ReservationController::class, 'store']);

        // Detail reservasi
        Route::get('/reservations/{reservation}', [\App\Http\Controllers\ReservationController::class, 'show']);

        // Batalkan reservasi
        Route::post('/reservations/{reservation}/cancel', [\App\Http\Controllers\ReservationController::class, 'cancel']);

    });

    // =========================================================================
    // Endpoint Petugas – dilindungi middleware role:officer (US-12)
    // Modul Facility Management (Orang 2) – Status Maintenance
    // =========================================================================
    Route::middleware('role:officer')->prefix('officer')->group(function () {
    
        // List fasilitas (active & maintenance)
        Route::get('/facilities', [OfficerFacilityController::class, 'index']);

        // Setujui fasilitas baru: pending -> active
        Route::patch('/facilities/{facility}/approve', [OfficerFacilityController::class, 'approve']);

        // Antrean reservasi user
        Route::get('/reservations', [OfficerReservationController::class, 'index']);
        Route::patch('/reservations/{reservation}/approve', [OfficerReservationController::class, 'approve']);
        Route::patch('/reservations/{reservation}/reject', [OfficerReservationController::class, 'reject']);

        // Tandai fasilitas sebagai 'dalam perbaikan' (maintenance)
        // PATCH /api/officer/facilities/{facility}/set-maintenance
        Route::patch('/facilities/{facility}/set-maintenance', [OfficerFacilityController::class, 'setMaintenance']);

        // Kembalikan fasilitas ke 'active' setelah selesai perbaikan
        // PATCH /api/officer/facilities/{facility}/complete-maintenance
        Route::patch('/facilities/{facility}/complete-maintenance', [OfficerFacilityController::class, 'completeMaintenance']);

    });

});
