<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Status saat tambah hanya boleh 'active' atau 'inactive' (validasi di StoreFacilityRequest).
     * Status 'maintenance' hanya bisa di-set oleh Petugas via endpoint terpisah.
     */
    public function store(StoreFacilityRequest $request): JsonResponse
    {
        $facility = Facility::create($request->validated());

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
