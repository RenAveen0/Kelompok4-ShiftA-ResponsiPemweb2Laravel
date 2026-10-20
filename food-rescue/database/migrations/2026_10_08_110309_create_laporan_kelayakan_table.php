<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_kelayakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donasi_id')->constrained('donasi_makanan')->cascadeOnDelete();
            $table->foreignId('pelapor_id')->constrained('users')->cascadeOnDelete();
            $table->text('alasan');
            $table->string('foto_bukti')->nullable();
            $table->enum('status', ['menunggu_tinjauan', 'diproses', 'ditindaklanjuti', 'ditolak'])->default('menunggu_tinjauan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kelayakan');
    }
};