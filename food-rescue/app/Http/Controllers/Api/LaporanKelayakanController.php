<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanKelayakan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanKelayakanController extends Controller
{
    // Admin: Lihat daftar seluruh laporan kelayakan makanan
    public function index(): JsonResponse
    {
        $laporan = LaporanKelayakan::latest()->get();
        return response()->json(['sukses' => true, 'data' => $laporan]);
    }

    // User/Penerima membuat laporan jika makanan basi / tidak layak
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'donasi_id'  => ['required', 'integer', 'exists:donasi_makanan,id'],
            'alasan'     => ['required', 'string'],
            'foto_bukti' => ['nullable', 'string'],
        ]);

        $validated['pelapor_id'] = $request->user()->id;
        $validated['status'] = 'menunggu_tinjauan';

        $laporan = LaporanKelayakan::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Laporan kelayakan berhasil dikirim.',
            'data'   => $laporan
        ], 201);
    }

    // Detail laporan
    public function show(int $id): JsonResponse
    {
        $laporan = LaporanKelayakan::findOrFail($id);
        return response()->json(['sukses' => true, 'data' => $laporan]);
    }

    // Admin merespons / mengubah status laporan
    public function update(Request $request, int $id): JsonResponse
    {
        $laporan = LaporanKelayakan::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:menunggu_tinjauan,diproses,ditindaklanjuti,ditolak'],
        ]);

        $laporan->update($validated);

        return response()->json(['sukses' => true, 'pesan' => 'Status laporan berhasil diperbarui.', 'data' => $laporan]);
    }

    // Hapus laporan
    public function destroy(int $id): JsonResponse
    {
        LaporanKelayakan::findOrFail($id)->delete();
        return response()->json(['sukses' => true, 'pesan' => 'Laporan kelayakan berhasil dihapus.']);
    }
}