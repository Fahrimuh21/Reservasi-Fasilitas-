<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FacilityType – Model untuk tipe fasilitas.
 *
 * Tabel: facility_types
 * Contoh data: Laboratorium, Ruang Kelas, Aula
 */
class FacilityType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    // =========================================================================
    // Relasi
    // =========================================================================

    /** Satu tipe memiliki banyak fasilitas. */
    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
