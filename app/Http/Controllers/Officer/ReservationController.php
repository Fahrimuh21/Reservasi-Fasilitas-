<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // 1. Menampilkan daftar antrian reservasi yang masih pending & statistik
    public function index()
    {
        $reservations = Reservation::with(['user', 'facility'])
            ->where('status', 'pending')
            ->get();

        $stats = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'approved' => Reservation::where('status', 'approved')->count(),
            'rejected' => Reservation::where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $reservations,
            'stats' => $stats
        ]);
    }

    // 2. Menyetujui reservasi dengan validasi pencegahan bentrok jadwal
    public function approve(int $id)
    {
        // Gunakan database transaction untuk mencegah race condition / crash data
        return DB::transaction(function () use ($id) {
            // Lock baris data agar tidak bisa dibaca/diubah proses lain secara bersamaan
            $reservation = Reservation::lockForUpdate()->findOrFail($id);

            // Validasi Conflict Checking (Cek jadwal beririsan pada fasilitas yang sama)
            $isConflict = Reservation::where('facility_id', $reservation->facility_id)
                ->where('status', 'approved')
                ->where(function($query) use ($reservation) {
                    $query->whereBetween('start_at', [$reservation->start_at, $reservation->end_at])
                          ->orWhereBetween('end_at', [$reservation->start_at, $reservation->end_at])
                          ->orWhere(function($q) use ($reservation) {
                              $q->where('start_at', '<=', $reservation->start_at)
                                ->where('end_at', '>=', $reservation->end_at);
                          });
                })->exists();

            if ($isConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal approve! Jadwal berbenturan dengan reservasi lain yang sudah disetujui.'
                ], 422);
            }

            $reservation->update([
                'status' => 'approved',
                'handled_by' => Auth::id() ?? 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil disetujui.'
            ]);
        });
    }

    // 3. Menolak reservasi dengan alasan
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $reservation = Reservation::findOrFail($id);
        
        $reservation->update([
            'status' => 'rejected',
            'handled_by' => Auth::id() ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil ditolak.'
        ]);
    }

    // 4. Pembatalan darurat untuk reservasi yang sudah approved
    public function cancelEmergency(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $reservation = Reservation::findOrFail($id);

        $reservation->update([
            'status' => 'cancelled',
            'handled_by' => Auth::id() ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi dibatalkan secara darurat.'
        ]);
    }
}