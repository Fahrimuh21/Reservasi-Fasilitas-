<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke User (Pemohon)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Facility (Fasilitas yang dipinjam)
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    // Relasi ke Petugas yang menangani (Opsional jika pakai kolom handled_by)
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
