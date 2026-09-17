<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reservation – Model sementara untuk Modul Anggota 3
 */
class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'facility_id',
        'start_at',
        'end_at',
        'status',
        'purpose',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
