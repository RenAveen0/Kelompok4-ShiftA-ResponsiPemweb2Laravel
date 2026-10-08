<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuktiSerahTerima;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuktiSerahTerimaController extends Controller
{
    // Lihat seluruh daftar penyerahan
    public function index(): JsonResponse
    {
        $bukti = BuktiSerahTerima::latest()->get();
        return response()->json(['sukses' => true, 'data' => $bukti]);
    }

    // Relawan/Admin unggah bukti penyerahan makanan
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'klaim_id'           => ['required', 'integer', 'exists:klaim_donasi,id'],
            'foto_bukti'         => ['required', 'string'],
            'catatan'            => ['nullable', 'string'],
            'waktu_serah_terima' => ['nullable', 'date'],
        ]);

        $validated['relawan_id'] = $request->user()->id;
        $validated['waktu_serah_terima'] = $validated['waktu_serah_terima'] ?? now();

        $bukti = BuktiSerahTerima::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Bukti serah terima berhasil diunggah.',
            'data'   => $bukti
        ], 201);
    }

    // Detail bukti serah terima
    public function show(int $id): JsonResponse
    {
        $bukti = BuktiSerahTerima::findOrFail($id);
        return response()->json(['sukses' => true, 'data' => $bukti]);
    }

    // Hapus bukti serah terima
    public function destroy(int $id): JsonResponse
    {
        BuktiSerahTerima::findOrFail($id)->delete();
        return response()->json(['sukses' => true, 'pesan' => 'Bukti serah terima berhasil dihapus.']);
    }
}