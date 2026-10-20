<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function klaim(): BelongsTo
    {
        return $this->belongsTo(KlaimDonasi::class, 'klaim_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
