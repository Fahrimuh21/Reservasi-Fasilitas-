<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Get list of user's reservations.
     */
    public function index(Request $request)
    {
        $reservations = Reservation::with(['facility', 'facility.facilityType'])
            ->where('user_id', $request->user()->id)
            ->orderBy('start_at', 'desc')
            ->get();

        return response()->json([
            'data' => $reservations
        ]);
    }

    /**
     * Create a new reservation.
     */
    public function store(StoreReservationRequest $request)
    {
        try {
            $reservation = $this->reservationService->create(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Reservasi berhasil dibuat.',
                'data' => $reservation
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Gagal membuat reservasi.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan sistem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific reservation details.
     */
    public function show(Request $request, Reservation $reservation)
    {
        // Authorization: only the owner can view their reservation
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $reservation->load(['facility', 'facility.facilityType', 'facility.location', 'handler', 'cancelledBy']);

        return response()->json([
            'data' => $reservation
        ]);
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $cancelledReservation = $this->reservationService->cancel(
                $request->user(),
                $reservation,
                $request->reason
            );

            return response()->json([
                'message' => 'Reservasi berhasil dibatalkan.',
                'data' => $cancelledReservation
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Gagal membatalkan reservasi.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan sistem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
