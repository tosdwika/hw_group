<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $buku1 = Buku::find(1);
        $buku2 = Buku::find(2);
        $buku3 = Buku::find(3);

        if (! $user || ! $buku1 || ! $buku2 || ! $buku3) {
            return;
        }

        $data = [
            [
                'user_id' => $user->id,
                'buku_id' => $buku1->id,
                'tanggal_pinjam' => '2026-10-20',
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
            ],
            [
                'user_id' => $user->id,
                'buku_id' => $buku2->id,
                'tanggal_pinjam' => '2026-10-15',
                'tanggal_kembali' => '2026-10-21',
                'status' => 'dikembalikan',
            ],
            [
                'user_id' => $user->id,
                'buku_id' => $buku3->id,
                'tanggal_pinjam' => '2026-10-22',
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
            ],
        ];

        foreach ($data as $item) {
            Peminjaman::create($item);

            if ($item['status'] === 'dipinjam') {
                Buku::where('id', $item['buku_id'])->decrement('stok');
            }
        }
    }
}
