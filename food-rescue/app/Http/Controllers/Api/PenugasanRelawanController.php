<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenugasanRelawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenugasanRelawanController extends Controller
{
    public function index(): JsonResponse
    {
        $penugasan = PenugasanRelawan::with(['klaim', 'relawan'])->latest()->get();

        return response()->json([
            'sukses' => true,
            'data'   => $penugasan
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'klaim_id' => ['required', 'exists:klaim_donasi,id'],
            'catatan'  => ['nullable', 'string'],
        ]);

        $validated['relawan_id'] = $request->user()->id;
        $validated['status'] = 'ditugaskan';

        $penugasan = PenugasanRelawan::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Tugas pengantaran berhasil diambil oleh relawan.',
            'data'   => $penugasan
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $penugasan = PenugasanRelawan::with(['klaim', 'relawan'])->find($id);

        if (!$penugasan) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan relawan tidak ditemukan.'], 404);
        }

        return response()->json(['sukses' => true, 'data' => $penugasan]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $penugasan = PenugasanRelawan::find($id);

        if (!$penugasan) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan relawan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'status'  => ['required', 'in:ditugaskan,menuju_lokasi,diambil,diantar,selesai'],
            'catatan' => ['nullable', 'string'],
        ]);

        $penugasan->update($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Status penugasan relawan berhasil diperbarui.',
            'data'   => $penugasan
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $penugasan = PenugasanRelawan::find($id);

        if (!$penugasan) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan relawan tidak ditemukan.'], 404);
        }

        $penugasan->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Penugasan relawan berhasil dibatalkan.']);
    }
}