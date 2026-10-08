<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Admin: Lihat daftar seluruh pengguna
    public function index(): JsonResponse
    {
        $users = User::latest()->paginate(10);
        return response()->json(['sukses' => true, 'data' => $users]);
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
            'latitude'   => ['nullable', 'numeric'],
            'longitude'  => ['nullable', 'numeric'],
        ]);

        $user->update($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Profil berhasil diperbarui.',
            'data'   => $user
        ]);
    }

    // Admin: Hapus pengguna
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Pengguna berhasil dihapus.']);
    }
}