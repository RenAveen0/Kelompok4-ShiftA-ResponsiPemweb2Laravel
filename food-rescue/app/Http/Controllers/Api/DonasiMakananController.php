<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonasiMakanan;
use App\Events\DonasiBaruDibuat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonasiMakananController extends Controller
{
    public function index(): JsonResponse
    {
        $donasi = DonasiMakanan::with(['user', 'kategori', 'titikLokasi'])
            ->where('status', 'tersedia')
            ->latest()
            ->paginate(10);

        return response()->json([
            'sukses' => true,
            'data'   => $donasi
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_id'     => ['required', 'exists:kategori_makanan,id'],
            'titik_lokasi_id' => ['nullable', 'exists:titik_lokasi,id'],
            'judul'           => ['required', 'string', 'max:150'],
            'deskripsi'       => ['nullable', 'string'],
            'porsi'           => ['required', 'integer', 'min:1'],
            'lokasi'          => ['required', 'string'],
            'batas_waktu'     => ['nullable', 'date'],
        ]);

        $donasi = DonasiMakanan::create([
            'user_id'         => $request->user()->id,
            'kategori_id'     => $validated['kategori_id'],
            'titik_lokasi_id' => $validated['titik_lokasi_id'] ?? null,
            'judul'           => $validated['judul'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'porsi'           => $validated['porsi'],
            'porsi_tersisa'   => $validated['porsi'],
            'lokasi'          => $validated['lokasi'],
            'batas_waktu'     => $validated['batas_waktu'] ?? null,
            'status'          => 'tersedia',
        ]);

        // Memicu Event Realtime Reverb
        DonasiBaruDibuat::dispatch($donasi);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Donasi makanan berhasil dibuat.',
            'data'   => $donasi
        ], 201);
    }
}