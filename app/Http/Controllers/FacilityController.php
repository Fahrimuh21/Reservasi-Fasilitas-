<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacilityResource;
use App\Http\Resources\FacilityTypeResource;
use App\Http\Resources\LocationResource;
use App\Models\Facility;
use App\Models\FacilityType;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * FacilityController – Endpoint publik untuk modul Facility Management.
 *
 * Semua endpoint di controller ini TIDAK membutuhkan autentikasi.
 * Bisa diakses oleh Pengunjung, Pengguna, Petugas, maupun Admin.
 *
 * Sesuai PRD Anggota 2:
 * - FR-1: Daftar Fasilitas (hanya yang aktif)
 * - FR-2: Search & Filter
 * - FR-3: Ketersediaan Fasilitas (slot 30 menit, 07.00–20.00)
 * - FR-4: Detail Fasilitas
 */
class FacilityController extends Controller
{
    /**
     * FR-1 & FR-2 – Daftar fasilitas aktif dengan search, filter, dan pagination.
     *
     * GET /api/facilities?search=Lab&type_id=1&location_id=2&capacity_min=30&page=1&per_page=10
     *
     * Hanya menampilkan fasilitas dengan status 'active'.
     * Fasilitas inactive dan maintenance TIDAK muncul di endpoint publik.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Facility::with(['facilityType', 'location'])
            ->active()
            ->orderBy('name');

        // FR-2: Search berdasarkan nama fasilitas
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // FR-2: Filter berdasarkan tipe fasilitas
        if ($request->filled('type_id')) {
            $query->filterByType($request->type_id);
        }

        // FR-2: Filter berdasarkan lokasi
        if ($request->filled('location_id')) {
            $query->filterByLocation($request->location_id);
        }

        // FR-2: Filter berdasarkan kapasitas minimum
        if ($request->filled('capacity_min')) {
            $query->filterByCapacity($request->capacity_min);
        }

        $perPage = $request->integer('per_page', 10);

        return FacilityResource::collection($query->paginate($perPage));
    }

    /**
     * FR-4 – Detail fasilitas lengkap beserta ketersediaan hari ini.
     *
     * GET /api/facilities/{facility}
     *
     * Menampilkan: nama, tipe, lokasi, kapasitas, deskripsi, status,
     * dan jadwal ketersediaan hari ini (slot 30 menit).
     */
    public function show(Facility $facility): FacilityResource
    {
        $facility->load(['facilityType', 'location']);

        // Sertakan ketersediaan hari ini secara default
        $facility->availability_today = $this->generateAvailabilitySlots(
            $facility,
            Carbon::today()->toDateString()
        );

        return new FacilityResource($facility);
    }

    /**
     * FR-3 – Ketersediaan fasilitas per slot 30 menit (07.00–20.00).
     *
     * GET /api/facilities/{facility}/availability?date=2026-09-20
     *
     * Mengembalikan 26 slot dengan status 'tersedia' atau 'terisi'.
     * TIDAK menampilkan nama pemesan, tujuan, atau informasi pribadi apa pun.
     *
     * Logika overlap:
     *   slot dianggap "terisi" jika ada reservasi approved yang memenuhi:
     *   slot_start < reservation.end_at AND slot_end > reservation.start_at
     */
    public function availability(Request $request, Facility $facility): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->date;

        $slots = $this->generateAvailabilitySlots($facility, $date);

        return response()->json([
            'data' => [
                'facility_id' => $facility->id,
                'date'        => $date,
                'slots'       => $slots,
            ],
        ]);
    }

    /**
     * Dropdown – Daftar semua tipe fasilitas.
     *
     * GET /api/facility-types
     */
    public function types(): AnonymousResourceCollection
    {
        return FacilityTypeResource::collection(
            FacilityType::orderBy('name')->get()
        );
    }

    /**
     * Dropdown – Daftar semua lokasi.
     *
     * GET /api/locations
     */
    public function locations(): AnonymousResourceCollection
    {
        return LocationResource::collection(
            Location::orderBy('building')->orderBy('name')->get()
        );
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * Generate 26 slot ketersediaan (07:00–20:00, per 30 menit) untuk tanggal tertentu.
     *
     * Known Limitation: Query menggunakan DATE(start_at) = :date sehingga
     * tidak menangkap reservasi yang melewati tengah malam. Risiko kecil
     * karena jam operasional hanya 07.00–20.00.
     *
     * @return array<int, array{start: string, end: string, status: string}>
     */
    private function generateAvailabilitySlots(Facility $facility, string $date): array
    {
        // Ambil semua reservasi 'approved' pada tanggal tersebut untuk fasilitas ini
        $reservations = $facility->reservations()
            ->where('status', 'approved')
            ->whereDate('start_at', $date)
            ->get(['start_at', 'end_at']);

        $slots = [];
        $startHour = 7;  // 07:00
        $endHour   = 20; // 20:00

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            foreach ([0, 30] as $minute) {
                $slotStart = Carbon::parse("{$date} {$hour}:" . str_pad($minute, 2, '0', STR_PAD_LEFT) . ':00');
                $slotEnd   = $slotStart->copy()->addMinutes(30);

                // Cek overlap: slot_start < reservation.end_at AND slot_end > reservation.start_at
                $isBooked = $reservations->contains(function ($reservation) use ($slotStart, $slotEnd) {
                    return $slotStart->lt(Carbon::parse($reservation->end_at))
                        && $slotEnd->gt(Carbon::parse($reservation->start_at));
                });

                $slots[] = [
                    'start'  => $slotStart->format('H:i'),
                    'end'    => $slotEnd->format('H:i'),
                    'status' => $isBooked ? 'terisi' : 'tersedia',
                ];
            }
        }

        return $slots;
    }
}
