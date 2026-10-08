<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriMakanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriMakananController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['sukses' => true, 'data' => KategoriMakanan::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'             => ['required', 'string', 'max:100'],
            'deskripsi'        => ['nullable', 'string'],
            'panduan_simpan'   => ['nullable', 'string'],
            'batas_simpan_jam' => ['required', 'integer', 'min:1'],
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
            'nama'             => ['sometimes', 'string', 'max:100'],
            'deskripsi'        => ['nullable', 'string'],
            'panduan_simpan'   => ['nullable', 'string'],
            'batas_simpan_jam' => ['sometimes', 'integer', 'min:1'],
        ]);

        $kategori->update($validated);

        return response()->json(['sukses' => true, 'pesan' => 'Kategori berhasil diperbarui.', 'data' => $kategori]);
    }

    public function destroy(int $id): JsonResponse
    {
        KategoriMakanan::findOrFail($id)->delete();
        return response()->json(['sukses' => true, 'pesan' => 'Kategori berhasil dihapus.']);
    }
}