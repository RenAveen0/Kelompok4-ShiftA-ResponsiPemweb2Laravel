<?php

namespace App\Events;

use App\Models\DonasiMakanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonasiBaruDibuat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DonasiMakanan $donasi) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('donasi-baru')
        ];
    }

    public function broadcastAs(): string
    {
        return 'donasi.dibuat';
    }
}