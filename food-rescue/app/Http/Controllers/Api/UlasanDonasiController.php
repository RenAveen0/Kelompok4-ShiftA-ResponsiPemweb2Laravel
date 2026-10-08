<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UlasanDonasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UlasanDonasiController extends Controller
{
    // Lihat daftar ulasan & rating
    public function index(): JsonResponse
    {
        $ulasan = UlasanDonasi::latest()->get();
        return response()->json(['sukses' => true, 'data' => $ulasan]);
    }

    // Penerima membuat ulasan setelah makanan diterima
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'klaim_id' => ['required', 'integer', 'exists:klaim_donasi,id'],
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $request->user()->id;

        $ulasan = UlasanDonasi::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Ulasan berhasil diberikan.',
            'data'   => $ulasan
        ], 201);
    }

    // Detail ulasan
    public function show(int $id): JsonResponse
    {
        $ulasan = UlasanDonasi::findOrFail($id);
        return response()->json(['sukses' => true, 'data' => $ulasan]);
    }

    // Update ulasan
    public function update(Request $request, int $id): JsonResponse
    {
        $ulasan = UlasanDonasi::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'rating'   => ['sometimes', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string'],
        ]);

        $ulasan->update($validated);

        return response()->json(['sukses' => true, 'pesan' => 'Ulasan berhasil diperbarui.', 'data' => $ulasan]);
    }

    // Hapus ulasan
    public function destroy(int $id): JsonResponse
    {
        UlasanDonasi::findOrFail($id)->delete();
        return response()->json(['sukses' => true, 'pesan' => 'Ulasan berhasil dihapus.']);
    }
}