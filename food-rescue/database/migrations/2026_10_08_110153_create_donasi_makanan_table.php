<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donasi_makanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_makanan')->restrictOnDelete();
            $table->foreignId('titik_lokasi_id')->nullable()->constrained('titik_lokasi')->nullOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('porsi');
            $table->unsignedInteger('porsi_tersisa')->default(0);
            $table->string('lokasi');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('foto')->nullable();
            $table->dateTime('batas_waktu')->nullable();
            $table->enum('status', ['tersedia', 'habis', 'selesai', 'dibatalkan'])->default('tersedia');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donasi_makanan');
    }
};