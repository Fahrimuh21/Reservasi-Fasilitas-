DESIGN.md

Sistem Reservasi & Pelaporan Fasilitas Kampus

Tech Stack: Laravel 12 + Vue 3 + PostgreSQL

Dokumen ini menjelaskan rancangan teknis aplikasi Sistem Reservasi & Pelaporan Fasilitas Kampus dengan backend Laravel 12, frontend Vue 3, dan database PostgreSQL.

1. Technology Stack

Backend      : Laravel 12
Frontend     : Vue 3
Bundler      : Vite
Database     : PostgreSQL
ORM          : Laravel Eloquent ORM
Authentication: Laravel Session Authentication
Styling      : Bootstrap 5
Versioning   : Git + GitHub/GitLab

Untuk project satu repository, struktur direkomendasikan menggunakan Laravel sebagai backend utama dan Vue sebagai frontend yang dibangun melalui Vite.

2. Gambaran Arsitektur

Browser
   │
   ▼
Vue 3
   │
   ├── Components
   ├── Pages
   ├── Forms
   └── State
   │
   ▼
Laravel Routes
   │
   ▼
Middleware
   │
   ├── auth
   ├── role
   └── verified
   │
   ▼
Controller
   │
   ▼
Service / Business Logic
   │
   ▼
Eloquent Model
   │
   ▼
PostgreSQL

Laravel menangani:

Routing.

Authentication.

Authorization.

Validasi server.

Business logic.

Database.

Upload file.

Export data.

Vue menangani:

Tampilan.

Form.

Interaksi pengguna.

Filter.

Modal.

Kalender.

Dashboard.

Feedback validasi dari server.

3. Aktor Sistem

Pengunjung

Tanpa login:

Melihat daftar fasilitas.

Mencari fasilitas.

Memfilter fasilitas.

Melihat status ketersediaan per slot waktu.

Pengunjung tidak boleh melihat:

Nama pemesan.

Tujuan penggunaan.

Informasi akun pemesan.

Detail laporan internal.

Pengguna

Pengguna terdiri atas mahasiswa, dosen, dan staf.

Fitur:

Registrasi.

Login.

Logout.

Melihat fasilitas.

Melihat availability.

Mengajukan reservasi.

Melihat riwayat reservasi.

Membatalkan reservasi milik sendiri.

Membuat laporan kerusakan.

Upload foto.

Melihat status laporan.

Petugas

Fitur:

Login.

Dashboard.

Melihat antrian reservasi.

Approve reservasi.

Reject reservasi.

Membatalkan reservasi approved.

Melihat laporan kerusakan.

Memproses laporan.

Menambahkan catatan resolusi.

Mengubah status fasilitas.

Menandai fasilitas dalam perbaikan.

Petugas tidak dapat melakukan registrasi mandiri.

Admin

Fitur:

Login.

Mengelola akun.

Membuat akun petugas.

Membuat akun pengguna.

Verifikasi pengguna.

Menolak registrasi pengguna.

CRUD fasilitas.

Kelola tipe fasilitas.

Kelola lokasi.

Melihat rekap.

Export CSV.

Export Excel.

Export PDF.

4. Struktur Project Laravel + Vue

project/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   │
│   │   │   ├── FacilityController.php
│   │   │   ├── ReservationController.php
│   │   │   ├── ReportController.php
│   │   │   ├── Officer/
│   │   │   │   ├── ReservationController.php
│   │   │   │   └── ReportController.php
│   │   │   │
│   │   │   └── Admin/
│   │   │       ├── UserController.php
│   │   │       ├── FacilityController.php
│   │   │       └── RecapController.php
│   │   │
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   │
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       ├── RegisterRequest.php
│   │       ├── StoreReservationRequest.php
│   │       ├── StoreReportRequest.php
│   │       └── StoreFacilityRequest.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Facility.php
│   │   ├── FacilityType.php
│   │   ├── Location.php
│   │   ├── Reservation.php
│   │   ├── Report.php
│   │   ├── ReportPhoto.php
│   │   └── FacilityStatusLog.php
│   │
│   ├── Services/
│   │   ├── ReservationService.php
│   │   ├── FacilityService.php
│   │   ├── ReportService.php
│   │   └── RecapService.php
│   │
│   └── Policies/
│       ├── ReservationPolicy.php
│       └── ReportPolicy.php
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   ├── app.js
│   │   ├── components/
│   │   │   ├── Navbar.vue
│   │   │   ├── Sidebar.vue
│   │   │   ├── FacilityCard.vue
│   │   │   ├── StatusBadge.vue
│   │   │   ├── ReservationForm.vue
│   │   │   └── ReportForm.vue
│   │   │
│   │   ├── layouts/
│   │   │   ├── GuestLayout.vue
│   │   │   ├── UserLayout.vue
│   │   │   ├── OfficerLayout.vue
│   │   │   └── AdminLayout.vue
│   │   │
│   │   └── pages/
│   │       ├── Auth/
│   │       ├── Facilities/
│   │       ├── Reservations/
│   │       ├── Reports/
│   │       ├── Officer/
│   │       └── Admin/
│   │
│   └── views/
│       └── app.blade.php
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   └── app/
│       └── public/
│           └── reports/
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── .env
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
├── README.md
├── COMMIT.md
└── DESIGN.md

5. Database Design

users

id
name
email
password
role
account_status
registration_source
created_by
verified_by
verified_at
created_at
updated_at

Role:

user
officer
admin

Status akun:

pending
active
rejected
suspended

facility_types

id
name
description
created_at
updated_at

locations

id
name
building
floor
description
created_at
updated_at

facilities

id
code
name
facility_type_id
location_id
capacity
description
status
status_note
created_by
created_at
updated_at

Status:

active
maintenance
inactive

reservations

id
user_id
facility_id
start_at
end_at
purpose
status
handled_by
handled_at
decision_note
cancellation_reason
cancelled_by
cancelled_at
created_at
updated_at

Status:

pending
approved
rejected
cancelled

reports

id
reporter_id
facility_id
category
description
status
handled_by
resolution_note
closed_at
created_at
updated_at

Status:

new
in_progress
resolved
rejected

report_photos

id
report_id
file_path
created_at
updated_at

facility_status_logs

id
facility_id
old_status
new_status
related_report_id
changed_by
note
created_at
updated_at

6. Relasi Eloquent

User.php

public function reservations()
{
    return $this->hasMany(Reservation::class);
}

public function reports()
{
    return $this->hasMany(Report::class, 'reporter_id');
}

Facility.php

public function type()
{
    return $this->belongsTo(FacilityType::class, 'facility_type_id');
}

public function location()
{
    return $this->belongsTo(Location::class);
}

public function reservations()
{
    return $this->hasMany(Reservation::class);
}

public function reports()
{
    return $this->hasMany(Report::class);
}

public function statusLogs()
{
    return $this->hasMany(FacilityStatusLog::class);
}

Reservation.php

public function user()
{
    return $this->belongsTo(User::class);
}

public function facility()
{
    return $this->belongsTo(Facility::class);
}

public function handler()
{
    return $this->belongsTo(User::class, 'handled_by');
}

Report.php

public function reporter()
{
    return $this->belongsTo(User::class, 'reporter_id');
}

public function facility()
{
    return $this->belongsTo(Facility::class);
}

public function photos()
{
    return $this->hasMany(ReportPhoto::class);
}

public function handler()
{
    return $this->belongsTo(User::class, 'handled_by');
}

7. Laravel Routes

Public

Route::get('/', [FacilityController::class, 'index']);

Route::get('/facilities', [FacilityController::class, 'index'])
    ->name('facilities.index');

Route::get('/facilities/{facility}', [FacilityController::class, 'show'])
    ->name('facilities.show');

Route::get(
    '/facilities/{facility}/availability',
    [FacilityController::class, 'availability']
)->name('facilities.availability');

8. Authentication Routes

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create']);
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);

});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth');

9. User Routes

Route::middleware(['auth', 'role:user'])->group(function () {

    Route::get(
        '/reservations',
        [ReservationController::class, 'index']
    );

    Route::get(
        '/reservations/create',
        [ReservationController::class, 'create']
    );

    Route::post(
        '/reservations',
        [ReservationController::class, 'store']
    );

    Route::get(
        '/reservations/{reservation}',
        [ReservationController::class, 'show']
    );

    Route::post(
        '/reservations/{reservation}/cancel',
        [ReservationController::class, 'cancel']
    );


    Route::get(
        '/reports',
        [ReportController::class, 'index']
    );

    Route::get(
        '/reports/create',
        [ReportController::class, 'create']
    );

    Route::post(
        '/reports',
        [ReportController::class, 'store']
    );

});

10. Officer Routes

Route::prefix('officer')
    ->middleware(['auth', 'role:officer'])
    ->group(function () {

        Route::get(
            '/dashboard',
            [OfficerDashboardController::class, 'index']
        );
        Route::get(
            '/reservations',
            [OfficerReservationController::class, 'index']
        );

        Route::post(
            '/reservations/{reservation}/approve',
            [OfficerReservationController::class, 'approve']
        );

        Route::post(
            '/reservations/{reservation}/reject',
            [OfficerReservationController::class, 'reject']
        );

        Route::post(
            '/reservations/{reservation}/cancel',
            [OfficerReservationController::class, 'cancel']
        );

        Route::get(
            '/reports',
            [OfficerReportController::class, 'index']
        );

        Route::post(
            '/reports/{report}/process',
            [OfficerReportController::class, 'process']
        );

        Route::post(
            '/reports/{report}/resolve',
            [OfficerReportController::class, 'resolve']
        );

        Route::post(
            '/reports/{report}/reject',
            [OfficerReportController::class, 'reject']
        );

    });

11. Admin Routes

Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::resource(
            'facilities',
            AdminFacilityController::class
        );

        Route::get(
            '/users',
            [AdminUserController::class, 'index']
        );

        Route::get(
            '/users/pending',
            [AdminUserController::class, 'pending']
        );

        Route::post(
            '/users/{user}/approve',
            [AdminUserController::class, 'approve']
        );

        Route::post(
            '/users/{user}/reject',
            [AdminUserController::class, 'reject']
        );

        Route::get(
            '/recap/occupancy',
            [RecapController::class, 'occupancy']
        );

        Route::get(
            '/recap/damage',
            [RecapController::class, 'damage']
        );

    });

12. Vue Page Structure

resources/js/pages/
│
├── Auth/
│   ├── Login.vue
│   └── Register.vue
│
├── Facilities/
│   ├── Index.vue
│   └── Show.vue
│
├── Reservations/
│   ├── Index.vue
│   ├── Create.vue
│   └── Show.vue
│
├── Reports/
│   ├── Index.vue
│   ├── Create.vue
│   └── Show.vue
│
├── Officer/
│   ├── Dashboard.vue
│   ├── Reservations/
│   │   └── Index.vue
│   └── Reports/
│       └── Index.vue
│
└── Admin/
    ├── Dashboard.vue
    ├── Users/
    ├── Facilities/
    └── Recaps/

13. Vue Components

Komponen reusable:

Navbar.vue
Sidebar.vue
FacilityCard.vue
FacilityFilter.vue
AvailabilityTable.vue
ReservationForm.vue
ReservationStatusBadge.vue
ReportForm.vue
ReportStatusBadge.vue
ConfirmModal.vue
Pagination.vue
EmptyState.vue
AlertMessage.vue

Prinsip:

Page menangani konteks halaman.

Component menangani UI reusable.

Business rule utama tetap berada di Laravel.

Vue tidak menjadi satu-satunya tempat validasi penting.

14. Validasi Laravel

Gunakan Laravel Form Request.

Contoh:

class StoreReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'facility_id' => [
                'required',
                'exists:facilities,id'
            ],

            'start_at' => [
                'required',
                'date'
            ],

            'end_at' => [
                'required',
                'date',
                'after:start_at'
            ],

            'purpose' => [
                'required',
                'string',
                'max:1000'
            ],
        ];
    }
}

Validasi slot waktu tidak cukup hanya menggunakan rule dasar. Business rule harus diperiksa di ReservationService.

15. Reservation Service

Contoh struktur:

class ReservationService
{
    public function create(User $user, array $data): Reservation
    {
        $this->validateOperationalHours(
            $data['start_at'],
            $data['end_at']
        );

        $this->validateThirtyMinuteSlot(
            $data['start_at'],
            $data['end_at']
        );

        return Reservation::create([
            'user_id'     => $user->id,
            'facility_id' => $data['facility_id'],
            'start_at'    => $data['start_at'],
            'end_at'      => $data['end_at'],
            'purpose'     => $data['purpose'],
            'status'      => 'pending',
        ]);
    }
}

16. Aturan Jam Reservasi

Jam operasional:

07.00 – 20.00

Slot:

30 menit

Valid:

07.00
07.30
08.00
08.30
...
19.30
20.00

Start tidak boleh:

06.30
07.15
20.00

End tidak boleh lebih dari:

20.00

17. Conflict Checking

Ketika petugas approve:

$conflict = Reservation::query()
    ->where('facility_id', $reservation->facility_id)
    ->where('status', 'approved')
    ->where('id', '!=', $reservation->id)
    ->where('start_at', '<', $reservation->end_at)
    ->where('end_at', '>', $reservation->start_at)
    ->exists();

if ($conflict) {
    throw ValidationException::withMessages([
        'reservation' => 'Jadwal reservasi bertabrakan.'
    ]);
}

Selain validasi Laravel, PostgreSQL tetap disarankan memiliki constraint database untuk mencegah race condition.

18. Approval Transaction

Approval sebaiknya menggunakan database transaction.

DB::transaction(function () use ($reservation, $officer) {

    // cek fasilitas
    // lock data yang diperlukan
    // cek konflik
    // update reservation

    $reservation->update([
        'status'     => 'approved',
        'handled_by' => $officer->id,
        'handled_at' => now(),
    ]);

});

Tujuannya agar dua petugas tidak dapat menyetujui dua reservasi bentrok pada waktu hampir bersamaan.

19. Middleware Role

Contoh:

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ) {
        if (
            !$request->user() ||
            !in_array($request->user()->role, $roles)
        ) {
            abort(403);
        }

        return $next($request);
    }
}

Contoh pemakaian:

Route::middleware([
    'auth',
    'role:admin'
]);

20. Authorization Policy

Selain middleware, gunakan Policy untuk resource milik pengguna.

Contoh pembatalan reservasi:

public function cancel(
    User $user,
    Reservation $reservation
): bool {
    return $reservation->user_id === $user->id;
}

Tujuannya agar pengguna tidak dapat membatalkan reservasi milik akun lain hanya dengan mengubah ID URL.

21. Upload Foto Laporan

Gunakan Laravel Storage.

Contoh:

$path = $request->file('photo')
    ->store('reports', 'public');

Database menyimpan:

reports/xxxx.jpg

Bukan file binary.

Validasi:

'photo' => [
    'nullable',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:2048'
]

22. Vue Form

Contoh struktur form reservasi:

<script setup>
import { reactive } from 'vue'

const form = reactive({
    facility_id: '',
    start_at: '',
    end_at: '',
    purpose: ''
})
</script>

<template>
    <form>
        <select v-model="form.facility_id">
            <!-- facilities -->
        </select>

        <input
            v-model="form.start_at"
            type="datetime-local"
        >

        <input
            v-model="form.end_at"
            type="datetime-local"
        >

        <textarea
            v-model="form.purpose"
        />

        <button type="submit">
            Ajukan Reservasi
        </button>
    </form>
</template>

Client-side validation membantu UX, tetapi keputusan akhir tetap ditentukan Laravel.

23. Availability Response

Backend dapat mengirim data:

{
    "facility_id": 1,
    "date": "2026-09-10",
    "slots": [
        {
            "start": "07:00",
            "end": "07:30",
            "available": true
        },
        {
            "start": "07:30",
            "end": "08:00",
            "available": false
        }
    ]
}

Frontend Vue menampilkan status:

07.00 – 07.30  Tersedia
07.30 – 08.00  Tidak Tersedia

Data pemesan tidak dikirim kepada pengunjung.

24. User Dashboard

Menu:

Dashboard
Fasilitas
Reservasi Saya
Buat Reservasi
Laporan Saya
Buat Laporan
Profil
Logout

Card:

Reservasi Pending
Reservasi Approved
Reservasi Cancelled
Laporan Aktif

25. Officer Dashboard

Menu:

Dashboard
Reservasi
Laporan
Status Fasilitas
Logout

Card:

Reservasi Pending
Laporan Baru
Laporan Diproses
Fasilitas Maintenance

26. Admin Dashboard

Menu:

Dashboard
Pengguna
Verifikasi Akun
Petugas
Fasilitas
Tipe Fasilitas
Lokasi
Rekap
Export
Logout

Card:

Total Pengguna
Total Petugas
Total Fasilitas
Reservasi Bulan Ini
Laporan Bulan Ini
Fasilitas Maintenance

27. Security

Laravel wajib menangani:

Password hashing.

Authentication.

CSRF protection.

Authorization.

Server-side validation.

SQL injection protection melalui Query Builder/Eloquent.

File upload validation.

Session security.

Jangan menyimpan:

password plaintext
database password di repository
APP_KEY di repository

Gunakan .env.

28. Environment

Contoh .env.example:

APP_NAME="Campus Facility"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=campus_facility
DB_USERNAME=postgres
DB_PASSWORD=

SESSION_DRIVER=database

File .env tidak boleh masuk repository.

29. Migration

Setiap tabel dibuat melalui Laravel Migration.

Contoh:

php artisan make:model Facility -m
php artisan make:model Reservation -m
php artisan make:model Report -m

Migration disimpan dalam:

database/migrations/

Database tim sebaiknya dibuat dari migration, bukan dari edit manual satu per satu.

30. Seeder

Seeder yang disarankan:

AdminSeeder
OfficerSeeder
UserSeeder
FacilityTypeSeeder
LocationSeeder
FacilitySeeder
ReservationSeeder
ReportSeeder

Jalankan:

php artisan migrate:fresh --seed

31. Pembagian 5 Anggota

Anggota 1

Authentication & User Management

Folder utama:

app/Http/Controllers/Auth/
app/Models/User.php
resources/js/pages/Auth/
resources/js/pages/Admin/Users/

Fitur:

Register.

Login.

Logout.

Verifikasi.

User management.

Officer account management.

Anggota 2

Facility Management

Folder:

app/Models/Facility.php
app/Models/FacilityType.php
app/Models/Location.php
app/Http/Controllers/FacilityController.php
resources/js/pages/Facilities/
resources/js/pages/Admin/Facilities/

Fitur:

List fasilitas.

Detail.

Filter.

Search.

Availability.

CRUD fasilitas.

Anggota 3

User Reservation

Folder:

app/Models/Reservation.php
app/Http/Controllers/ReservationController.php
app/Services/ReservationService.php
resources/js/pages/Reservations/

Fitur:

Create reservation.

Validasi waktu.

Riwayat.

Detail.

Cancel reservation.

Anggota 4

Officer Reservation

Folder:

app/Http/Controllers/Officer/
resources/js/pages/Officer/

Fitur:

Dashboard petugas.

Queue reservasi.

Approve.

Reject.

Conflict check.

Emergency cancellation.

Anggota 5

Report & Recap

Folder:

app/Models/Report.php
app/Models/ReportPhoto.php
app/Models/FacilityStatusLog.php
app/Http/Controllers/ReportController.php
app/Http/Controllers/Admin/RecapController.php
resources/js/pages/Reports/
resources/js/pages/Admin/Recaps/

Fitur:

Damage report.

Photo upload.

Report processing.

Maintenance status.

Occupancy recap.

Damage recap.

Export.

32. Branch Git

main
develop

Feature branches:

feature/auth
feature/facility
feature/user-reservation
feature/officer-reservation
feature/reporting

Workflow:

feature/*
    ↓
develop
    ↓
integration testing
    ↓
main

33. Testing Laravel

Gunakan Feature Test untuk fitur penting.

Contoh:

php artisan make:test ReservationTest

Test penting:

user dapat membuat reservasi
guest tidak dapat membuat reservasi
reservasi di luar jam operasional ditolak
slot bukan kelipatan 30 menit ditolak
petugas dapat approve
jadwal bentrok tidak dapat di-approve
user tidak dapat cancel reservasi orang lain
admin dapat membuat petugas
petugas tidak dapat registrasi sendiri

34. Testing Vue

Minimal lakukan:

Form validation.

Loading state.

Error message.

Empty state.

Pagination.

Search.

Filter.

Modal confirmation.

Mobile responsiveness.

35. Definition of Done

Fitur dianggap selesai jika:

Route tersedia.

Controller berjalan.

Request validation tersedia.

Authorization benar.

Model relationship benar.

UI Vue berjalan.

Error state ditampilkan.

Database migration tersedia.

Feature sudah diuji.

Tidak ada credential dalam repository.

Commit sudah dibuat.

Pull Request sudah direview.

Tidak merusak fitur anggota lain.

36. Prioritas Implementasi

1. Laravel 12 project setup
2. Vue + Vite setup
3. PostgreSQL connection
4. Migration
5. Seeder
6. Authentication
7. Role middleware
8. Facility
9. Availability
10. User reservation
11. Officer reservation
12. Damage reports
13. Facility maintenance
14. Admin management
15. Recap
16. Export
17. Integration testing
18. UI polishing
19. Documentation

37. Kesimpulan

Project menggunakan Laravel 12 sebagai backend, Vue 3 sebagai frontend, dan PostgreSQL sebagai database.

Laravel menjadi sumber utama business logic dan validasi. Vue berfungsi sebagai presentation layer dan menangani interaksi pengguna. Validasi reservasi tidak boleh hanya dilakukan di Vue karena pengguna dapat melewati validasi frontend.

Arsitektur ini memungkinkan lima anggota mengembangkan modul berbeda secara paralel tanpa mencampur seluruh logika aplikasi dalam satu controller atau satu halaman Vue.