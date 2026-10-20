<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KlaimDonasi;
use App\Models\UlasanDonasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UlasanDonasiController extends Controller
{
    private const RELASI = ['klaim.donasi:id,judul', 'user:id,name'];

    // Rating bersifat umum: semua pengguna yang login boleh melihat daftar ulasan
    public function index(): JsonResponse
    {
        $ulasan = UlasanDonasi::with(self::RELASI)->latest()->get();

        return response()->json(['sukses' => true, 'data' => $ulasan]);
    }

    // Penerima membuat ulasan untuk klaimnya sendiri setelah makanan diterima
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'klaim_id' => ['required', 'integer', 'exists:klaim_donasi,id', 'unique:ulasan_donasi,klaim_id'],
            'rating'   => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ], [
            'klaim_id.unique' => 'Klaim ini sudah pernah diulas. Gunakan PUT untuk mengubah ulasan.',
        ]);

        $klaim = KlaimDonasi::findOrFail($validated['klaim_id']);

        if ((int) $klaim->penerima_id !== (int) $request->user()->id) {
            return $this->tolak('Anda hanya dapat mengulas klaim milik sendiri.', 403);
        }

        if ($klaim->status !== 'selesai') {
            return $this->tolak('Ulasan hanya dapat diberikan setelah donasi selesai diterima.', 422);
        }

        $validated['user_id'] = $request->user()->id;

        $ulasan = UlasanDonasi::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Ulasan berhasil diberikan.',
            'data'   => $ulasan->load(self::RELASI),
        ], 201);
    }

    // Detail ulasan
    public function show(int $id): JsonResponse
    {
        $ulasan = UlasanDonasi::with(self::RELASI)->findOrFail($id);

        return response()->json(['sukses' => true, 'data' => $ulasan]);
    }

    // Penerima mengubah ulasannya sendiri
    public function update(Request $request, int $id): JsonResponse
    {
        $ulasan = UlasanDonasi::findOrFail($id);

        if ((int) $ulasan->user_id !== (int) $request->user()->id) {
            return $this->tolak('Ulasan ini bukan milik Anda.', 403);
        }

        $validated = $request->validate([
            'rating'   => ['sometimes', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        $ulasan->update($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Ulasan berhasil diperbarui.',
            'data'   => $ulasan->load(self::RELASI),
        ]);
    }

    // Admin boleh menghapus semua ulasan; penerima hanya ulasannya sendiri
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $ulasan = UlasanDonasi::findOrFail($id);

        if ($user->role !== 'admin' && (int) $ulasan->user_id !== (int) $user->id) {
            return $this->tolak('Ulasan ini bukan milik Anda.', 403);
        }

        $ulasan->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Ulasan berhasil dihapus.']);
    }

    private function tolak(string $pesan, int $kode): JsonResponse
    {
        return response()->json(['sukses' => false, 'pesan' => $pesan], $kode);
    }
}
