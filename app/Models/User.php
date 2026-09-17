<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * Sesuai DESIGN.md Bagian 5 (Database Design - users) + PRD FR-1, FR-5, FR-6.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'user_type',
        'registration_source',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at'       => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // =========================================================================
    // Helpers – status & role checks (dipakai controller & middleware)
    // =========================================================================

    /** Apakah akun boleh login? Hanya status 'active'. */
    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    /** Peran admin sesuai design.md */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Peran officer sesuai design.md */
    public function isOfficer(): bool
    {
        return $this->role === 'officer';
    }

    // =========================================================================
    // Relasi – sesuai DESIGN.md Bagian 6 (Relasi Eloquent)
    // =========================================================================

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /** Admin yang membuat akun ini */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Admin yang melakukan verifikasi */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
