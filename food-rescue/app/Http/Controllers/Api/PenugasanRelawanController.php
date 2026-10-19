<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KlaimDonasi;
use App\Models\PenugasanRelawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PenugasanRelawanController extends Controller
{
    // Urutan tahap penugasan
    private const URUTAN = ['ditugaskan', 'menuju_lokasi', 'diambil', 'diantar', 'selesai'];

    // Status klaim yang mengikuti status penugasan
    private const STATUS_KLAIM = [
        'ditugaskan'    => 'disetujui',
        'menuju_lokasi' => 'disetujui',
        'diambil'       => 'diambil_relawan',
        'diantar'       => 'diantar',
        'selesai'       => 'selesai',
    ];

    private const RELASI = ['klaim.donasi', 'klaim.penerima', 'relawan'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PenugasanRelawan::with(self::RELASI)->latest();

        // Relawan hanya melihat tugasnya sendiri; admin melihat semua
        if ($user->role === 'relawan') {
            $query->where('relawan_id', $user->id);
        }

        return response()->json([
            'sukses' => true,
            'data'   => $query->get()
        ]);
    }

    // Relawan: mengambil tugas sendiri. Admin: menugaskan relawan (relawan_id wajib diisi).
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $admin = $user->role === 'admin';

        $rules = [
            'klaim_id' => ['required', 'exists:klaim_donasi,id'],
            'catatan'  => ['nullable', 'string'],
        ];
        if ($admin) {
            $rules['relawan_id'] = ['required', Rule::exists('users', 'id')->where('role', 'relawan')];
        }

        $validated = $request->validate($rules);
        $relawanId = $admin ? (int) $validated['relawan_id'] : (int) $user->id;

        $penugasan = DB::transaction(function () use ($validated, $relawanId) {
            $klaim = KlaimDonasi::lockForUpdate()->findOrFail($validated['klaim_id']);

            if ($klaim->status !== 'disetujui') {
                abort(422, 'Hanya klaim yang sudah disetujui yang dapat ditugaskan ke relawan.');
            }

            if (PenugasanRelawan::where('klaim_id', $klaim->id)->exists()) {
                abort(422, 'Klaim ini sudah memiliki relawan.');
            }

            $penugasan = PenugasanRelawan::create([
                'klaim_id'   => $klaim->id,
                'relawan_id' => $relawanId,
                'status'     => 'ditugaskan',
                'catatan'    => $validated['catatan'] ?? null,
            ]);

            $klaim->update(['relawan_id' => $relawanId]);

            return $penugasan;
        });

        return response()->json([
            'sukses' => true,
            'pesan'  => $admin ? 'Relawan berhasil ditugaskan.' : 'Tugas pengantaran berhasil diambil.',
            'data'   => $penugasan->load(self::RELASI)
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $penugasan = PenugasanRelawan::with(self::RELASI)->find($id);

        if (!$penugasan) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan relawan tidak ditemukan.'], 404);
        }

        $user = $request->user();
        if ($user->role === 'relawan' && (int) $penugasan->relawan_id !== (int) $user->id) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan ini bukan milik Anda.'], 403);
        }

        return response()->json(['sukses' => true, 'data' => $penugasan]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $admin = $user->role === 'admin';

        $rules = [
            'status'  => ['required', Rule::in(self::URUTAN)],
            'catatan' => ['nullable', 'string'],
        ];
        if ($admin) {
            // Admin boleh mengganti relawan
            $rules['relawan_id'] = ['sometimes', Rule::exists('users', 'id')->where('role', 'relawan')];
        }

        $validated = $request->validate($rules);

        $penugasan = DB::transaction(function () use ($id, $validated, $user, $admin) {
            $penugasan = PenugasanRelawan::lockForUpdate()->find($id);
            if (!$penugasan) {
                return null;
            }

            if (!$admin && (int) $penugasan->relawan_id !== (int) $user->id) {
                abort(403, 'Penugasan ini bukan milik Anda.');
            }

            $lama = array_search($penugasan->status, self::URUTAN, true);
            $baru = array_search($validated['status'], self::URUTAN, true);
            if (!$admin && $baru < $lama) {
                abort(422, 'Status penugasan tidak boleh mundur.');
            }

            $penugasan->fill($validated);

            if (in_array($validated['status'], ['diambil', 'diantar', 'selesai'], true) && !$penugasan->waktu_ambil) {
                $penugasan->waktu_ambil = now();
            }
            if ($validated['status'] === 'selesai' && !$penugasan->waktu_selesai) {
                $penugasan->waktu_selesai = now();
            }

            $penugasan->save();

            // Status klaim ikut mengikuti tahap penugasan
            KlaimDonasi::whereKey($penugasan->klaim_id)->update([
                'relawan_id' => $penugasan->relawan_id,
                'status'     => self::STATUS_KLAIM[$penugasan->status],
            ]);

            return $penugasan;
        });

        if (!$penugasan) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan relawan tidak ditemukan.'], 404);
        }

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Status penugasan relawan berhasil diperbarui.',
            'data'   => $penugasan->load(self::RELASI)
        ]);
    }

    // Khusus admin (dibatasi di routes/api.php)
    public function destroy(int $id): JsonResponse
    {
        $ditemukan = DB::transaction(function () use ($id) {
            $penugasan = PenugasanRelawan::lockForUpdate()->find($id);
            if (!$penugasan) {
                return false;
            }

            // Klaim kembali ke "disetujui" dan tanpa relawan, siap ditugaskan ulang
            $klaim = KlaimDonasi::find($penugasan->klaim_id);
            if ($klaim) {
                $klaim->relawan_id = null;
                if (in_array($klaim->status, ['diambil_relawan', 'diantar'], true)) {
                    $klaim->status = 'disetujui';
                }
                $klaim->save();
            }

            $penugasan->delete();

            return true;
        });

        if (!$ditemukan) {
            return response()->json(['sukses' => false, 'pesan' => 'Penugasan relawan tidak ditemukan.'], 404);
        }

        return response()->json(['sukses' => true, 'pesan' => 'Penugasan relawan berhasil dibatalkan.']);
    }
}