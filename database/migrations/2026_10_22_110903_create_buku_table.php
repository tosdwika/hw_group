<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_buku_id')->nullable()->constrained('kategori_buku')->nullOnDelete();
            $table->string('judul');
            $table->string('penulis');
            $table->string('penerbit');
            $table->integer('tahun_terbit');
            $table->text('deskripsi')->nullable();
            $table->integer('stok')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
