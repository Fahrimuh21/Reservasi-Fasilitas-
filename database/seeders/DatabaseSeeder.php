<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder – Seeder akun demo untuk kebutuhan pengujian & submission.
 *
 * Mengisi 1 akun per role sesuai kewajiban PRD:
 *   - admin  : langsung active, dibuat langsung oleh sistem
 *   - officer: langsung active, registration_source = admin_created (tidak pernah self-register)
 *   - user   : langsung active (untuk demo); di production, user nyata akan melalui pending
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
        $admin = User::firstOrNew(['email' => 'admin@reservasi.test']);
        if (!$admin->exists) {
            $admin->forceFill([
                'name'                => 'Administrator Sistem',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'account_status'      => 'active',
                'registration_source' => 'admin_created',
            ])->save();
        }

        // =====================================================================
        // 2. Officer (Petugas)
        // Tidak pernah registrasi mandiri – selalu dibuat admin (US-13 SRS)
        // =====================================================================
        $officer = User::firstOrNew(['email' => 'petugas@reservasi.test']);
        if (!$officer->exists) {
            $officer->forceFill([
                'name'                => 'Petugas Fasilitas',
                'password'            => Hash::make('password'),
                'role'                => 'officer',
                'account_status'      => 'active',
                'registration_source' => 'admin_created',
                'created_by'          => $admin->id,
                'verified_by'         => $admin->id,
                'verified_at'         => now(),
            ])->save();
        }

        // =====================================================================
        // 3. Regular User (Pengguna / Mahasiswa)
        // Status 'active' untuk demo; normalnya user self-register → pending → admin approve
        // =====================================================================
        $user = User::firstOrNew(['email' => 'user@reservasi.test']);
        if (!$user->exists) {
            $user->forceFill([
                'name'                => 'Pengguna Mahasiswa',
                'password'            => Hash::make('password'),
                'role'                => 'user',
                'account_status'      => 'active',
                'user_type'           => 'mahasiswa',
                'registration_source' => 'self_register',
                'verified_by'         => $admin->id,
                'verified_at'         => now(),
            ])->save();
        }

        // =====================================================================
        // 4. Contoh akun 'pending' untuk demo verifikasi admin
        // =====================================================================
        $pending = User::firstOrNew(['email' => 'pending@reservasi.test']);
        if (!$pending->exists) {
            $pending->forceFill([
                'name'                => 'Calon Pengguna (Pending)',
                'password'            => Hash::make('password'),
                'role'                => 'user',
                'account_status'      => 'pending',
                'user_type'           => 'dosen',
                'registration_source' => 'self_register',
            ])->save();
        }

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

    }
}
