<?php

use App\Models\Facility;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$database = getenv('DB_DATABASE');
if (! $database || file_exists($database) || ! str_starts_with(realpath(dirname($database)), realpath(sys_get_temp_dir()))) {
    throw new RuntimeException('Browser checks require a new database in a temporary directory.');
}
touch($database);
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database]);
Artisan::call('migrate', ['--force' => true]);
$user = User::factory()->create(['name' => 'Pengguna Uji', 'email' => 'browser-user@example.test']);
$officer = User::factory()->officer()->create(['name' => 'Petugas Uji', 'email' => 'browser-officer@example.test']);
$admin = User::factory()->admin()->create(['name' => 'Administrator Uji', 'email' => 'browser-admin@example.test']);
$pendingUser = User::factory()->pending()->create([
    'name' => 'Pendaftar Browser',
    'email' => 'browser-pending@example.test',
    'password' => 'password',
    'user_type' => 'mahasiswa',
]);
User::factory()->count(4)->pending()->create();
$type = DB::table('facility_types')->insertGetId(['name' => 'Ruang Rapat']);
$location = DB::table('locations')->insertGetId(['name' => 'Ruang 1', 'building' => 'Gedung A', 'floor' => '1']);
$facility = Facility::create([
    'code' => 'BROWSER-01', 'name' => 'Ruang Rapat Pengujian', 'facility_type_id' => $type,
    'location_id' => $location, 'capacity' => 20, 'status' => 'active',
]);
echo json_encode([
    'facility_id' => $facility->id,
    'pending_user_id' => $pendingUser->id,
    'date' => now()->addDay()->toDateString(),
]);
