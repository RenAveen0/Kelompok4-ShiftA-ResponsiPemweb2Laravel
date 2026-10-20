<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected function casts(): array
    {
        return [
            'batas_simpan_jam' => 'integer',
        ];
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(DonasiMakanan::class, 'kategori_id');
    }
}
