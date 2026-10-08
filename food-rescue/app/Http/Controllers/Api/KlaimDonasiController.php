<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonasiMakanan;
use App\Models\KlaimDonasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KlaimDonasiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'donasi_id'    => ['required', 'exists:donasi_makanan,id'],
            'jumlah_porsi' => ['required', 'integer', 'min:1'],
        ]);

        $klaim = DB::transaction(function () use ($request) {
            // Mengunci baris data donasi agar tidak dibaca/diubah proses lain secara bersamaan
            $donasi = DonasiMakanan::lockForUpdate()->findOrFail($request->donasi_id);

            if ($donasi->status !== 'tersedia') {
                abort(422, 'Donasi sudah tidak tersedia.');
            }

            if ($donasi->batas_waktu && $donasi->batas_waktu->isPast()) {
                abort(422, 'Masa berlaku donasi telah kadaluwarsa.');
            }

            if ($request->jumlah_porsi > $donasi->porsi_tersisa) {
                abort(422, 'Jumlah porsi yang diminta melebihi kuota tersisa.');
            }

            $donasi->decrement('porsi_tersisa', $request->jumlah_porsi);

            if ($donasi->fresh()->porsi_tersisa === 0) {
                $donasi->update(['status' => 'habis']);
            }

            return KlaimDonasi::create([
                'donasi_id'    => $donasi->id,
                'penerima_id'  => $request->user()->id,
                'jumlah_porsi' => $request->jumlah_porsi,
                'status'       => 'diajukan',
            ]);
        });

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Klaim donasi berhasil diajukan.',
            'data'   => $klaim->load('donasi')
        ], 201);
    }
}