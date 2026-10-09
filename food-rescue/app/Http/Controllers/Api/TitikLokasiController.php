<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TitikLokasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitikLokasiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => TitikLokasi::where('is_aktif', true)->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:150'],
            'alamat'    => ['required', 'string'],
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
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
            'latitude'  => ['sometimes', 'numeric'],
            'longitude' => ['sometimes', 'numeric'],
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
