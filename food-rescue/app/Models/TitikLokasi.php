<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TitikLokasi extends Model
{
    use HasFactory;

    protected $table = 'titik_lokasi';

    protected $fillable = [
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
            'is_aktif'  => 'boolean',
        ];
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(DonasiMakanan::class, 'titik_lokasi_id');
    }
}
