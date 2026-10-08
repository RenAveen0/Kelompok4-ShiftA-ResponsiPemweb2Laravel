<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\KategoriMakanan;
use App\Models\TitikLokasi;
use App\Models\KlaimDonasi;
class DonasiMakanan extends Model
{
    protected $table = 'donasi_makanan';

    protected $fillable = [
        'user_id', 'kategori_id', 'titik_lokasi_id', 'judul', 'deskripsi',
        'porsi', 'porsi_tersisa', 'lokasi', 'latitude', 'longitude', 'foto',
        'batas_waktu', 'status'
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu' => 'datetime',
            'porsi' => 'integer',
            'porsi_tersisa' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMakanan::class, 'kategori_id');
    }

    public function titikLokasi(): BelongsTo
    {
        return $this->belongsTo(TitikLokasi::class, 'titik_lokasi_id');
    }

    public function klaim(): HasMany
    {
        return $this->hasMany(KlaimDonasi::class, 'donasi_id');
    }
}