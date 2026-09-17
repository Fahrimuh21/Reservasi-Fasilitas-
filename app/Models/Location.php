<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Location – Model untuk lokasi fasilitas.
 *
 * Tabel: locations
 * Kolom 'name' = nama ruang/area spesifik (contoh: "Lab Komputer Lt. 1")
 * Kolom 'building' = nama gedung (contoh: "Gedung A")
 * Kolom 'floor' = lantai (contoh: "1", "2", "Basement")
 */
class Location extends Model
{
    protected $fillable = [
        'name',
        'building',
        'floor',
    ];

    // =========================================================================
    // Relasi
    // =========================================================================

    /** Satu lokasi memiliki banyak fasilitas. */
    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
