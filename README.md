# Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi web untuk mengelola **reservasi fasilitas kampus** dan **pelaporan kerusakan fasilitas** dalam satu sistem terintegrasi.

Project ini dikembangkan menggunakan **Laravel 12**, **Vue 3**, **Vite**, dan **PostgreSQL**.

---

## Tentang Project

Sistem Reservasi & Pelaporan Fasilitas Kampus dibuat untuk membantu pengelolaan fasilitas seperti:

- Ruang kelas
- Aula
- Laboratorium
- Lapangan
- Peralatan kampus

Sistem memungkinkan pengguna untuk mengecek ketersediaan fasilitas, melakukan reservasi, serta melaporkan kerusakan atau masalah pada fasilitas.

Petugas dapat memproses reservasi dan laporan yang masuk, sedangkan admin dapat mengelola data master, akun pengguna, akun petugas, serta melihat rekap penggunaan fasilitas.

---

## Fitur Utama

### Pengunjung

- Melihat daftar fasilitas tanpa login
- Melihat status ketersediaan fasilitas
- Mencari fasilitas berdasarkan tipe
- Mencari berdasarkan lokasi
- Memfilter berdasarkan kapasitas

### Pengguna

- Registrasi akun
- Login dan logout
- Mengajukan reservasi
- Melihat riwayat reservasi
- Melihat status reservasi
- Membatalkan reservasi milik sendiri
- Membuat laporan kerusakan
- Mengunggah foto kerusakan
- Melihat status laporan

### Petugas

- Melihat dashboard petugas
- Melihat antrian reservasi
- Menyetujui reservasi
- Menolak reservasi
- Membatalkan reservasi yang sudah disetujui
- Memproses laporan kerusakan
- Menambahkan catatan resolusi
- Mengubah status fasilitas
- Menandai fasilitas dalam perbaikan

### Admin

- Mengelola akun pengguna
- Membuat akun petugas
- Memverifikasi akun pengguna
- Menolak registrasi pengguna
- Mengelola fasilitas
- Mengelola tipe fasilitas
- Mengelola lokasi
- Melihat rekap okupansi fasilitas
- Melihat rekap kerusakan
- Export data ke CSV, Excel, atau PDF

---

## Tech Stack

### Backend

- Laravel 12
- PHP 8.x
- Eloquent ORM

### Frontend

- Vue 3
- Vite
- JavaScript
- Bootstrap 5

### Database

- PostgreSQL

### Tools

- Git
- GitHub / GitLab
- Composer
- NPM

---

## Arsitektur Sistem

Project menggunakan pendekatan **MVC** dengan Laravel sebagai backend dan Vue sebagai frontend.

```text
Browser
   │
   ▼
Vue 3
   │
   ▼
Laravel Routes
   │
   ▼
Middleware
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

---

## Struktur Folder

```text
project/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   └── Policies/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── components/
│   │   ├── layouts/
│   │   └── pages/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│
├── tests/
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

## Requirement

Pastikan perangkat sudah memiliki:

- PHP 8.2 atau lebih baru
- Composer
- Node.js
- NPM
- PostgreSQL
- Git

Cek versi:

```bash
php -v
composer -V
node -v
npm -v
psql --version
git --version
```

---

## Instalasi Project

### 1. Clone Repository

```bash
git clone <repository-url>
cd Reservasi-Fasilitas
```

### 2. Install Dependency Laravel

```bash
composer install
```

### 3. Install Dependency Frontend

```bash
npm install
```

### 4. Buat File Environment

Linux / macOS:

```bash
cp .env.example .env
```

Windows:

```bash
copy .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

## Konfigurasi Database PostgreSQL

Buat database baru:

```sql
CREATE DATABASE reservasi_fasilitas;
```

Kemudian sesuaikan `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=reservasi_fasilitas
DB_USERNAME=postgres
DB_PASSWORD=password_anda
```

Jangan memasukkan file `.env` ke repository.

---

## Migration dan Seeder

Jalankan migration:

```bash
php artisan migrate
```

Jika project memiliki seeder:

```bash
php artisan db:seed
```

Atau reset database dan jalankan seluruh seeder:

```bash
php artisan migrate:fresh --seed
```

---

## Storage Link

Untuk menampilkan foto laporan dari storage:

```bash
php artisan storage:link
```

---

## Menjalankan Project

### Terminal 1

Jalankan Laravel:

```bash
php artisan serve
```

### Terminal 2

Jalankan Vite:

```bash
npm run dev
```

Aplikasi biasanya dapat diakses melalui:

```text
http://127.0.0.1:8000
```

---

## Build Production

Untuk membuat frontend production:

```bash
npm run build
```

---

## Aturan Reservasi

Reservasi mengikuti ketentuan berikut:

- Jam operasional pukul **07.00 sampai 20.00**
- Slot reservasi menggunakan interval **30 menit**
- Waktu mulai harus lebih kecil dari waktu selesai
- Reservasi harus berada pada tanggal yang sama
- Fasilitas harus berstatus aktif
- Dua reservasi dengan status `approved` tidak boleh bertabrakan pada fasilitas yang sama

Contoh slot valid:

```text
07.00 - 07.30
07.30 - 08.00
08.00 - 09.30
13.30 - 15.00
```

Contoh slot tidak valid:

```text
06.30 - 07.30
07.15 - 08.00
19.30 - 20.30
```

---

## Role dan Hak Akses

| Fitur | Pengunjung | Pengguna | Petugas | Admin |
|---|:---:|:---:|:---:|:---:|
| Lihat fasilitas | ✅ | ✅ | ✅ | ✅ |
| Cari fasilitas | ✅ | ✅ | ✅ | ✅ |
| Lihat availability | ✅ | ✅ | ✅ | ✅ |
| Registrasi | ✅ | ✅ | ❌ | ❌ |
| Login | ❌ | ✅ | ✅ | ✅ |
| Ajukan reservasi | ❌ | ✅ | ❌ | ❌ |
| Riwayat reservasi | ❌ | ✅ | ❌ | ❌ |
| Buat laporan | ❌ | ✅ | ❌ | ❌ |
| Approve/reject reservasi | ❌ | ❌ | ✅ | ❌ |
| Proses laporan | ❌ | ❌ | ✅ | ❌ |
| CRUD fasilitas | ❌ | ❌ | ❌ | ✅ |
| Kelola akun | ❌ | ❌ | ❌ | ✅ |
| Rekap dan export | ❌ | ❌ | ❌ | ✅ |

---

## Database Utama

Tabel utama:

```text
users
facility_types
locations
facilities
reservations
reports
report_photos
facility_status_logs
```

Relasi utama:

```text
users 1:N reservations
users 1:N reports

facilities 1:N reservations
facilities 1:N reports

facility_types 1:N facilities
locations 1:N facilities

reports 1:N report_photos

facilities 1:N facility_status_logs
```

---

## Status Data

### Status Akun

```text
pending
active
rejected
suspended
```

### Status Fasilitas

```text
active
maintenance
inactive
```

### Status Reservasi

```text
pending
approved
rejected
cancelled
```

### Status Laporan

```text
new
in_progress
resolved
rejected
```

---

## Pembagian Tugas Tim

| Anggota | Modul | Tanggung Jawab |
|---|---|---|
| Anggota 1 | Authentication & User Management | Register, login, logout, verifikasi, kelola akun |
| Anggota 2 | Facility Management | Daftar, detail, search, filter, availability, CRUD fasilitas |
| Anggota 3 | User Reservation | Pengajuan, validasi waktu, riwayat, detail, pembatalan |
| Anggota 4 | Officer Reservation | Dashboard, antrian, approve, reject, conflict checking |
| Anggota 5 | Report & Recap | Laporan, foto, maintenance, rekap, export |

---

## Git Workflow

Branch utama:

```text
main
develop
```

Branch fitur:

```text
feature/auth
feature/facility
feature/user-reservation
feature/officer-reservation
feature/reporting
```

Alur:

```text
feature/*
    ↓
develop
    ↓
testing
    ↓
main
```

---

## Format Commit

Gunakan format:

```text
<type>(scope): deskripsi
```

Contoh:

```text
feat(auth): tambah fitur login
feat(facility): tambah pencarian fasilitas
feat(reservation): tambah form reservasi
fix(reservation): perbaiki validasi bentrok jadwal
feat(report): tambah upload foto laporan
docs(design): tambahkan dokumentasi desain sistem
docs(readme): perbarui dokumentasi project
```

Panduan lengkap tersedia pada:

```text
COMMIT.md
```

---

## Dokumentasi

Dokumentasi teknis project tersedia pada:

```text
DESIGN.md
```

Dokumen tersebut berisi:

- Arsitektur sistem
- Struktur folder
- Rancangan database
- Relasi Eloquent
- Routing
- Middleware
- Validasi
- Business logic
- Security
- Testing
- Pembagian modul

---

## Testing

Jalankan test Laravel:

```bash
php artisan test
```

Beberapa skenario yang perlu diuji:

- Registrasi pengguna
- Login dan logout
- Validasi role
- Pengajuan reservasi
- Validasi slot 30 menit
- Validasi jam operasional
- Pencegahan reservasi bentrok
- Pembatalan reservasi
- Upload laporan kerusakan
- Perubahan status laporan
- Perubahan status fasilitas
- Authorization admin dan petugas

---

## Troubleshooting

### Database Connection Error

Pastikan PostgreSQL aktif dan konfigurasi `.env` benar.

Kemudian jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

### Perubahan `.env` Tidak Terbaca

```bash
php artisan config:clear
```

### Frontend Tidak Berubah

```bash
npm run dev
```

Jika masih bermasalah:

```bash
rm -rf node_modules
npm install
npm run dev
```

Pada Windows, hapus folder `node_modules` secara manual jika perintah `rm` tidak tersedia.

### Foto Tidak Tampil

Pastikan sudah menjalankan:

```bash
php artisan storage:link
```

---

## Catatan Keamanan

- Jangan commit file `.env`
- Jangan menyimpan password dalam plaintext
- Gunakan Laravel validation
- Gunakan middleware dan policy untuk authorization
- Validasi file upload
- Gunakan CSRF protection
- Validasi aturan reservasi di backend
- Jangan mengandalkan validasi Vue saja

---

## Tim Pengembang

Project dikembangkan oleh tim beranggotakan lima mahasiswa untuk memenuhi tugas **Pengembangan Platform Khusus 2026 Sebelum UTS**.

Tambahkan identitas anggota tim pada bagian berikut:

| No | Nama | NIM | Modul |
|---:|---|---|---|
| 1 | Nama Anggota 1 | NIM | Authentication & User |
| 2 | Nama Anggota 2 | NIM | Facility |
| 3 | Nama Anggota 3 | NIM | User Reservation |
| 4 | Nawaal Hanif Mumtaz Arriye | 24060124120041 | Officer Reservation |
| 5 | Nama Anggota 5 | NIM | Report & Recap |

---

## License

Project ini dibuat untuk keperluan akademik.

Framework Laravel menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
