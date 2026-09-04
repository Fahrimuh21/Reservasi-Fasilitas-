COMMIT GUIDE

Project PPK 2026 – Sistem Reservasi & Pelaporan Fasilitas Kampus

Dokumen ini digunakan sebagai panduan commit untuk seluruh anggota tim agar riwayat pengembangan project rapi, jelas, dan mudah ditelusuri.

1. Aturan Umum Commit

Setiap anggota wajib:

Menggunakan akun GitHub/GitLab masing-masing.

Melakukan commit menggunakan identitas masing-masing.

Membuat commit sesuai fitur yang benar-benar dikerjakan.

Menggunakan pesan commit yang singkat, jelas, dan spesifik.

Menghindari commit dengan pesan seperti update, fix, revisi, atau coba.

Melakukan pull sebelum mulai bekerja.

Memastikan fitur sudah diuji sebelum di-merge ke branch utama.

Tidak melakukan commit file sensitif seperti .env, password, token, atau kredensial database.

2. Format Pesan Commit

Gunakan format:

<type>(scope): deskripsi singkat

Contoh:

feat(auth): tambah fitur login pengguna
fix(reservation): perbaiki validasi bentrok jadwal
style(facility): rapikan tampilan daftar fasilitas
docs(project): tambah dokumentasi instalasi

3. Jenis Commit

Type

Fungsi

Contoh

feat

Menambah fitur baru

feat(auth): tambah registrasi pengguna

fix

Memperbaiki bug

fix(report): perbaiki upload foto laporan

style

Perubahan tampilan tanpa mengubah logika

style(admin): rapikan layout dashboard

refactor

Merapikan kode tanpa menambah fitur

refactor(reservation): pisahkan validasi jadwal

docs

Dokumentasi

docs(readme): tambah panduan instalasi

test

Testing

test(auth): tambah pengujian login

chore

Konfigurasi atau pekerjaan pendukung

chore(config): tambah konfigurasi database

db

Perubahan database

db(reservation): tambah constraint bentrok jadwal

4. Pembagian Scope per Anggota

Anggota 1 – Authentication & User Management

Scope utama:

auth
user
verification

Contoh commit:

feat(auth): buat halaman registrasi pengguna
feat(auth): implementasi login dan session
feat(auth): tambah fitur logout
feat(user): buat halaman kelola akun
feat(user): tambah akun petugas melalui admin
feat(verification): tambah verifikasi akun pengguna
fix(auth): perbaiki validasi email duplikat
style(auth): rapikan tampilan halaman login

Anggota 2 – Facility Management

Scope utama:

facility
location
facility-type
availability

Contoh commit:

feat(facility): buat halaman daftar fasilitas
feat(facility): tambah detail fasilitas
feat(facility): tambah pencarian fasilitas
feat(facility): tambah filter tipe lokasi dan kapasitas
feat(availability): tampilkan slot ketersediaan fasilitas
feat(facility): tambah fitur CRUD fasilitas admin
feat(location): tambah master lokasi fasilitas
feat(facility-type): tambah master tipe fasilitas
fix(availability): perbaiki status slot yang sudah terisi
style(facility): rapikan kartu fasilitas

Anggota 3 – User Reservation

Scope utama:

reservation
schedule
history

Contoh commit:

feat(reservation): buat form pengajuan reservasi
feat(schedule): tambah validasi jam operasional
feat(schedule): tambah validasi slot 30 menit
feat(reservation): simpan pengajuan reservasi
feat(history): buat halaman riwayat reservasi pengguna
feat(reservation): buat halaman detail reservasi
feat(reservation): tambah pembatalan reservasi sendiri
fix(schedule): perbaiki validasi jam selesai
fix(reservation): cegah pengajuan dengan data kosong
style(reservation): rapikan tampilan form reservasi

Anggota 4 – Officer Reservation Management

Scope utama:

officer
approval
reservation
dashboard

Contoh commit:

feat(dashboard): buat dashboard petugas
feat(officer): tampilkan antrian reservasi pending
feat(approval): tambah fitur approve reservasi
feat(approval): tambah pengecekan bentrok jadwal
feat(approval): tambah fitur reject reservasi
feat(reservation): tambah pembatalan reservasi approved
feat(reservation): tambah alasan pembatalan oleh petugas
fix(approval): cegah approve fasilitas maintenance
fix(approval): perbaiki pengecekan overlapping jadwal
style(dashboard): rapikan tabel antrian reservasi

Anggota 5 – Damage Report, Maintenance & Recap

Scope utama:

report
maintenance
recap
export

Contoh commit:

feat(report): buat form laporan kerusakan
feat(report): tambah upload foto laporan
feat(report): buat halaman status laporan pengguna
feat(report): buat antrian laporan petugas
feat(report): tambah perubahan status laporan
feat(report): tambah catatan resolusi laporan
feat(maintenance): tambah status fasilitas dalam perbaikan
feat(maintenance): tambah riwayat perubahan status fasilitas
feat(recap): buat rekap okupansi fasilitas
feat(recap): buat rekap frekuensi kerusakan
feat(export): tambah export CSV
feat(export): tambah export Excel
feat(export): tambah export PDF
fix(report): perbaiki validasi file foto
style(recap): rapikan halaman rekap admin

5. Branching Strategy

Gunakan branch utama:

main
develop

Setiap anggota membuat branch fitur masing-masing.

Contoh:

feature/auth
feature/facility
feature/user-reservation
feature/officer-reservation
feature/reporting

Struktur sederhana:

main
└── develop
    ├── feature/auth
    ├── feature/facility
    ├── feature/user-reservation
    ├── feature/officer-reservation
    └── feature/reporting

6. Alur Kerja Git

Sebelum mulai mengerjakan fitur

git checkout develop
git pull origin develop

Kemudian masuk ke branch masing-masing:

git checkout feature/auth

atau:

git checkout feature/facility

Setelah selesai mengerjakan

Cek perubahan:

git status

Tambahkan file:

git add .

Commit:

git commit -m "feat(auth): tambah fitur login pengguna"

Push:

git push origin feature/auth

Setelah itu buat Pull Request atau Merge Request ke:

develop

7. Contoh Commit yang Baik

feat(auth): tambah registrasi pengguna
feat(facility): tambah filter berdasarkan lokasi
feat(reservation): tambah pengajuan reservasi
fix(schedule): perbaiki validasi slot 30 menit
feat(approval): tambah pengecekan bentrok reservasi
feat(report): tambah upload foto kerusakan
feat(recap): tambah rekap okupansi fasilitas
docs(project): tambah panduan instalasi aplikasi

8. Contoh Commit yang Tidak Disarankan

Hindari:

update
revisi
fix
coba
tes
final
final banget
update terbaru
perbaikan
punya saya

Ganti dengan pesan yang menjelaskan perubahan.

Contoh:

fix(auth): perbaiki validasi password login

bukan:

fix login

9. Commit Database

Perubahan database juga harus memiliki commit tersendiri.

Contoh:

db(user): buat tabel users
db(facility): buat tabel facilities dan locations
db(reservation): buat tabel reservations
db(report): buat tabel reports dan report_photos
db(facility): tambah facility_status_logs
db(reservation): tambah constraint overlapping jadwal

File SQL sebaiknya disimpan di:

/database/schema.sql
/database/seed.sql

atau:

/sql/database.sql

10. Commit UI

Untuk pekerjaan tampilan:

style(auth): buat tampilan halaman login
style(facility): buat card daftar fasilitas
style(reservation): rapikan halaman riwayat reservasi
style(dashboard): rapikan dashboard petugas
style(admin): rapikan dashboard admin

Jika perubahan UI juga menambah fungsi baru, gunakan feat, bukan style.

11. Contoh Riwayat Commit per Anggota

Anggota 1

feat(auth): buat halaman registrasi
feat(auth): implementasi login pengguna
feat(auth): tambah logout
feat(verification): tambah verifikasi akun
feat(user): tambah manajemen petugas
fix(auth): perbaiki validasi registrasi

Anggota 2

feat(facility): buat daftar fasilitas
feat(facility): tambah detail fasilitas
feat(facility): tambah filter pencarian
feat(availability): tambah tampilan slot tersedia
feat(facility): tambah CRUD fasilitas
fix(availability): perbaiki status ketersediaan

Anggota 3

feat(reservation): buat form reservasi
feat(schedule): tambah validasi jam operasional
feat(schedule): tambah validasi slot 30 menit
feat(history): tambah riwayat reservasi
feat(reservation): tambah pembatalan reservasi
fix(reservation): perbaiki validasi tanggal

Anggota 4

feat(dashboard): buat dashboard petugas
feat(approval): tambah approve reservasi
feat(approval): tambah reject reservasi
feat(approval): tambah validasi bentrok
feat(reservation): tambah emergency cancellation
fix(approval): perbaiki pengecekan jadwal

Anggota 5

feat(report): buat laporan kerusakan
feat(report): tambah upload foto
feat(report): tambah proses laporan
feat(maintenance): tambah status perbaikan fasilitas
feat(recap): tambah rekap fasilitas
feat(export): tambah export laporan

12. Target Minimal Commit

Disarankan setiap anggota memiliki minimal:

8–12 commit bermakna

Commit tidak perlu dibuat berlebihan hanya untuk mengejar jumlah. Satu commit harus mewakili satu perubahan yang jelas dan dapat diuji.

Contoh pembagian:

Anggota

Target Commit

Anggota 1

8–12

Anggota 2

8–12

Anggota 3

8–12

Anggota 4

8–12

Anggota 5

8–12

Total repository dapat memiliki sekitar:

40–60 commit

selama setiap commit memang merepresentasikan perkembangan project yang nyata.

13. Checklist Sebelum Commit

Pastikan:

Fitur dapat dijalankan.

Tidak ada error utama.

Tidak ada password atau .env yang ikut ter-upload.

Nama file dan folder sudah sesuai struktur project.

Kode sudah cukup rapi.

Pesan commit menjelaskan perubahan.

Sudah melakukan git pull dari develop.

Tidak menghapus kode anggota lain.

Sudah melakukan testing dasar.

14. Checklist Sebelum Merge

Branch fitur sudah di-push.

Tidak ada conflict yang belum selesai.

Fitur sudah diuji.

Database migration/schema sudah sinkron.

Tidak ada file sensitif.

Pull Request/Merge Request memiliki deskripsi.

Minimal satu anggota lain melakukan review jika memungkinkan.

15. Format Pull Request

Gunakan format berikut:

## Fitur
Nama fitur yang ditambahkan.

## Perubahan
- Perubahan pertama
- Perubahan kedua
- Perubahan ketiga

## Testing
Jelaskan cara fitur diuji.

## Screenshot
Tambahkan screenshot jika perubahan berkaitan dengan UI.

## Catatan
Tambahkan informasi tambahan jika diperlukan.

16. Contoh Pull Request

## Fitur
Reservasi fasilitas oleh pengguna.

## Perubahan
- Menambahkan form reservasi.
- Menambahkan validasi jam operasional 07.00–20.00.
- Menambahkan validasi slot waktu 30 menit.
- Menambahkan penyimpanan reservasi berstatus pending.

## Testing
1. Login sebagai pengguna.
2. Pilih fasilitas.
3. Isi tanggal dan waktu.
4. Submit reservasi.
5. Pastikan data masuk ke database.

## Screenshot
Screenshot form dan hasil reservasi.

## Catatan
Reservasi baru memiliki status pending sebelum diproses petugas.

17. Struktur Commit Project yang Disarankan

Initial Project
│
├── setup project structure
├── setup database connection
│
├── Authentication
│   ├── register
│   ├── login
│   ├── logout
│   └── verification
│
├── Facility
│   ├── list
│   ├── search
│   ├── availability
│   └── CRUD
│
├── Reservation User
│   ├── create
│   ├── validation
│   ├── history
│   └── cancellation
│
├── Reservation Officer
│   ├── queue
│   ├── approval
│   ├── rejection
│   └── conflict validation
│
├── Reporting
│   ├── create report
│   ├── photo upload
│   ├── processing
│   └── maintenance
│
└── Admin Report
    ├── recap
    └── export

Penutup

Setiap anggota bertanggung jawab terhadap modul masing-masing, tetapi integrasi akhir tetap dilakukan bersama. Gunakan commit kecil dan terarah agar kontribusi setiap anggota mudah dilihat pada repository dan proses debugging lebih mudah dilakukan.