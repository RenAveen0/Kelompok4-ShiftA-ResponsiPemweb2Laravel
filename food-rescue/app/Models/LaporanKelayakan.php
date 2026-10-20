<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    // Di database disimpan path file (atau URL penuh); di respons API selalu berupa URL.
    protected function fotoBukti(): Attribute
    {
        return Attribute::get(fn (?string $nilai) => $nilai && ! str_starts_with($nilai, 'http')
            ? Storage::disk('public')->url($nilai)
            : $nilai);
    }

    public function donasi(): BelongsTo
    {
        return $this->belongsTo(DonasiMakanan::class, 'donasi_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }
}
