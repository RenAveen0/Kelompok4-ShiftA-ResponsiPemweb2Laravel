<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UlasanDonasi extends Model
{
    use HasFactory;

    protected $table = 'ulasan_donasi';

    protected $fillable = [
        'klaim_id',
        'user_id',
        'rating',
        'komentar',
    ];
}