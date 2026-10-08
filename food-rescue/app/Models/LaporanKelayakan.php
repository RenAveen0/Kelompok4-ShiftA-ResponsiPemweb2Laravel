<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKelayakan extends Model
{
    use HasFactory;

    protected $table = 'laporan_kelayakan';

    protected $fillable = [
        'donasi_id',
        'pelapor_id',
        'alasan',
        'foto_bukti',
        'status',
    ];
}