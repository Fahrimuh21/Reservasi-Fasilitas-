<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Facility – Model untuk fasilitas kampus.
 *
 * Tabel: facilities
 * Status yang valid: active, inactive, maintenance
 *
 * Relasi:
 * - belongsTo FacilityType
 * - belongsTo Location
 * - hasMany Reservation (milik modul Anggota 3 & 4)
 */
class Facility extends Model
{
    // =========================================================================
    // Konstanta Status
    // =========================================================================

    const STATUS_ACTIVE      = 'active';
    const STATUS_INACTIVE    = 'inactive';
    const STATUS_PENDING     = 'pending';
    const STATUS_MAINTENANCE = 'maintenance';

    // =========================================================================
    // Mass Assignment
    // =========================================================================

    protected $fillable = [
        'code',
        'name',
        'facility_type_id',
        'location_id',
        'capacity',
        'description',
        'status',
    ];

    // =========================================================================
    // Relasi
    // =========================================================================

    /** Tipe fasilitas (Laboratorium, Ruang Kelas, dll). */
    public function facilityType(): BelongsTo
    {
        return $this->belongsTo(FacilityType::class);
    }

    /** Lokasi fasilitas (ruang/gedung/lantai). */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** Daftar reservasi untuk fasilitas ini (milik modul Anggota 3 & 4). */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /** Apakah fasilitas aktif dan bisa direservasi? */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // =========================================================================
    // Query Scopes – digunakan oleh FacilityController untuk search & filter
    // =========================================================================

    /** Scope: hanya fasilitas dengan status 'active'. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Scope: search nama fasilitas (LIKE %keyword%). */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('name', 'like', "%{$keyword}%");
    }

    /** Scope: filter berdasarkan tipe fasilitas. */
    public function scopeFilterByType(Builder $query, int $typeId): Builder
    {
        return $query->where('facility_type_id', $typeId);
    }

    /** Scope: filter berdasarkan lokasi. */
    public function scopeFilterByLocation(Builder $query, int $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    /** Scope: filter berdasarkan kapasitas minimum (capacity >= N). */
    public function scopeFilterByCapacity(Builder $query, int $minCapacity): Builder
    {
        return $query->where('capacity', '>=', $minCapacity);
    }
}
