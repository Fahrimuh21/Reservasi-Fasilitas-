<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada data dummy atau sesuaikan ID user & facility dengan data di DB-mu
        Reservation::create([
            'user_id' => 1,
            'facility_id' => 1,
            'start_at' => Carbon::tomorrow()->setHour(8),
            'end_at' => Carbon::tomorrow()->setHour(10),
            'status' => 'pending',
        ]);

        Reservation::create([
            'user_id' => 1,
            'facility_id' => 2,
            'start_at' => Carbon::tomorrow()->setHour(13),
            'end_at' => Carbon::tomorrow()->setHour(15),
            'status' => 'pending',
        ]);
    }
}