<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuktiSerahTerima;
use App\Models\KlaimDonasi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuktiSerahTerimaController extends Controller
{
    private const RELASI = ['klaim.donasi:id,judul,user_id', 'relawan:id,name'];

    // Bukti hanya boleh dibuat saat makanan sedang atau sudah diantar
    private const STATUS_KLAIM_BOLEH = ['diantar', 'selesai'];

    // Daftar bukti, dibatasi sesuai peran (admin melihat semua)
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = BuktiSerahTerima::with(self::RELASI)->latest();

        match ($user->role) {
            'relawan'  => $query->where('relawan_id', $user->id),
            'penerima' => $query->whereHas('klaim', fn ($q) => $q->where('penerima_id', $user->id)),
            'donatur'  => $query->whereHas('klaim.donasi', fn ($q) => $q->where('user_id', $user->id)),
            default    => null,
        };

        return response()->json(['sukses' => true, 'data' => $query->get()]);
    }

    // Relawan/Admin unggah bukti penyerahan (foto bisa berupa file gambar atau URL)
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'klaim_id'           => ['required', 'integer', 'exists:klaim_donasi,id', 'unique:bukti_serah_terima,klaim_id'],
            'foto_bukti'         => ['required', ...$this->aturanFoto($request)],
            'catatan'            => ['nullable', 'string'],
            'waktu_serah_terima' => ['nullable', 'date'],
        ], [
            'klaim_id.unique' => 'Bukti serah terima untuk klaim ini sudah ada. Gunakan PUT untuk memperbaruinya.',
        ]);

        $klaim = KlaimDonasi::findOrFail($validated['klaim_id']);

        if ($user->role === 'relawan' && (int) $klaim->relawan_id !== (int) $user->id) {
            return $this->tolak('Klaim ini bukan tugas Anda.', 403);
        }

        if (! in_array($klaim->status, self::STATUS_KLAIM_BOLEH, true)) {
            return $this->tolak('Bukti hanya dapat diunggah setelah makanan diantar.', 422);
        }

        $validated['relawan_id'] = $klaim->relawan_id ?? $user->id;
        $validated['waktu_serah_terima'] ??= now();
        $validated['foto_bukti'] = $this->simpanFoto($request, $validated['foto_bukti']);

        $bukti = BuktiSerahTerima::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Bukti serah terima berhasil diunggah.',
            'data'   => $bukti->load(self::RELASI),
        ], 201);
    }

    // Detail bukti serah terima
    public function show(Request $request, int $id): JsonResponse
    {
        $bukti = BuktiSerahTerima::with(self::RELASI)->findOrFail($id);

        if (! $this->bolehMelihat($request->user(), $bukti)) {
            return $this->tolak('Anda tidak memiliki hak akses untuk bukti ini.', 403);
        }

        return response()->json(['sukses' => true, 'data' => $bukti]);
    }

    // Relawan pemilik bukti / Admin memperbaiki foto atau catatan.
    // Catatan: untuk kirim file lewat update, gunakan POST + field _method=PUT (form-data).
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $bukti = BuktiSerahTerima::findOrFail($id);

        if ($user->role === 'relawan' && (int) $bukti->relawan_id !== (int) $user->id) {
            return $this->tolak('Bukti ini bukan milik Anda.', 403);
        }

        $validated = $request->validate([
            'foto_bukti'         => ['sometimes', ...$this->aturanFoto($request)],
            'catatan'            => ['nullable', 'string'],
            'waktu_serah_terima' => ['sometimes', 'date'],
        ]);

        if (array_key_exists('foto_bukti', $validated)) {
            $this->hapusFoto($bukti);
            $validated['foto_bukti'] = $this->simpanFoto($request, $validated['foto_bukti']);
        }

        $bukti->update($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Bukti serah terima berhasil diperbarui.',
            'data'   => $bukti->load(self::RELASI),
        ]);
    }

    // Khusus admin (dibatasi di routes/api.php)
    public function destroy(int $id): JsonResponse
    {
        $bukti = BuktiSerahTerima::findOrFail($id);
        $this->hapusFoto($bukti);
        $bukti->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Bukti serah terima berhasil dihapus.']);
    }

    private function bolehMelihat(User $user, BuktiSerahTerima $bukti): bool
    {
        return match ($user->role) {
            'admin'    => true,
            'relawan'  => (int) $bukti->relawan_id === (int) $user->id,
            'penerima' => (int) $bukti->klaim?->penerima_id === (int) $user->id,
            'donatur'  => (int) $bukti->klaim?->donasi?->user_id === (int) $user->id,
            default    => false,
        };
    }

    private function aturanFoto(Request $request): array
    {
        return $request->hasFile('foto_bukti')
            ? ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
            : ['string', 'url', 'max:2048'];
    }

    private function simpanFoto(Request $request, mixed $nilai): string
    {
        return $request->hasFile('foto_bukti')
            ? $request->file('foto_bukti')->store('bukti', 'public')
            : $nilai;
    }

    private function hapusFoto(BuktiSerahTerima $bukti): void
    {
        $path = $bukti->getRawOriginal('foto_bukti');

        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function tolak(string $pesan, int $kode): JsonResponse
    {
        return response()->json(['sukses' => false, 'pesan' => $pesan], $kode);
    }
}
