<?php

namespace App\Http\Controllers;

use App\Models\KategoriBuku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriBukuController extends Controller
{
    // Menampilkan semua data kategori buku
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 10) ?: 10;
        $kategori = KategoriBuku::paginate($perPage);

        return response()->json([
            'message' => 'Berhasil mengambil daftar kategori buku',
            'data' => $kategori,
        ], 200);
    }

    // Menyimpan data kategori buku baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:kategori_buku,nama'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $kategori = KategoriBuku::create($validated);

        return response()->json([
            'message' => 'Kategori buku berhasil ditambahkan',
            'data' => $kategori,
        ], 201);
    }

    // Menampilkan detail satu kategori buku
    public function show(string $id): JsonResponse
    {
        $kategori = KategoriBuku::with('buku')->find($id);

        if (! $kategori) {
            return response()->json([
                'message' => 'Kategori buku tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail kategori buku',
            'data' => $kategori,
        ], 200);
    }

    // Mengupdate data kategori buku
    public function update(Request $request, string $id): JsonResponse
    {
        $kategori = KategoriBuku::find($id);

        if (! $kategori) {
            return response()->json([
                'message' => 'Kategori buku tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('kategori_buku', 'nama')->ignore($kategori->id)],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $kategori->update($validated);

        return response()->json([
            'message' => 'Kategori buku berhasil diperbarui',
            'data' => $kategori,
        ], 200);
    }

    // Menghapus data kategori buku
    public function destroy(string $id): JsonResponse
    {
        $kategori = KategoriBuku::find($id);

        if (! $kategori) {
            return response()->json([
                'message' => 'Kategori buku tidak ditemukan',
            ], 404);
        }

        $kategori->delete();

        return response()->json([
            'message' => 'Kategori buku berhasil dihapus',
        ], 200);
    }
}
