<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private const PERAN = ['admin', 'donatur', 'penerima', 'relawan'];

    // Admin : Lihat daftar seluruh pengguna (opsional ?role=relawan&q=nama atau email)
    public function index(Request $request): JsonResponse
    {
        $cari = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->when($cari !== '', fn ($q) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$cari}%")->orWhere('email', 'like', "%{$cari}%")
            ))
            ->latest()
            ->paginate(10);

        return response()->json(['sukses' => true, 'data' => $users]);
    }

    // Admin : Daftar relawan (untuk dropdown penugasan)
    public function relawan(): JsonResponse
    {
        $relawan = User::where('role', 'relawan')->orderBy('name')->get(['id', 'name', 'no_telepon']);

        return response()->json(['sukses' => true, 'data' => $relawan]);
    }

    // Admin : Tambah pengguna (termasuk membuat akun admin lain)
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role'       => ['required', Rule::in(self::PERAN)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat'     => ['nullable', 'string'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        // Kata sandi di-hash otomatis oleh cast 'hashed' pada model User
        $user = User::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Pengguna berhasil ditambahkan.',
            'data'   => $user,
        ], 201);
    }

    // Admin : Detail satu pengguna
    public function show(int $id): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => User::findOrFail($id)]);
    }

    // Admin : Ubah data pengguna (termasuk peran dan reset kata sandi)
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'       => ['sometimes', 'string', 'max:100'],
            'email'      => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'   => ['sometimes', 'string', 'min:8', 'confirmed'],
            'role'       => ['sometimes', Rule::in(self::PERAN)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat'     => ['nullable', 'string'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $peranBerubah = isset($validated['role']) && $validated['role'] !== $user->role;

        if ($peranBerubah && $user->id === $request->user()->id) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
            ], 422);
        }

        $user->update($validated);

        // Hak akses (abilities) token ditentukan saat login, jadi token lama dicabut
        // ketika peran atau kata sandi berubah agar pengguna login ulang.
        if ($peranBerubah || isset($validated['password'])) {
            $user->tokens()->delete();
        }

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Data pengguna berhasil diperbarui.',
            'data'   => $user->fresh(),
        ]);
    }

    // Lihat profil pengguna yang sedang login
    public function profil(Request $request): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => $request->user()]);
    }

    // Pembaruan profil dan alamat diri sendiri
    public function updateProfil(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'       => ['sometimes', 'string', 'max:100'],
            'no_telepon' => ['sometimes', 'string', 'max:20'],
            'alamat'     => ['nullable', 'string'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $user->update($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Profil berhasil diperbarui.',
            'data'   => $user,
        ]);
    }

    // Ganti kata sandi sendiri; sesi lain dikeluarkan, sesi saat ini tetap aktif
    public function ubahPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password'      => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['password_lama'], $user->password)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Kata sandi lama tidak sesuai.'],
            ]);
        }

        $user->update(['password' => $data['password']]);

        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Kata sandi berhasil diubah.']);
    }

    // Admin : Hapus pengguna (donasi/klaim milik pengguna ikut terhapus oleh cascade di database)
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($id === $request->user()->id) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], 422);
        }

        $user = User::findOrFail($id);

        // Token Sanctum bersifat polimorfik (tanpa foreign key), jadi dihapus manual
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Pengguna berhasil dihapus.']);
    }
}
