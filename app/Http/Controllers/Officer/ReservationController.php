<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::with(['user:id,name,email', 'facility:id,name,code'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function approve(Request $request, Reservation $reservation): JsonResponse
    {
        $request->validate(['decision_note' => 'nullable|string|max:1000']);

        $conflict = DB::transaction(function () use ($request, $reservation) {
            // The same facility lock makes concurrent approvals deterministic.
            $facility = Facility::whereKey($reservation->facility_id)->lockForUpdate()->firstOrFail();
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->status !== 'pending') {
                throw ValidationException::withMessages(['reservation' => 'Reservasi sudah diproses atau dibatalkan.']);
            }
            if (! $facility->isActive()) {
                throw ValidationException::withMessages(['facility' => 'Fasilitas tidak aktif atau sedang dalam perbaikan.']);
            }
            if ($reservation->start_at->isPast()) {
                throw ValidationException::withMessages(['reservation' => 'Jadwal reservasi sudah lewat.']);
            }

            $hasConflict = Reservation::where('id', '!=', $reservation->id)
                ->where('facility_id', $reservation->facility_id)
                ->where('status', 'approved')
                ->where('start_at', '<', $reservation->end_at)
                ->where('end_at', '>', $reservation->start_at)
                ->exists();

            if ($hasConflict) {
                return true;
            }

            $reservation->update([
                'status' => 'approved',
                'handled_by' => $request->user()->id,
                'handled_at' => now(),
                'decision_note' => $request->decision_note,
            ]);

            return false;
        });

        if ($conflict) {
            return response()->json([
                'message' => 'Jadwal bertabrakan dengan reservasi lain yang sudah disetujui.',
            ], 422);
        }

        return response()->json([
            'message' => 'Reservasi berhasil disetujui.',
            'data' => $reservation->fresh(['user:id,name,email', 'facility:id,name,code']),
        ]);
    }

    public function reject(Request $request, Reservation $reservation): JsonResponse
    {
        $request->validate([
            'decision_note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $reservation) {
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->status !== 'pending') {
                throw ValidationException::withMessages(['reservation' => 'Reservasi sudah diproses atau dibatalkan.']);
            }
            $reservation->update([
                'status' => 'rejected',
                'handled_by' => $request->user()->id,
                'handled_at' => now(),
                'decision_note' => $request->decision_note,
            ]);
        });

        return response()->json([
            'message' => 'Reservasi berhasil ditolak.',
            'data' => $reservation->fresh(['user:id,name,email', 'facility:id,name,code']),
        ]);
    }

    public function cancel(Request $request, Reservation $reservation): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $reservation = DB::transaction(function () use ($request, $reservation) {
            $lockedReservation = Reservation::whereKey($reservation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReservation->status !== 'approved') {
                throw ValidationException::withMessages([
                    'reservation' => 'Hanya reservasi approved yang dapat dibatalkan petugas.',
                ]);
            }

            $lockedReservation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => $request->user()->id,
                'cancelled_at' => now(),
            ]);

            return $lockedReservation->fresh(['user:id,name,email', 'facility:id,name,code']);
        });

        return response()->json([
            'message' => 'Reservasi berhasil dibatalkan petugas.',
            'data' => $reservation,
        ]);
    }
}
