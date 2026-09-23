<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Facility;
use App\Models\FacilityType;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder – Seeder akun demo & data fasilitas untuk kebutuhan pengujian & submission.
 *
 * Mengisi 1 akun per role sesuai kewajiban PRD:
 *   - admin  : langsung active, dibuat langsung oleh sistem
 *   - officer: langsung active, registration_source = admin_created (tidak pernah self-register)
 *   - user   : langsung active (untuk demo); di production, user nyata akan melalui pending
 *
 * Mengisi data fasilitas demo:
 *   - 3 tipe fasilitas (Laboratorium, Ruang Kelas, Aula)
 *   - 4 lokasi (Gedung A Lt.1, Gedung A Lt.2, Gedung B Lt.1, Gedung C Lt.1)
 *   - 6 fasilitas dengan berbagai status (active, inactive, maintenance)
 *
 * Jalankan: php artisan db:seed
 * Atau reset: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =====================================================================
        // 1. Admin
        // =====================================================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@reservasi.test'],
            [
                'name'                => 'Administrator Sistem',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'account_status'      => 'active',
                'registration_source' => 'admin_created',
            ]
        );

        // =====================================================================
        // 2. Officer (Petugas)
        // Tidak pernah registrasi mandiri – selalu dibuat admin (US-13 SRS)
        // =====================================================================
        User::firstOrCreate(
            ['email' => 'petugas@reservasi.test'],
            [
                'name'                => 'Petugas Fasilitas',
                'password'            => Hash::make('password'),
                'role'                => 'officer',
                'account_status'      => 'active',
                'registration_source' => 'admin_created',
                'created_by'          => $admin->id,
                'verified_by'         => $admin->id,
                'verified_at'         => now(),
            ]
        );

        // =====================================================================
        // 3. Regular User (Pengguna / Mahasiswa)
        // Status 'active' untuk demo; normalnya user self-register → pending → admin approve
        // =====================================================================
        User::firstOrCreate(
            ['email' => 'user@reservasi.test'],
            [
                'name'                => 'Pengguna Mahasiswa',
                'password'            => Hash::make('password'),
                'role'                => 'user',
                'account_status'      => 'active',
                'user_type'           => 'mahasiswa',
                'registration_source' => 'self_register',
                'verified_by'         => $admin->id,
                'verified_at'         => now(),
            ]
        );

        // =====================================================================
        // 4. Contoh akun 'pending' untuk demo verifikasi admin
        // =====================================================================
        User::firstOrCreate(
            ['email' => 'pending@reservasi.test'],
            [
                'name'                => 'Calon Pengguna (Pending)',
                'password'            => Hash::make('password'),
                'role'                => 'user',
                'account_status'      => 'pending',
                'user_type'           => 'dosen',
                'registration_source' => 'self_register',
            ]
        );

        $this->command->info('✓ Seeder selesai. Akun demo:');
        $this->command->table(
            ['Role', 'Email', 'Password', 'Status'],
            [
                ['admin',   'admin@reservasi.test',   'password', 'active'],
                ['officer', 'petugas@reservasi.test', 'password', 'active'],
                ['user',    'user@reservasi.test',    'password', 'active'],
                ['user',    'pending@reservasi.test', 'password', 'pending (demo verifikasi)'],
            ]
        );

        // =====================================================================
        // 5. Tipe Fasilitas (Modul Facility Management – Orang 2)
        // =====================================================================
        $typeLab = FacilityType::firstOrCreate(
            ['name' => 'Laboratorium'],
            ['description' => 'Ruang laboratorium untuk praktikum dan penelitian']
        );

        $typeKelas = FacilityType::firstOrCreate(
            ['name' => 'Ruang Kelas'],
            ['description' => 'Ruang kelas untuk perkuliahan dan seminar']
        );

        $typeAula = FacilityType::firstOrCreate(
            ['name' => 'Aula'],
            ['description' => 'Ruang aula untuk acara besar dan kegiatan kemahasiswaan']
        );

        // =====================================================================
        // 6. Lokasi
        // =====================================================================
        $locA1 = Location::firstOrCreate(
            ['name' => 'Lab Komputer Lt. 1'],
            ['building' => 'Gedung A', 'floor' => '1']
        );

        $locA2 = Location::firstOrCreate(
            ['name' => 'Ruang Kelas Lt. 2'],
            ['building' => 'Gedung B', 'floor' => '2']
        );

        $locB1 = Location::firstOrCreate(
            ['name' => 'Lab Jaringan Lt. 1'],
            ['building' => 'Gedung C', 'floor' => '1']
        );

        $locC1 = Location::firstOrCreate(
            ['name' => 'Aula Utama Lt. 1'],
            ['building' => 'Gedung D', 'floor' => '1']
        );

        // Fasilitas sengaja tidak di-seed agar katalog dimulai dalam keadaan kosong.
        $this->command->info('✓ Katalog fasilitas dikosongkan. Tambahkan fasilitas melalui CRUD Admin.');
    }
}
