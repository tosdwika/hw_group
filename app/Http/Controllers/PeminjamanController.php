<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    // Menampilkan semua data transaksi peminjaman
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 10) ?: 10;
        $peminjaman = Peminjaman::with(['user:id,name,email', 'buku:id,judul,penulis,stok'])
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Berhasil mengambil daftar peminjaman',
            'data' => $peminjaman,
        ], 200);
    }

    // Menyimpan transaksi peminjaman baru dan mengurangi stok buku
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'exists:buku,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali' => ['nullable', 'date', 'after_or_equal:tanggal_pinjam'],
            'status' => ['nullable', 'in:dipinjam,dikembalikan'],
        ]);

        $validated['user_id'] = $validated['user_id'] ?? auth('api')->id();
        $validated['status'] = $validated['status'] ?? 'dipinjam';

        $result = DB::transaction(function () use ($validated) {
            $buku = Buku::lockForUpdate()->find($validated['buku_id']);

            if ($validated['status'] === 'dipinjam' && $buku->stok <= 0) {
                return ['error' => 'Stok buku habis, tidak dapat melakukan peminjaman', 'status_code' => 400];
            }

            if ($validated['status'] === 'dipinjam') {
                $buku->decrement('stok');
            }

            $peminjaman = Peminjaman::create($validated);

            return ['peminjaman' => $peminjaman];
        });

        if (isset($result['error'])) {
            return response()->json([
                'message' => $result['error'],
            ], $result['status_code']);
        }

        return response()->json([
            'message' => 'Transaksi peminjaman berhasil dibuat',
            'data' => $result['peminjaman']->load(['user:id,name,email', 'buku']),
        ], 201);
    }

    // Menampilkan detail transaksi peminjaman
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with(['user:id,name,email', 'buku.kategori'])->find($id);

        if (! $peminjaman) {
            return response()->json([
                'message' => 'Data peminjaman tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail peminjaman',
            'data' => $peminjaman,
        ], 200);
    }

    // Mengupdate data transaksi peminjaman dan menyesuaikan stok buku
    public function update(Request $request, string $id): JsonResponse
    {
        $peminjaman = Peminjaman::find($id);

        if (! $peminjaman) {
            return response()->json([
                'message' => 'Data peminjaman tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'buku_id' => ['sometimes', 'required', 'exists:buku,id'],
            'tanggal_pinjam' => ['sometimes', 'required', 'date'],
            'tanggal_kembali' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', 'in:dipinjam,dikembalikan'],
        ]);

        $result = DB::transaction(function () use ($peminjaman, $validated) {
            $oldBukuId = $peminjaman->buku_id;
            $oldStatus = $peminjaman->status;

            $newBukuId = $validated['buku_id'] ?? $oldBukuId;
            $newStatus = $validated['status'] ?? $oldStatus;

            if ($oldStatus === 'dipinjam' && $newStatus === 'dikembalikan') {
                Buku::where('id', $oldBukuId)->increment('stok');
                if (! isset($validated['tanggal_kembali']) && ! $peminjaman->tanggal_kembali) {
                    $validated['tanggal_kembali'] = now()->toDateString();
                }
            } elseif ($oldStatus === 'dikembalikan' && $newStatus === 'dipinjam') {
                $buku = Buku::lockForUpdate()->find($newBukuId);
                if (! $buku || $buku->stok <= 0) {
                    return ['error' => 'Stok buku habis, tidak dapat mengubah status menjadi dipinjam', 'status_code' => 400];
                }
                $buku->decrement('stok');
                $validated['tanggal_kembali'] = null;
            } elseif ($oldStatus === 'dipinjam' && $newStatus === 'dipinjam' && $oldBukuId != $newBukuId) {
                Buku::where('id', $oldBukuId)->increment('stok');
                $bukuBaru = Buku::lockForUpdate()->find($newBukuId);
                if (! $bukuBaru || $bukuBaru->stok <= 0) {
                    return ['error' => 'Stok buku pengganti habis', 'status_code' => 400];
                }
                $bukuBaru->decrement('stok');
            }

            $peminjaman->update($validated);

            return ['peminjaman' => $peminjaman];
        });

        if (isset($result['error'])) {
            return response()->json([
                'message' => $result['error'],
            ], $result['status_code']);
        }

        return response()->json([
            'message' => 'Transaksi peminjaman berhasil diperbarui',
            'data' => $result['peminjaman']->fresh()->load(['user:id,name,email', 'buku']),
        ], 200);
    }

    // Menghapus transaksi peminjaman dan mengembalikan stok buku
    public function destroy(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::find($id);

        if (! $peminjaman) {
            return response()->json([
                'message' => 'Data peminjaman tidak ditemukan',
            ], 404);
        }

        DB::transaction(function () use ($peminjaman) {
            if ($peminjaman->status === 'dipinjam') {
                Buku::where('id', $peminjaman->buku_id)->increment('stok');
            }
            $peminjaman->delete();
        });

        return response()->json([
            'message' => 'Transaksi peminjaman berhasil dihapus',
        ], 200);
    }
}
