<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TitikLokasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitikLokasiController extends Controller
{
    // Pengguna biasa hanya melihat titik aktif. Admin dapat menambahkan ?semua=1
    // agar titik nonaktif juga tampil (dan bisa diaktifkan kembali).
    public function index(Request $request): JsonResponse
    {
        $semua = $request->boolean('semua') && $request->user()->role === 'admin';

        $titik = TitikLokasi::query()
            ->when(! $semua, fn ($q) => $q->where('is_aktif', true))
            ->orderBy('nama')
            ->get();

        return response()->json(['sukses' => true, 'data' => $titik]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:150'],
            'alamat'    => ['required', 'string'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_aktif'  => ['boolean'],
        ]);

        $lokasi = TitikLokasi::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Titik lokasi berhasil ditambahkan.',
            'data'   => $lokasi
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => TitikLokasi::findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $lokasi = TitikLokasi::findOrFail($id);

        $validated = $request->validate([
            'nama'      => ['sometimes', 'string', 'max:150'],
            'alamat'    => ['sometimes', 'string'],
            'latitude'  => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'is_aktif'  => ['boolean'],
        ]);

        $lokasi->update($validated);

        return response()->json(['sukses' => true, 'pesan' => 'Titik lokasi berhasil diperbarui.', 'data' => $lokasi]);
    }

    public function destroy(int $id): JsonResponse
    {
        TitikLokasi::findOrFail($id)->delete();
        return response()->json(['sukses' => true, 'pesan' => 'Titik lokasi berhasil dihapus.']);
    }
}
