# DESIGN.md
## Sistem Reservasi & Pelaporan Fasilitas Kampus
### Tech Stack: Laravel 12 + Vue 3 + PostgreSQL

Dokumen ini menjelaskan rancangan teknis aplikasi **Sistem Reservasi & Pelaporan Fasilitas Kampus** dengan backend **Laravel 12**, frontend **Vue 3**, dan database **PostgreSQL**.

---

# 1. Technology Stack

```text
Backend      : Laravel 12
Frontend     : Vue 3
Bundler      : Vite
Database     : PostgreSQL
ORM          : Laravel Eloquent ORM
Authentication: Laravel Session Authentication
Styling      : Bootstrap 5 / Tailwind CSS
Versioning   : Git + GitHub/GitLab
```

Untuk project satu repository, struktur direkomendasikan menggunakan Laravel sebagai backend utama dan Vue sebagai frontend yang dibangun melalui Vite.

---

# 2. Gambaran Arsitektur

```text
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
```

Laravel menangani:

- Routing.
- Authentication.
- Authorization.
- Validasi server.
- Business logic.
- Database.
- Upload file.
- Export data.

Vue menangani:

- Tampilan.
- Form.
- Interaksi pengguna.
- Filter.
- Modal.
- Kalender.
- Dashboard.
- Feedback validasi dari server.

---

# 3. Aktor Sistem

## Pengunjung

Tanpa login:

- Melihat daftar fasilitas.
- Mencari fasilitas.
- Memfilter fasilitas.
- Melihat status ketersediaan per slot waktu.

Pengunjung tidak boleh melihat:

- Nama pemesan.
- Tujuan penggunaan.
- Informasi akun pemesan.
- Detail laporan internal.

---

## Pengguna

Pengguna terdiri atas mahasiswa, dosen, dan staf.

Fitur:

- Registrasi.
- Login.
- Logout.
- Melihat fasilitas.
- Melihat availability.
- Mengajukan reservasi.
- Melihat riwayat reservasi.
- Membatalkan reservasi milik sendiri.
- Membuat laporan kerusakan.
- Upload foto.
- Melihat status laporan.

---

## Petugas

Fitur:

- Login.
- Dashboard.
- Melihat antrian reservasi.
- Approve reservasi.
- Reject reservasi.
- Membatalkan reservasi approved.
- Melihat laporan kerusakan.
- Memproses laporan.
- Menambahkan catatan resolusi.
- Mengubah status fasilitas.
- Menandai fasilitas dalam perbaikan.

Petugas tidak dapat melakukan registrasi mandiri.

---

## Admin

Fitur:

- Login.
- Mengelola akun.
- Membuat akun petugas.
- Membuat akun pengguna.
- Verifikasi pengguna.
- Menolak registrasi pengguna.
- CRUD fasilitas.
- Kelola tipe fasilitas.
- Kelola lokasi.
- Melihat rekap.
- Export CSV.
- Export Excel.
- Export PDF.

---

# 4. Struktur Project Laravel + Vue

```text
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
```

---

# 5. Database Design

## users

```text
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
```

Role:

```text
user
officer
admin
```

Status akun:

```text
pending
active
rejected
suspended
```

---

## facility_types

```text
id
name
description
created_at
updated_at
```

---

## locations

```text
id
name
building
floor
description
created_at
updated_at
```

---

## facilities

```text
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
```

Status:

```text
active
maintenance
inactive
```

---

## reservations

```text
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
```

Status:

```text
pending
approved
rejected
cancelled
```

---

## reports

```text
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
```

Status:

```text
new
in_progress
resolved
rejected
```

---

## report_photos

```text
id
report_id
file_path
created_at
updated_at
```

---

## facility_status_logs

```text
id
facility_id
old_status
new_status
related_report_id
changed_by
note
created_at
updated_at
```

---

# 6. Relasi Eloquent

## User.php

```php
public function reservations()
{
    return $this->hasMany(Reservation::class);
}

public function reports()
{
    return $this->hasMany(Report::class, 'reporter_id');
}
```

---

## Facility.php

```php
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
```

---

## Reservation.php

```php
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
```

---

## Report.php

```php
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
```

---

# 7. Laravel Routes

## Public

```php
Route::get('/', [FacilityController::class, 'index']);

Route::get('/facilities', [FacilityController::class, 'index'])
    ->name('facilities.index');

Route::get('/facilities/{facility}', [FacilityController::class, 'show'])
    ->name('facilities.show');

Route::get(
    '/facilities/{facility}/availability',
    [FacilityController::class, 'availability']
)->name('facilities.availability');
```

---

# 8. Authentication Routes

```php
Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create']);
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);

});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth');
```

---

# 9. User Routes

```php
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
```

---

# 10. Officer Routes

```php
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
```

---

# 11. Admin Routes

```php
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
```

---

# 12. Vue Page Structure

```text
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
```

---

# 13. Vue Components

Komponen reusable:

```text
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
```

Prinsip:

- Page menangani konteks halaman.
- Component menangani UI reusable.
- Business rule utama tetap berada di Laravel.
- Vue tidak menjadi satu-satunya tempat validasi penting.

---

# 14. Validasi Laravel

Gunakan Laravel Form Request.

Contoh:

```php
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
```

Validasi slot waktu tidak cukup hanya menggunakan rule dasar. Business rule harus diperiksa di `ReservationService`.

---

# 15. Reservation Service

Contoh struktur:

```php
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
```

---

# 16. Aturan Jam Reservasi

Jam operasional:

```text
07.00 – 20.00
```

Slot:

```text
30 menit
```

Valid:

```text
07.00
07.30
08.00
08.30
...
19.30
20.00
```

Start tidak boleh:

```text
06.30
07.15
20.00
```

End tidak boleh lebih dari:

```text
20.00
```

---

# 17. Conflict Checking

Ketika petugas approve:

```php
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
```

Selain validasi Laravel, PostgreSQL tetap disarankan memiliki constraint database untuk mencegah race condition.

---

# 18. Approval Transaction

Approval sebaiknya menggunakan database transaction.

```php
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
```

Tujuannya agar dua petugas tidak dapat menyetujui dua reservasi bentrok pada waktu hampir bersamaan.

---

# 19. Middleware Role

Contoh:

```php
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
```

Contoh pemakaian:

```php
Route::middleware([
    'auth',
    'role:admin'
]);
```

---

# 20. Authorization Policy

Selain middleware, gunakan Policy untuk resource milik pengguna.

Contoh pembatalan reservasi:

```php
public function cancel(
    User $user,
    Reservation $reservation
): bool {
    return $reservation->user_id === $user->id;
}
```

Tujuannya agar pengguna tidak dapat membatalkan reservasi milik akun lain hanya dengan mengubah ID URL.

---

# 21. Upload Foto Laporan

Gunakan Laravel Storage.

Contoh:

```php
$path = $request->file('photo')
    ->store('reports', 'public');
```

Database menyimpan:

```text
reports/xxxx.jpg
```

Bukan file binary.

Validasi:

```php
'photo' => [
    'nullable',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:2048'
]
```

---

# 22. Vue Form

Contoh struktur form reservasi:

```vue
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
```

Client-side validation membantu UX, tetapi keputusan akhir tetap ditentukan Laravel.

---

# 23. Availability Response

Backend dapat mengirim data:

```json
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
```

Frontend Vue menampilkan status:

```text
07.00 – 07.30  Tersedia
07.30 – 08.00  Tidak Tersedia
```

Data pemesan tidak dikirim kepada pengunjung.

---

# 24. User Dashboard

Menu:

```text
Dashboard
Fasilitas
Reservasi Saya
Buat Reservasi
Laporan Saya
Buat Laporan
Profil
Logout
```

Card:

```text
Reservasi Pending
Reservasi Approved
Reservasi Cancelled
Laporan Aktif
```

---

# 25. Officer Dashboard

Menu:

```text
Dashboard
Reservasi
Laporan
Status Fasilitas
Logout
```

Card:

```text
Reservasi Pending
Laporan Baru
Laporan Diproses
Fasilitas Maintenance
```

---

# 26. Admin Dashboard

Menu:

```text
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
```

Card:

```text
Total Pengguna
Total Petugas
Total Fasilitas
Reservasi Bulan Ini
Laporan Bulan Ini
Fasilitas Maintenance
```

---

# 27. Security

Laravel wajib menangani:

- Password hashing.
- Authentication.
- CSRF protection.
- Authorization.
- Server-side validation.
- SQL injection protection melalui Query Builder/Eloquent.
- File upload validation.
- Session security.

Jangan menyimpan:

```text
password plaintext
database password di repository
APP_KEY di repository
```

Gunakan `.env`.

---

# 28. Environment

Contoh `.env.example`:

```env
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
```

File `.env` tidak boleh masuk repository.

---

# 29. Migration

Setiap tabel dibuat melalui Laravel Migration.

Contoh:

```bash
php artisan make:model Facility -m
php artisan make:model Reservation -m
php artisan make:model Report -m
```

Migration disimpan dalam:

```text
database/migrations/
```

Database tim sebaiknya dibuat dari migration, bukan dari edit manual satu per satu.

---

# 30. Seeder

Seeder yang disarankan:

```text
AdminSeeder
OfficerSeeder
UserSeeder
FacilityTypeSeeder
LocationSeeder
FacilitySeeder
ReservationSeeder
ReportSeeder
```

Jalankan:

```bash
php artisan migrate:fresh --seed
```

---

# 31. Pembagian 5 Anggota

## Anggota 1

### Authentication & User Management

Folder utama:

```text
app/Http/Controllers/Auth/
app/Models/User.php
resources/js/pages/Auth/
resources/js/pages/Admin/Users/
```

Fitur:

- Register.
- Login.
- Logout.
- Verifikasi.
- User management.
- Officer account management.

---

## Anggota 2

### Facility Management

Folder:

```text
app/Models/Facility.php
app/Models/FacilityType.php
app/Models/Location.php
app/Http/Controllers/FacilityController.php
resources/js/pages/Facilities/
resources/js/pages/Admin/Facilities/
```

Fitur:

- List fasilitas.
- Detail.
- Filter.
- Search.
- Availability.
- CRUD fasilitas.

---

## Anggota 3

### User Reservation

Folder:

```text
app/Models/Reservation.php
app/Http/Controllers/ReservationController.php
app/Services/ReservationService.php
resources/js/pages/Reservations/
```

Fitur:

- Create reservation.
- Validasi waktu.
- Riwayat.
- Detail.
- Cancel reservation.

---

## Anggota 4

### Officer Reservation

Folder:

```text
app/Http/Controllers/Officer/
resources/js/pages/Officer/
```

Fitur:

- Dashboard petugas.
- Queue reservasi.
- Approve.
- Reject.
- Conflict check.
- Emergency cancellation.

---

## Anggota 5

### Report & Recap

Folder:

```text
app/Models/Report.php
app/Models/ReportPhoto.php
app/Models/FacilityStatusLog.php
app/Http/Controllers/ReportController.php
app/Http/Controllers/Admin/RecapController.php
resources/js/pages/Reports/
resources/js/pages/Admin/Recaps/
```

Fitur:

- Damage report.
- Photo upload.
- Report processing.
- Maintenance status.
- Occupancy recap.
- Damage recap.
- Export.

---

# 32. Branch Git

```text
main
develop
```

Feature branches:

```text
feature/auth
feature/facility
feature/user-reservation
feature/officer-reservation
feature/reporting
```

Workflow:

```text
feature/*
    ↓
develop
    ↓
integration testing
    ↓
main
```

---

# 33. Testing Laravel

Gunakan Feature Test untuk fitur penting.

Contoh:

```bash
php artisan make:test ReservationTest
```

Test penting:

```text
user dapat membuat reservasi
guest tidak dapat membuat reservasi
reservasi di luar jam operasional ditolak
slot bukan kelipatan 30 menit ditolak
petugas dapat approve
jadwal bentrok tidak dapat di-approve
user tidak dapat cancel reservasi orang lain
admin dapat membuat petugas
petugas tidak dapat registrasi sendiri
```

---

# 34. Testing Vue

Minimal lakukan:

- Form validation.
- Loading state.
- Error message.
- Empty state.
- Pagination.
- Search.
- Filter.
- Modal confirmation.
- Mobile responsiveness.

---

# 35. Definition of Done

Fitur dianggap selesai jika:

- Route tersedia.
- Controller berjalan.
- Request validation tersedia.
- Authorization benar.
- Model relationship benar.
- UI Vue berjalan.
- Error state ditampilkan.
- Database migration tersedia.
- Feature sudah diuji.
- Tidak ada credential dalam repository.
- Commit sudah dibuat.
- Pull Request sudah direview.
- Tidak merusak fitur anggota lain.

---

# 36. Prioritas Implementasi

```text
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
```

---

# 37. Kesimpulan

Project menggunakan **Laravel 12 sebagai backend**, **Vue 3 sebagai frontend**, dan **PostgreSQL sebagai database**.

Laravel menjadi sumber utama business logic dan validasi. Vue berfungsi sebagai presentation layer dan menangani interaksi pengguna. Validasi reservasi tidak boleh hanya dilakukan di Vue karena pengguna dapat melewati validasi frontend.

Arsitektur ini memungkinkan lima anggota mengembangkan modul berbeda secara paralel tanpa mencampur seluruh logika aplikasi dalam satu controller atau satu halaman Vue.

# 38. Design System

Design system ini menjadi acuan visual utama untuk seluruh antarmuka **Sistem Reservasi & Pelaporan Fasilitas Kampus** yang dibangun menggunakan **Laravel 12 + Vue 3**.

## 38.1 Arah Visual

Karakter antarmuka:

- Modern dan bersih.
- Profesional dan akademik.
- Dominan warna biru.
- Background terang.
- Card putih dengan border tipis.
- Shadow ringan.
- Kontras teks kuat.
- Responsif di desktop, tablet, dan mobile.
- Komponen Vue dibuat reusable dan konsisten.

---

## 38.2 Typography

Font utama:

```text
Figtree
```

Fallback:

```css
font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system,
             BlinkMacSystemFont, "Segoe UI", sans-serif;
```

Skala typography:

| Elemen | Ukuran | Weight | Line Height |
|---|---:|---:|---:|
| Display | 40px | 700 | 1.2 |
| H1 | 32px | 700 | 1.25 |
| H2 | 28px | 700 | 1.25 |
| H3 | 24px | 600 | 1.3 |
| H4 | 20px | 600 | 1.35 |
| Body Large | 18px | 400 | 1.6 |
| Body | 16px | 400 | 1.6 |
| Body Small | 14px | 400 | 1.5 |
| Caption | 12px | 500 | 1.4 |
| Button | 14–16px | 600 | 1.2 |

Setup Figtree:

```css
@import url('https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap');

html,
body,
button,
input,
select,
textarea {
    font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
}
```

---

## 38.3 Primary Color Palette

| Token | Hex | Penggunaan |
|---|---|---|
| Blue 50 | `#EFF6FF` | Background aktif dan soft highlight |
| Blue 100 | `#DBEAFE` | Soft badge |
| Blue 200 | `#BFDBFE` | Border lembut |
| Blue 300 | `#93C5FD` | Accent ringan |
| Blue 400 | `#60A5FA` | Secondary accent |
| Blue 500 | `#3B82F6` | Accent |
| Blue 600 | `#2563EB` | **Primary utama** |
| Blue 700 | `#1D4ED8` | Hover primary |
| Blue 800 | `#1E40AF` | Active state |
| Blue 900 | `#1E3A8A` | Deep blue |

Warna utama:

```text
Primary        : #2563EB
Primary Hover  : #1D4ED8
Primary Active : #1E40AF
Primary Soft   : #EFF6FF
```

---

## 38.4 Neutral Palette

| Token | Hex |
|---|---|
| Slate 50 | `#F8FAFC` |
| Slate 100 | `#F1F5F9` |
| Slate 200 | `#E2E8F0` |
| Slate 300 | `#CBD5E1` |
| Slate 400 | `#94A3B8` |
| Slate 500 | `#64748B` |
| Slate 600 | `#475569` |
| Slate 700 | `#334155` |
| Slate 800 | `#1E293B` |
| Slate 900 | `#0F172A` |

Pemakaian:

```text
Page Background : #F8FAFC
Surface         : #FFFFFF
Border          : #E2E8F0
Text Primary    : #0F172A
Text Secondary  : #475569
Text Muted      : #64748B
```

---

## 38.5 Semantic Colors

| Status | Main | Soft Background |
|---|---|---|
| Success | `#16A34A` | `#F0FDF4` |
| Warning | `#D97706` | `#FFFBEB` |
| Danger | `#DC2626` | `#FEF2F2` |
| Info | `#0284C7` | `#F0F9FF` |

Mapping status:

```text
Approved / Active / Resolved : Success
Pending                       : Warning
Rejected / Cancelled          : Danger
Maintenance / In Progress     : Warning
Informasi                     : Info / Blue
```

---

## 38.6 CSS Design Tokens

Tambahkan ke `resources/css/app.css`:

```css
:root {
    --font-sans: 'Figtree', ui-sans-serif, system-ui, -apple-system,
                 BlinkMacSystemFont, "Segoe UI", sans-serif;

    --blue-50: #EFF6FF;
    --blue-100: #DBEAFE;
    --blue-200: #BFDBFE;
    --blue-300: #93C5FD;
    --blue-400: #60A5FA;
    --blue-500: #3B82F6;
    --blue-600: #2563EB;
    --blue-700: #1D4ED8;
    --blue-800: #1E40AF;
    --blue-900: #1E3A8A;

    --slate-50: #F8FAFC;
    --slate-100: #F1F5F9;
    --slate-200: #E2E8F0;
    --slate-300: #CBD5E1;
    --slate-400: #94A3B8;
    --slate-500: #64748B;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1E293B;
    --slate-900: #0F172A;

    --success: #16A34A;
    --warning: #D97706;
    --danger: #DC2626;
    --info: #0284C7;

    --page-background: #F8FAFC;
    --surface: #FFFFFF;
    --border: #E2E8F0;
    --text-primary: #0F172A;
    --text-secondary: #475569;
    --text-muted: #64748B;

    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;

    --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
    --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.08);
}
```

---

## 38.7 Layout

### Desktop

```text
Sidebar Width : 248px
Navbar Height : 64px
Page Padding  : 24–32px
```

### Tablet

```text
Sidebar      : Collapsible
Page Padding : 20–24px
```

### Mobile

```text
Sidebar      : Drawer
Page Padding : 16px
Card         : Full width
Table        : Horizontal scroll
```

---

## 38.8 Sidebar

```text
Background        : #FFFFFF
Border            : #E2E8F0
Text              : #475569
Active Background : #EFF6FF
Active Text       : #1D4ED8
Active Indicator  : #2563EB
```

Contoh:

```css
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    color: var(--slate-600);
}

.sidebar-link:hover {
    background: var(--slate-50);
}

.sidebar-link.active {
    background: var(--blue-50);
    color: var(--blue-700);
    font-weight: 600;
}
```

---

## 38.9 Navbar

```text
Height     : 64px
Background : #FFFFFF
Border     : #E2E8F0
```

Isi navbar:

- Nama halaman.
- Breadcrumb.
- Notifikasi jika diperlukan.
- Avatar pengguna.
- Dropdown profil.
- Logout.

---

## 38.10 Buttons

Primary:

```css
.btn-primary {
    min-height: 40px;
    padding: 10px 16px;
    border-radius: 8px;
    border: 1px solid var(--blue-600);
    background: var(--blue-600);
    color: #FFFFFF;
    font-weight: 600;
}

.btn-primary:hover {
    background: var(--blue-700);
    border-color: var(--blue-700);
}
```

Secondary:

```text
Background : #FFFFFF
Border     : #CBD5E1
Text       : #334155
```

Danger:

```text
Background : #DC2626
Text       : #FFFFFF
```

---

## 38.11 Forms

```text
Height       : 42px
Background   : #FFFFFF
Border       : #CBD5E1
Radius       : 8px
Focus Border : #2563EB
Focus Ring   : rgba(37, 99, 235, 0.15)
```

```css
.form-control {
    width: 100%;
    min-height: 42px;
    padding: 9px 12px;
    border: 1px solid var(--slate-300);
    border-radius: 8px;
    background: #FFFFFF;
    color: var(--slate-900);
}

.form-control:focus {
    outline: none;
    border-color: var(--blue-600);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
```

Label:

```text
Color  : #334155
Size   : 14px
Weight : 600
```

Error:

```text
Border : #DC2626
Text   : #DC2626
```

---

## 38.12 Cards

```text
Background : #FFFFFF
Border     : #E2E8F0
Radius     : 12px
Padding    : 20–24px
Shadow     : ringan
```

```css
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
}
```

---

## 38.13 Tables

Header:

```text
Background : #F8FAFC
Text       : #475569
Weight     : 600
```

Body:

```text
Background : #FFFFFF
Border     : #E2E8F0
```

Hover:

```text
#F8FAFC
```

---

## 38.14 Status Badges

Pending:

```text
Background : #FFFBEB
Text       : #B45309
```

Approved / Active / Resolved:

```text
Background : #F0FDF4
Text       : #15803D
```

Rejected / Cancelled:

```text
Background : #FEF2F2
Text       : #B91C1C
```

Maintenance / In Progress:

```text
Background : #FFF7ED
Text       : #C2410C
```

Info:

```text
Background : #EFF6FF
Text       : #1D4ED8
```

---

## 38.15 Modal

Digunakan untuk:

- Approve reservasi.
- Reject reservasi.
- Pembatalan reservasi.
- Nonaktifkan fasilitas.
- Perubahan status fasilitas.

Style:

```text
Width      : 480–560px
Background : #FFFFFF
Radius     : 14px
Overlay    : rgba(15, 23, 42, 0.45)
```

---

## 38.16 Spacing System

Gunakan kelipatan 4px:

```text
4px
8px
12px
16px
20px
24px
32px
40px
48px
64px
```

Rekomendasi:

```text
Gap icon dan text : 8–10px
Gap form          : 16px
Gap card          : 24px
Section gap       : 32px
```

---

## 38.17 Border Radius

```text
Input       : 8px
Button      : 8px
Card        : 12px
Modal       : 14px
Large Panel : 16px
Badge       : 999px
```

---

## 38.18 Icon System

Gunakan satu library icon secara konsisten.

Rekomendasi:

```text
Lucide Icons
```

Contoh:

```text
Dashboard : LayoutDashboard
Fasilitas : Building2
Reservasi : CalendarDays
Laporan   : ClipboardList
User      : Users
Logout    : LogOut
Search    : Search
Filter    : SlidersHorizontal
```

---

## 38.19 Vue Components

Gunakan PascalCase:

```text
AppSidebar.vue
AppNavbar.vue
PageHeader.vue
FacilityCard.vue
FacilityFilter.vue
AvailabilityTable.vue
ReservationForm.vue
ReservationTable.vue
ReportForm.vue
StatusBadge.vue
ConfirmModal.vue
EmptyState.vue
Pagination.vue
```

Business rule penting tetap berada di Laravel, bukan hanya di komponen Vue.

---

## 38.20 Accessibility

Minimal:

- Input harus memiliki label.
- Focus state harus terlihat.
- Jangan mengandalkan warna saja untuk menunjukkan status.
- Badge harus memiliki teks.
- Button icon-only harus memiliki `aria-label`.
- Kontras teks harus cukup jelas.
- Modal harus mudah ditutup dan dinavigasi.

---

## 38.21 UI Consistency Rules

1. Gunakan **Figtree** sebagai font utama.
2. Gunakan `#2563EB` sebagai primary.
3. Gunakan Slate sebagai warna netral.
4. Gunakan semantic color yang konsisten.
5. Gunakan spacing kelipatan 4px.
6. Gunakan radius yang konsisten.
7. Gunakan reusable Vue components.
8. Primary button hanya untuk aksi utama.
9. Aksi berbahaya menggunakan warna merah.
10. Validasi Vue hanya membantu UX.
11. Validasi Laravel tetap menjadi sumber validasi utama.
12. Seluruh halaman harus responsif.

---

# 39. Ringkasan Design System

```text
Font              : Figtree

Primary           : #2563EB
Primary Hover     : #1D4ED8
Primary Active    : #1E40AF
Primary Soft      : #EFF6FF

Page Background   : #F8FAFC
Surface           : #FFFFFF
Border            : #E2E8F0

Text Primary      : #0F172A
Text Secondary    : #475569

Success           : #16A34A
Warning           : #D97706
Danger            : #DC2626
Info              : #0284C7

Input Radius      : 8px
Button Radius     : 8px
Card Radius       : 12px
Modal Radius      : 14px

Spacing Base      : 4px
Navbar Height     : 64px
Sidebar Width     : 248px
```

Design system ini harus menjadi acuan saat membuat dan memperbarui seluruh halaman serta komponen Vue.

