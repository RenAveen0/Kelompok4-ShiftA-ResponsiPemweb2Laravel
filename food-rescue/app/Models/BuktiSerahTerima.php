<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BuktiSerahTerima extends Model
{
    use HasFactory;

    protected $table = 'bukti_serah_terima';

    protected $fillable = [
        'klaim_id',
        'relawan_id',
        'foto_bukti',
        'catatan',
        'dikonfirmasi_at',
        'waktu_serah_terima',
    ];

    protected function casts(): array
    {
        return [
            'dikonfirmasi_at'    => 'datetime',
            'waktu_serah_terima' => 'datetime',
        ];
    }

    // Di database disimpan path file (atau URL penuh); di respons API selalu berupa URL.
    protected function fotoBukti(): Attribute
    {
        return Attribute::get(fn (?string $nilai) => $nilai && ! str_starts_with($nilai, 'http')
            ? Storage::disk('public')->url($nilai)
            : $nilai);
    }

    public function klaim(): BelongsTo
    {
        return $this->belongsTo(KlaimDonasi::class, 'klaim_id');
    }

    public function relawan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relawan_id');
    }
}
