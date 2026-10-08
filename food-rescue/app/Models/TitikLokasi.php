<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}