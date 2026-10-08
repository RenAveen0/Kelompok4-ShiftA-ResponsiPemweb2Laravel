<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('klaim_donasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donasi_id')->constrained('donasi_makanan')->cascadeOnDelete();
            $table->foreignId('penerima_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('relawan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('jumlah_porsi');
            $table->enum('status', [
                'diajukan', 'disetujui', 'ditolak', 'diambil_relawan', 'diantar', 'selesai', 'dibatalkan'
            ])->default('diajukan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klaim_donasi');
    }
};