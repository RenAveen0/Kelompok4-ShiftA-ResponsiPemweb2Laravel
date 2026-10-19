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
    // Status klaim yang masih "memegang" porsi donasi
    private const STATUS_AKTIF = ['diajukan', 'disetujui', 'diambil_relawan', 'diantar'];

    private const RELASI = ['donasi', 'penerima', 'relawan', 'penugasan'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = KlaimDonasi::with(self::RELASI)->latest();

        if ($user->role === 'penerima') {
            $query->where('penerima_id', $user->id);
        } elseif ($user->role === 'relawan') {
            // Relawan hanya melihat klaim yang sudah disetujui dan belum punya relawan
            $query->where('status', 'disetujui')->whereNull('relawan_id');
        } elseif ($user->role === 'donatur') {
            $query->whereHas('donasi', fn ($q) => $q->where('user_id', $user->id));
        }

        return response()->json([
            'sukses' => true,
            'data'   => $query->get()
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'donasi_id'    => ['required', 'exists:donasi_makanan,id'],
            'jumlah_porsi' => ['required', 'integer', 'min:1'],
        ]);

        $klaim = DB::transaction(function () use ($request) {
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

    public function show(Request $request, int $id): JsonResponse
    {
        $klaim = KlaimDonasi::with(self::RELASI)->find($id);

        if (!$klaim) {
            return response()->json(['sukses' => false, 'pesan' => 'Klaim donasi tidak ditemukan.'], 404);
        }

        $user = $request->user();
        if ($user->role === 'penerima' && (int) $klaim->penerima_id !== (int) $user->id) {
            return response()->json(['sukses' => false, 'pesan' => 'Anda tidak memiliki hak akses untuk klaim ini.'], 403);
        }

        return response()->json(['sukses' => true, 'data' => $klaim]);
    }

    // Khusus admin (dibatasi di routes/api.php)
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:diajukan,disetujui,ditolak,diambil_relawan,diantar,selesai,dibatalkan'],
        ]);

        $klaim = DB::transaction(function () use ($id, $validated) {
            $klaim = KlaimDonasi::lockForUpdate()->find($id);
            if (!$klaim) {
                return null;
            }

            $lama = $klaim->status;
            $baru = $validated['status'];

            if (in_array($lama, ['ditolak', 'dibatalkan'], true) && $baru !== $lama) {
                abort(422, 'Klaim yang sudah ditolak atau dibatalkan tidak dapat diaktifkan kembali.');
            }

            // Klaim ditolak/dibatalkan: porsi dikembalikan, penugasan relawan dilepas
            if (in_array($baru, ['ditolak', 'dibatalkan'], true) && in_array($lama, self::STATUS_AKTIF, true)) {
                $this->kembalikanPorsi($klaim);
                $klaim->penugasan()->delete();
                $klaim->relawan_id = null;
            }

            $klaim->status = $baru;
            $klaim->save();

            return $klaim;
        });

        if (!$klaim) {
            return response()->json(['sukses' => false, 'pesan' => 'Klaim donasi tidak ditemukan.'], 404);
        }

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Status klaim donasi berhasil diperbarui.',
            'data'   => $klaim->load(self::RELASI)
        ]);
    }

    // Khusus admin (dibatasi di routes/api.php)
    public function destroy(int $id): JsonResponse
    {
        $ditemukan = DB::transaction(function () use ($id) {
            $klaim = KlaimDonasi::lockForUpdate()->find($id);
            if (!$klaim) {
                return false;
            }

            if (in_array($klaim->status, self::STATUS_AKTIF, true)) {
                $this->kembalikanPorsi($klaim);
            }

            $klaim->delete();

            return true;
        });

        if (!$ditemukan) {
            return response()->json(['sukses' => false, 'pesan' => 'Klaim donasi tidak ditemukan.'], 404);
        }

        return response()->json(['sukses' => true, 'pesan' => 'Klaim donasi berhasil dibatalkan.']);
    }

    private function kembalikanPorsi(KlaimDonasi $klaim): void
    {
        $donasi = DonasiMakanan::lockForUpdate()->find($klaim->donasi_id);
        if (!$donasi) {
            return;
        }

        $donasi->porsi_tersisa = min($donasi->porsi, $donasi->porsi_tersisa + $klaim->jumlah_porsi);

        if ($donasi->status === 'habis' && $donasi->porsi_tersisa > 0) {
            $donasi->status = 'tersedia';
        }

        $donasi->save();
    }
}