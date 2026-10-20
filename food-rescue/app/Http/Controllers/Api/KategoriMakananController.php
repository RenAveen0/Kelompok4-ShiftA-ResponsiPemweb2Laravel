<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriMakanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriMakananController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => KategoriMakanan::orderBy('nama')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'             => ['required', 'string', 'max:100', 'unique:kategori_makanan,nama'],
            'deskripsi'        => ['nullable', 'string'],
            'panduan_simpan'   => ['nullable', 'string'],
            'batas_simpan_jam' => ['required', 'integer', 'min:1', 'max:720'],
        ], [
            'nama.unique' => 'Nama kategori sudah digunakan.',
        ]);

        $kategori = KategoriMakanan::create($validated);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Kategori makanan berhasil ditambahkan.',
            'data'   => $kategori
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => KategoriMakanan::findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $kategori = KategoriMakanan::findOrFail($id);

        $validated = $request->validate([
            'nama'             => ['sometimes', 'string', 'max:100', Rule::unique('kategori_makanan', 'nama')->ignore($kategori->id)],
            'deskripsi'        => ['nullable', 'string'],
            'panduan_simpan'   => ['nullable', 'string'],
            'batas_simpan_jam' => ['sometimes', 'integer', 'min:1', 'max:720'],
        ], [
            'nama.unique' => 'Nama kategori sudah digunakan.',
        ]);

        $kategori->update($validated);

        return response()->json(['sukses' => true, 'pesan' => 'Kategori berhasil diperbarui.', 'data' => $kategori]);
    }

    public function destroy(int $id): JsonResponse
    {
        $kategori = KategoriMakanan::findOrFail($id);

        // donasi_makanan.kategori_id memakai restrictOnDelete(); tanpa pengecekan ini hasilnya error 500
        if ($kategori->donasi()->exists()) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Kategori tidak dapat dihapus karena masih dipakai oleh donasi makanan.',
            ], 409);
        }

        $kategori->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Kategori berhasil dihapus.']);
    }
}
