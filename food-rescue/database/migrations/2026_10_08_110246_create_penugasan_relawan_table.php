<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penugasan_relawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klaim_id')->unique()->constrained('klaim_donasi')->cascadeOnDelete();
            $table->foreignId('relawan_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['ditugaskan', 'menuju_lokasi', 'diambil', 'diantar', 'selesai'])->default('ditugaskan');
            $table->timestamp('waktu_ambil')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_relawan');
    }
};