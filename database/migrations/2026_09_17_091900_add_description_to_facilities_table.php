<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom 'description' ke tabel facilities.
 *
 * Kolom ini dibutuhkan oleh FR-4 (Detail Fasilitas) untuk menampilkan
 * deskripsi lengkap fasilitas kepada pengunjung.
 *
 * Migration terpisah karena tabel facilities sudah dibuat sebelumnya
 * tanpa kolom ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->text('description')->nullable()->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
