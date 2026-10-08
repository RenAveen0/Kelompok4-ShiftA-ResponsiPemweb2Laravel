<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bukti_serah_terima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klaim_id')->constrained('klaim_donasi')->cascadeOnDelete();
            $table->foreignId('relawan_id')->constrained('users')->cascadeOnDelete();
            $table->string('foto_bukti');
            $table->text('catatan')->nullable();
            $table->timestamp('dikonfirmasi_at')->nullable();
            $table->timestamp('waktu_serah_terima')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_serah_terima');
    }
};