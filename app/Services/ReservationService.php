<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Create a new reservation.
     *
     * @throws ValidationException
     */
    public function create(User $user, array $data): Reservation
    {
        $startAt = Carbon::parse($data['start_at']);
        $endAt = Carbon::parse($data['end_at']);
        $facilityId = $data['facility_id'];

        if ($startAt->isPast()) {
            throw ValidationException::withMessages(['start_at' => 'Waktu reservasi harus di masa mendatang.']);
        }

        $this->validateOperationalHours($startAt, $endAt);
        $this->validateThirtyMinuteSlot($startAt, $endAt);
        $this->validateSameDay($startAt, $endAt);

        return DB::transaction(function () use ($user, $data, $startAt, $endAt, $facilityId) {
            // Serialize bookings for the same facility to prevent race-condition double booking.
            Facility::whereKey($facilityId)->lockForUpdate()->firstOrFail();
            $this->validateFacilityActive($facilityId);
            $this->validateConflict($facilityId, $startAt, $endAt);

            return Reservation::create([
                'user_id' => $user->id,
                'facility_id' => $facilityId,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'purpose' => $data['purpose'],
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Cancel an existing reservation.
     *
     * @throws ValidationException
     */
    public function cancel(User $user, Reservation $reservation, string $reason): Reservation
    {
        return DB::transaction(function () use ($user, $reservation, $reason) {
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'reservation' => 'Anda tidak memiliki hak untuk membatalkan reservasi ini.',
                ]);
            }

            if (! in_array($reservation->status, ['pending', 'approved']) || $reservation->start_at->isPast()) {
                throw ValidationException::withMessages([
                    'reservation' => 'Reservasi sudah selesai, dibatalkan, atau ditolak.',
                ]);
            }

            $reservation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
            ]);

            return $reservation;
        });
    }

    private function validateOperationalHours(Carbon $startAt, Carbon $endAt): void
    {
        $startHour = $startAt->copy()->format('H:i');
        $endHour = $endAt->copy()->format('H:i');

        if ($startHour < '07:00' || $endHour > '20:00' || $startHour >= '20:00') {
            throw ValidationException::withMessages([
                'time' => 'Jam operasional reservasi adalah 07.00 - 20.00.',
            ]);
        }
    }

    private function validateThirtyMinuteSlot(Carbon $startAt, Carbon $endAt): void
    {
        if ($startAt->minute % 30 !== 0 || $endAt->minute % 30 !== 0 || $startAt->second !== 0 || $endAt->second !== 0) {
            throw ValidationException::withMessages([
                'time' => 'Slot reservasi harus menggunakan interval 30 menit.',
            ]);
        }
    }

    private function validateSameDay(Carbon $startAt, Carbon $endAt): void
    {
        if (! $startAt->isSameDay($endAt)) {
            throw ValidationException::withMessages([
                'time' => 'Reservasi harus berada pada tanggal yang sama.',
            ]);
        }

        if ($startAt->gte($endAt)) {
            throw ValidationException::withMessages([
                'time' => 'Waktu mulai harus lebih kecil dari waktu selesai.',
            ]);
        }
    }

    private function validateFacilityActive(int $facilityId): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility || ! $facility->isActive()) {
            throw ValidationException::withMessages([
                'facility' => 'Fasilitas tidak tersedia atau sedang dalam perbaikan.',
            ]);
        }
    }

    private function validateConflict(int $facilityId, Carbon $startAt, Carbon $endAt): void
    {
        // Check for approved reservations that overlap
        // We also check pending reservations to avoid double booking
        $conflict = Reservation::where('facility_id', $facilityId)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where(function ($q) use ($startAt, $endAt) {
                    // New reservation starts during an existing one
                    $q->where('start_at', '<', $endAt)
                        ->where('end_at', '>', $startAt);
                });
            })->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'time' => 'Jadwal reservasi bertabrakan dengan jadwal yang sudah ada.',
            ]);
        }
    }
}
