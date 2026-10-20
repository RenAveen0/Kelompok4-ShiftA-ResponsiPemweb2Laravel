<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KlaimDonasi;
use App\Models\LaporanKelayakan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaporanKelayakanController extends Controller
{
    private const STATUS = ['menunggu_tinjauan', 'diproses', 'ditindaklanjuti', 'ditolak'];

    private const RELASI = ['donasi:id,judul,user_id', 'pelapor:id,name'];

    // Admin melihat semua laporan; donatur melihat laporan atas donasinya; lainnya hanya laporannya sendiri
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = LaporanKelayakan::with(self::RELASI)->latest();

        match ($user->role) {
            'admin'   => null,
            'donatur' => $query->whereHas('donasi', fn ($q) => $q->where('user_id', $user->id)),
            default   => $query->where('pelapor_id', $user->id),
        };

        return response()->json(['sukses' => true, 'data' => $query->get()]);
    }

    // Penerima melaporkan donasi yang pernah diklaimnya jika makanan basi / tidak layak
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'donasi_id'  => ['required', 'integer', 'exists:donasi_makanan,id'],
            'alasan'     => ['required', 'string', 'max:1000'],
            'foto_bukti' => ['nullable', ...$this->aturanFoto($request)],
        ]);

        $pernahKlaim = KlaimDonasi::where('donasi_id', $validated['donasi_id'])
            ->where('penerima_id', $user->id)
            ->whereNotIn('status', ['ditolak', 'dibatalkan'])
            ->exists();

        if (! $pernahKlaim) {
            return $this->tolak('Anda hanya dapat melaporkan donasi yang pernah Anda klaim.', 403);
        }

        $masihDiproses = LaporanKelayakan::where('donasi_id', $validated['donasi_id'])
            ->where('pelapor_id', $user->id)
            ->whereIn('status', ['menunggu_tinjauan', 'diproses'])
            ->exists();

        if ($masihDiproses) {
            return $this->tolak('Laporan Anda untuk donasi ini masih dalam proses tinjauan.', 422);
        }

        if ($request->hasFile('foto_bukti')) {
            $validated['foto_bukti'] = $request->file('foto_bukti')->store('laporan', 'public');
        }

        $validated['pelapor_id'] = $user->id;
        $validated['status'] = 'menunggu_tinjauan';

        $laporan = LaporanKelayakan::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Laporan kelayakan berhasil dikirim.',
            'data'   => $laporan->load(self::RELASI),
        ], 201);
    }

    // Detail laporan: admin, pelapor, atau donatur pemilik donasi
    public function show(Request $request, int $id): JsonResponse
    {
        $laporan = LaporanKelayakan::with(self::RELASI)->findOrFail($id);

        if (! $this->bolehMelihat($request->user(), $laporan)) {
            return $this->tolak('Anda tidak memiliki hak akses untuk laporan ini.', 403);
        }

        return response()->json(['sukses' => true, 'data' => $laporan]);
    }

    // Khusus admin (dibatasi di routes/api.php): menindaklanjuti laporan
    public function update(Request $request, int $id): JsonResponse
    {
        $laporan = LaporanKelayakan::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(self::STATUS)],
        ]);

        $laporan->update($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Status laporan berhasil diperbarui.',
            'data'   => $laporan->load(self::RELASI),
        ]);
    }

    // Khusus admin (dibatasi di routes/api.php)
    public function destroy(int $id): JsonResponse
    {
        $laporan = LaporanKelayakan::findOrFail($id);

        $path = $laporan->getRawOriginal('foto_bukti');
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }

        $laporan->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Laporan kelayakan berhasil dihapus.']);
    }

    private function bolehMelihat(User $user, LaporanKelayakan $laporan): bool
    {
        return $user->role === 'admin'
            || (int) $laporan->pelapor_id === (int) $user->id
            || ($user->role === 'donatur' && (int) $laporan->donasi?->user_id === (int) $user->id);
    }

    private function aturanFoto(Request $request): array
    {
        return $request->hasFile('foto_bukti')
            ? ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
            : ['string', 'url', 'max:2048'];
    }

    private function tolak(string $pesan, int $kode): JsonResponse
    {
        return response()->json(['sukses' => false, 'pesan' => $pesan], $kode);
    }
}
