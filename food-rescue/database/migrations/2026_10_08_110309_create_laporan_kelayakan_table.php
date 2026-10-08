<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_kelayakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klaim_id')->constrained('klaim_donasi')->cascadeOnDelete();
            $table->foreignId('pelapor_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->enum('status', ['baru', 'ditinjau', 'selesai'])->default('baru');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kelayakan');
    }
};