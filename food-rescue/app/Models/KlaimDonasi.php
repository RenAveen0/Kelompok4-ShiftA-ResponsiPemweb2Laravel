<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\DonasiMakanan;
use App\Models\User;
use App\Models\PenugasanRelawan;
use App\Models\UlasanDonasi;

class KlaimDonasi extends Model
{
    protected $table = 'klaim_donasi';

    protected $fillable = ['donasi_id', 'penerima_id', 'relawan_id', 'jumlah_porsi', 'status'];

    public function donasi(): BelongsTo
    {
        return $this->belongsTo(DonasiMakanan::class, 'donasi_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function relawan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relawan_id');
    }

    public function penugasan(): HasOne
    {
        return $this->hasOne(PenugasanRelawan::class, 'klaim_id');
    }

    public function ulasan(): HasOne
    {
        return $this->hasOne(UlasanDonasi::class, 'klaim_id');
    }
}