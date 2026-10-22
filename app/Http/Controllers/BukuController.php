<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    // Menampilkan semua data buku
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 10) ?: 10;
        $buku = Buku::with('kategori')->paginate($perPage);

        return response()->json([
            'message' => 'Berhasil mengambil daftar buku',
            'data' => $buku,
        ], 200);
    }

    // Menyimpan data buku baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_buku_id' => ['nullable', 'exists:kategori_buku,id'],
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'tahun_terbit' => ['required', 'integer'],
            'deskripsi' => ['nullable', 'string'],
            'stok' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['stok'] = $validated['stok'] ?? 1;

        $buku = Buku::create($validated);

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'data' => $buku,
        ], 201);
    }

    // Menampilkan detail satu buku
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with('kategori')->find($id);

        if (! $buku) {
            return response()->json([
                'message' => 'Buku tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail buku',
            'data' => $buku,
        ], 200);
    }

    // Mengupdate data buku
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (! $buku) {
            return response()->json([
                'message' => 'Buku tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'kategori_buku_id' => ['sometimes', 'nullable', 'exists:kategori_buku,id'],
            'judul' => ['sometimes', 'required', 'string', 'max:255'],
            'penulis' => ['sometimes', 'required', 'string', 'max:255'],
            'penerbit' => ['sometimes', 'required', 'string', 'max:255'],
            'tahun_terbit' => ['sometimes', 'required', 'integer'],
            'deskripsi' => ['nullable', 'string'],
            'stok' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $buku->update($validated);

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data' => $buku,
        ], 200);
    }

    // Menghapus data buku
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (! $buku) {
            return response()->json([
                'message' => 'Buku tidak ditemukan',
            ], 404);
        }

        $buku->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus',
        ], 200);
    }
}
