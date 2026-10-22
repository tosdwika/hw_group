<?php

namespace Database\Seeders;

use App\Models\KategoriBuku;
use Illuminate\Database\Seeder;

class KategoriBukuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Fiksi',
                'deskripsi' => 'Karya sastra imajinatif seperti novel dan cerpen.',
            ],
            [
                'nama' => 'Sastra',
                'deskripsi' => 'Karya sastra klasik dan bernilai seni tinggi.',
            ],
            [
                'nama' => 'Filsafat',
                'deskripsi' => 'Buku pemikiran filosofis, kebijaksanaan hidup, dan logika.',
            ],
            [
                'nama' => 'Sejarah',
                'deskripsi' => 'Buku yang mengulas peristiwa dan tokoh sejarah.',
            ],
            [
                'nama' => 'Pengembangan Diri',
                'deskripsi' => 'Buku motivasi, produktivitas, dan pengembangan karakter.',
            ],
        ];

        foreach ($data as $item) {
            KategoriBuku::create($item);
        }
    }
}
