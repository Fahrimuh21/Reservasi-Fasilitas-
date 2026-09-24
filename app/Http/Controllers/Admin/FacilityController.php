<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Admin\FacilityController – CRUD fasilitas oleh Admin (US-16).
 *
 * Semua endpoint dilindungi middleware: auth:sanctum + role:admin (di routes/api.php).
 *
 * Sesuai PRD Anggota 2 FR-5a:
 * - List semua fasilitas (termasuk inactive & maintenance)
 * - Tambah fasilitas baru
 * - Edit data master fasilitas
 * - Toggle status active ↔ inactive
 */
class FacilityController extends Controller
{
    /**
     * Ringkasan dashboard admin berdasarkan data aktual.
     *
     * GET /api/admin/facilities/summary?period=week|month|year
     */
    public function summary(Request $request): JsonResponse
    {
        $period = $request->string('period', 'month')->toString();
        $start = match ($period) {
            'week' => now()->subDays(6)->startOfDay(),
            'year' => now()->startOfYear(),
            default => now()->subDays(29)->startOfDay(),
        };

        $reservations = Reservation::where('created_at', '>=', $start);
        $reports = DB::table('reports')->where('created_at', '>=', $start);
        $activeFacilities = Facility::active()->count();
        $reservationCount = (clone $reservations)->count();
        $approvedCount = (clone $reservations)->where('status', 'approved')->count();
        $periodDays = max(1, $start->diffInDays(now()) + 1);
        $usageRate = $activeFacilities === 0
            ? 0
            : min(100, (int) round(($approvedCount / ($activeFacilities * $periodDays)) * 100));

        $facilityData = Facility::with(['facilityType', 'location'])
            ->withCount(['reservations' => fn ($query) => $query->where('created_at', '>=', $start)])
            ->orderByDesc('reservations_count')
            ->orderBy('name')
            ->get()
            ->map(fn (Facility $facility) => [
                'id' => $facility->id,
                'name' => $facility->name,
                'type' => $facility->facilityType?->name,
                'status' => $facility->status,
                'reservations' => $facility->reservations_count,
                'usage' => $activeFacilities === 0 ? 0 : min(100, (int) round(($facility->reservations_count / $periodDays) * 100)),
            ]);

        return response()->json([
            'period' => $period,
            'kpis' => [
                'reservations' => $reservationCount,
                'usage_rate' => $usageRate,
                'reports' => $reports->count(),
                'active_users' => User::where('account_status', 'active')->count(),
            ],
            'facilities' => $facilityData,
        ]);
    }

    /**
     * List semua fasilitas (semua status) dengan search, filter, dan pagination.
     *
     * GET /api/admin/facilities?search=&type_id=&location_id=&status=inactive&page=1&per_page=10
     *
     * Berbeda dengan public endpoint, admin bisa melihat fasilitas
     * dengan status inactive dan maintenance.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Facility::with(['facilityType', 'location'])
            ->orderBy('name');

        // Search berdasarkan nama
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter berdasarkan tipe
        if ($request->filled('type_id')) {
            $query->filterByType($request->type_id);
        }

        // Filter berdasarkan lokasi
        if ($request->filled('location_id')) {
            $query->filterByLocation($request->location_id);
        }

        // Filter berdasarkan kapasitas minimum
        if ($request->filled('capacity_min')) {
            $query->filterByCapacity($request->capacity_min);
        }

        // Filter berdasarkan status (khusus admin)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 10);

        return FacilityResource::collection($query->paginate($perPage));
    }

    /**
     * Tambah fasilitas baru.
     *
     * POST /api/admin/facilities
     * Body: code, name, facility_type_id, location_id, capacity, description, status
     *
    * Fasilitas baru selalu berstatus pending sampai disetujui Petugas.
     */
    public function store(StoreFacilityRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = Facility::STATUS_PENDING;
        $facility = Facility::create($data);

        $facility->load(['facilityType', 'location']);

        return response()->json([
            'message' => "Fasilitas '{$facility->name}' berhasil ditambahkan.",
            'data'    => new FacilityResource($facility),
        ], 201);
    }

    /**
     * Edit data master fasilitas.
     *
     * PUT /api/admin/facilities/{facility}
     * Body: code, name, facility_type_id, location_id, capacity, description
     *
     * TIDAK mengubah status. Perubahan status menggunakan endpoint toggle-status.
     */
    public function update(UpdateFacilityRequest $request, Facility $facility): JsonResponse
    {
        $facility->update($request->validated());

        $facility->load(['facilityType', 'location']);

        return response()->json([
            'message' => "Fasilitas '{$facility->name}' berhasil diperbarui.",
            'data'    => new FacilityResource($facility),
        ]);
    }

    /**
     * Toggle status fasilitas: active ↔ inactive (US-16).
     *
     * PATCH /api/admin/facilities/{facility}/toggle-status
     *
     * Aturan transisi:
     * - active    → inactive  ✅
     * - inactive  → active    ✅
     * - maintenance → (ditolak 403) — admin tidak berwenang mengubah status maintenance.
     *   Petugas yang harus mengembalikan ke active terlebih dahulu.
     */
    public function toggleStatus(Facility $facility): JsonResponse
    {
        if ($facility->status === Facility::STATUS_PENDING) {
            return response()->json([
                'message' => "Fasilitas '{$facility->name}' harus disetujui Petugas terlebih dahulu.",
            ], 422);
        }

        // Admin tidak berwenang mengubah status maintenance (US-12 = domain Petugas)
        if ($facility->status === Facility::STATUS_MAINTENANCE) {
            return response()->json([
                'message' => "Fasilitas '{$facility->name}' sedang dalam status 'maintenance'. "
                           . 'Hanya Petugas yang berwenang mengembalikan status maintenance ke active.',
            ], 403);
        }

        // Toggle active ↔ inactive
        $newStatus = $facility->status === Facility::STATUS_ACTIVE
            ? Facility::STATUS_INACTIVE
            : Facility::STATUS_ACTIVE;

        $facility->update(['status' => $newStatus]);

        $label = $newStatus === Facility::STATUS_ACTIVE ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Fasilitas '{$facility->name}' berhasil {$label}.",
            'data'    => [
                'id'     => $facility->id,
                'name'   => $facility->name,
                'status' => $facility->status,
            ],
        ]);
    }
}
