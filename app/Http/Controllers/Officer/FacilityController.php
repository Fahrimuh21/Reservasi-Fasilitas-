<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;

/**
 * Officer\FacilityController – Kelola status maintenance fasilitas oleh Petugas (US-12).
 *
 * Semua endpoint dilindungi middleware: auth:sanctum + role:officer (di routes/api.php).
 *
 * Sesuai PRD Anggota 2 FR-5b:
 * - Set fasilitas ke status 'maintenance' (dalam perbaikan)
 * - Kembalikan fasilitas ke status 'active' (selesai perbaikan)
 *
 * Petugas TIDAK berwenang mengubah status 'inactive' (domain Admin, US-16).
 */
class FacilityController extends Controller
{
    /**
     * Tandai fasilitas sebagai 'dalam perbaikan' (US-12).
     *
     * PATCH /api/officer/facilities/{facility}/set-maintenance
     *
     * Aturan transisi:
     * - active      → maintenance  ✅
     * - maintenance → (ditolak) sudah dalam perbaikan
     * - inactive    → (ditolak 403) fasilitas dinonaktifkan admin, tidak bisa langsung diperbaiki
     */
    public function setMaintenance(Facility $facility): JsonResponse
    {
        if ($facility->status === Facility::STATUS_MAINTENANCE) {
            return response()->json([
                'message' => "Fasilitas '{$facility->name}' sudah berstatus 'maintenance'.",
            ], 422);
        }

        if ($facility->status === Facility::STATUS_INACTIVE) {
            return response()->json([
                'message' => "Fasilitas '{$facility->name}' sedang dalam status 'inactive' (dinonaktifkan oleh Admin). "
                           . 'Tidak dapat diubah ke maintenance tanpa diaktifkan terlebih dahulu oleh Admin.',
            ], 403);
        }

        $facility->update(['status' => Facility::STATUS_MAINTENANCE]);

        return response()->json([
            'message' => "Fasilitas '{$facility->name}' berhasil ditandai sebagai 'dalam perbaikan' (maintenance).",
            'data'    => [
                'id'     => $facility->id,
                'name'   => $facility->name,
                'status' => $facility->status,
            ],
        ]);
    }

    /**
     * Kembalikan fasilitas ke status 'active' setelah selesai perbaikan (US-12).
     *
     * PATCH /api/officer/facilities/{facility}/complete-maintenance
     *
     * Aturan transisi:
     * - maintenance → active  ✅
     * - active      → (ditolak) tidak sedang dalam perbaikan
     * - inactive    → (ditolak) tidak sedang dalam perbaikan
     */
    public function completeMaintenance(Facility $facility): JsonResponse
    {
        if ($facility->status !== Facility::STATUS_MAINTENANCE) {
            return response()->json([
                'message' => "Fasilitas '{$facility->name}' tidak sedang dalam status 'maintenance' "
                           . "(status saat ini: {$facility->status}).",
            ], 422);
        }

        $facility->update(['status' => Facility::STATUS_ACTIVE]);

        return response()->json([
            'message' => "Perbaikan fasilitas '{$facility->name}' selesai. Status dikembalikan ke 'active'.",
            'data'    => [
                'id'     => $facility->id,
                'name'   => $facility->name,
                'status' => $facility->status,
            ],
        ]);
    }
}
