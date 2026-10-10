<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenugasanRelawan extends Model
{
    protected $table = 'penugasan_relawan';

    protected $fillable = [
        'klaim_id',
        'relawan_id',
        'status',
        'catatan',
    ];

    public function klaim()
    {
        return $this->belongsTo(KlaimDonasi::class, 'klaim_id');
    }

    public function relawan()
    {
        return $this->belongsTo(User::class, 'relawan_id');
    }
}