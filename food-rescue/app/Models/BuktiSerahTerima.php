<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuktiSerahTerima extends Model
{
    use HasFactory;

    protected $table = 'bukti_serah_terima';

    protected $fillable = [
        'klaim_id',
        'relawan_id',
        'foto_bukti',
        'catatan',
        'waktu_serah_terima',
    ];
}