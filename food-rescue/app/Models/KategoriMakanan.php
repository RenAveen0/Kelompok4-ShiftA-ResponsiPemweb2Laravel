<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriMakanan extends Model
{
    use HasFactory;

    protected $table = 'kategori_makanan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'panduan_simpan',
        'batas_simpan_jam',
    ];
}