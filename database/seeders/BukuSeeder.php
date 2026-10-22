<?php

namespace Database\Seeders;

use App\Models\Buku;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kategori_buku_id' => 1,
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2005,
                'deskripsi' => 'Novel tentang kehidupan 10 anak di Belitung yang penuh semangat belajar.',
                'stok' => 10,
            ],
            [
                'kategori_buku_id' => 2,
                'judul' => 'Bumi Manusia',
                'penulis' => 'Pramoedya Ananta Toer',
                'penerbit' => 'Hasta Mitra',
                'tahun_terbit' => 1980,
                'deskripsi' => 'Kisah perjuangan Minke di masa kolonial Belanda.',
                'stok' => 5,
            ],
            [
                'kategori_buku_id' => 3,
                'judul' => 'Filosofi Teras',
                'penulis' => 'Henry Manampiring',
                'penerbit' => 'Kompas',
                'tahun_terbit' => 2018,
                'deskripsi' => 'Penerapan filsafat Stoisisme dalam kehidupan sehari-hari.',
                'stok' => 8,
            ],
            [
                'kategori_buku_id' => 4,
                'judul' => 'Laut Bercerita',
                'penulis' => 'Leila S. Chudori',
                'penerbit' => 'Kepustakaan Populer Gramedia',
                'tahun_terbit' => 2017,
                'deskripsi' => 'Kisah aktivis mahasiswa era 1998 dan duka keluarga korban penculikan.',
                'stok' => 4,
            ],
            [
                'kategori_buku_id' => 5,
                'judul' => 'Atomic Habits',
                'penulis' => 'James Clear',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2018,
                'deskripsi' => 'Perubahan kecil yang memberikan hasil luar biasa dalam hidup.',
                'stok' => 12,
            ],
        ];

        foreach ($data as $item) {
            Buku::create($item);
        }
    }
}
