<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DonasiMakanan;
use App\Models\KlaimDonasi;
use App\Models\PenugasanRelawan;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'no_telepon', 'alamat', 'latitude', 'longitude'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(DonasiMakanan::class, 'user_id');
    }

    public function klaim(): HasMany
    {
        return $this->hasMany(KlaimDonasi::class, 'penerima_id');
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(PenugasanRelawan::class, 'relawan_id');
    }
}