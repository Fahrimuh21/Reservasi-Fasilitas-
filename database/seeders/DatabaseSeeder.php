<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Dummy Tipe Fasilitas & Lokasi (Lengkap dengan kolom floor)
        $facilityTypeId = DB::table('facility_types')->insertGetId([
            'name' => 'Ruangan Umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('locations')->insertGetId([
            'building' => 'Gedung A',
            'floor' => '1',
            'name' => 'Gedung Utama',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Buat User Petugas & Peminjam
        $officer = User::firstOrCreate(
            ['email' => 'officer@example.com'],
            [
                'name' => 'Petugas Fasilitas',
                'password' => bcrypt('password'),
                'role' => 'officer',
                'account_status' => 'active'
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'peminjam@example.com'],
            [
                'name' => 'Mahasiswa Peminjam',
                'password' => bcrypt('password'),
                'role' => 'user',
                'account_status' => 'active'
            ]
        );

        // 3. Buat Dummy Fasilitas
        $facility1 = Facility::firstOrCreate(
            ['id' => 1],
            [
                'code' => 'AULA-01',
                'name' => 'Aula Utama',
                'facility_type_id' => $facilityTypeId,
                'location_id' => $locationId,
                'capacity' => 100,
                'status' => 'available'
            ]
        );

        $facility2 = Facility::firstOrCreate(
            ['id' => 2],
            [
                'code' => 'LAB-01',
                'name' => 'Lab Komputer A',
                'facility_type_id' => $facilityTypeId,
                'location_id' => $locationId,
                'capacity' => 40,
                'status' => 'available'
            ]
        );

        // 4. Buat Dummy Data Reservasi
        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility1->id,
            'start_at' => Carbon::tomorrow()->setHour(8)->setMinute(0),
            'end_at' => Carbon::tomorrow()->setHour(10)->setMinute(0),
            'status' => 'pending',
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility2->id,
            'start_at' => Carbon::tomorrow()->setHour(13)->setMinute(0),
            'end_at' => Carbon::tomorrow()->setHour(15)->setMinute(0),
            'status' => 'pending',
        ]);
    }
}