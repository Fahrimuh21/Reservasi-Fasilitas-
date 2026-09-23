<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
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
        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'Hanya reservasi pending yang dapat disetujui.',
            ], 422);
        }

        $conflict = DB::transaction(function () use ($request, $reservation) {
            // The same facility lock makes concurrent approvals deterministic.
            \App\Models\Facility::whereKey($reservation->facility_id)->lockForUpdate()->firstOrFail();

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
        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'Hanya reservasi pending yang dapat ditolak.',
            ], 422);
        }

        $request->validate([
            'decision_note' => 'nullable|string|max:1000',
        ]);

        $reservation->update([
            'status' => 'rejected',
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
            'decision_note' => $request->decision_note,
        ]);

        return response()->json([
            'message' => 'Reservasi berhasil ditolak.',
            'data' => $reservation->fresh(['user:id,name,email', 'facility:id,name,code']),
        ]);
    }
}
