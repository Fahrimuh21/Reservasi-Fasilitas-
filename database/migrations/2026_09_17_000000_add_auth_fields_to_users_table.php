<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Tambahan Modul Auth (Orang 1 - Adam)
 * Menambahkan kolom-kolom pendukung auth ke tabel users yang sudah ada.
 * Sesuai DESIGN.md Bagian 5 (Database Design - users).
 *
 * Sifat: ADDITIVE – tidak mengubah kolom yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Jenis pengguna kampus (mahasiswa/dosen/staf) – hanya relevan saat role = user
            // Sesuai PRD FR-1 & DESIGN.md kolom user_type
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->nullable()->after('password');
                // nilai valid: 'mahasiswa', 'dosen', 'staf' – divalidasi di Form Request
            }

            // Sumber pendaftaran: 'self_register' (form publik) atau 'admin_created' (dibuat admin)
            // Dibutuhkan untuk audit dan membedakan akun pending vs langsung active
            if (!Schema::hasColumn('users', 'registration_source')) {
                $table->string('registration_source')->default('self_register')->after('user_type');
            }

            // Siapa yang membuat akun ini (admin yang membuat akun petugas/user langsung)
            // Nullable – null jika self-register
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('registration_source');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }

            // Siapa yang melakukan verifikasi/approve/reject
            if (!Schema::hasColumn('users', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('created_by');
                $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            }

            // Kapan akun diverifikasi
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['user_type', 'registration_source', 'created_by', 'verified_by', 'verified_at']);
        });
    }
};
