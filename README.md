# HW Group Library REST API

REST API dibuat dengan Laravel dan PostgreSQL. Lewat API ini Anda bisa mengelola kategori buku, data buku, dan transaksi peminjaman buku.

## Isi dokumen

1. [Cara kerja singkat](#cara-kerja-singkat)
2. [Yang perlu dipasang](#yang-perlu-dipasang)
3. [Instalasi](#instalasi)
4. [Mencoba lewat Postman](#mencoba-lewat-postman)
5. [Aturan umum](#aturan-umum)
6. [Autentikasi](#autentikasi)
7. [Kategori buku](#kategori-buku)
8. [Buku](#buku)
9. [Peminjaman](#peminjaman)
10. [Pengembalian buku](#pengembalian-buku)
11. [Buku yang sedang dipinjam](#buku-yang-sedang-dipinjam)
12. [Arti kode status](#arti-kode-status)
13. [Petunjuk Penanganan Respon Error](#petunjuk-penanganan-respon-error)

## Cara kerja singkat

Hampir semua endpoint baru bisa dipakai setelah login. Login menghasilkan token, dan token itu harus ikut dikirim di setiap request berikutnya.

Ada tiga jenis data:

- Kategori buku, misalnya Fiksi atau Sejarah.
- Buku. Setiap buku punya stok dan boleh masuk ke satu kategori.
- Peminjaman, yaitu catatan siapa meminjam buku apa, kapan dipinjam, dan kapan dikembalikan.

Stok buku diatur oleh server. Saat buku dipinjam, stoknya berkurang satu. Saat dikembalikan, stoknya bertambah satu lagi. Buku yang stoknya 0 tidak bisa dipinjam.

## Yang perlu dipasang

- PHP 8.3 atau yang lebih baru (usahakan pakai PHP yang sudah di patch keamanan terbaru), dengan ekstensi `pdo_pgsql`, `mbstring`, dan `openssl`
- Composer
- PostgreSQL
- Postman, untuk mencoba API

## Instalasi

### 1. Masuk ke folder proyek

```bash
cd folder-project\hw_group
```

### 2. Pasang dependensi

```bash
composer install
```

### 3. Siapkan file `.env`

Salin `.env.example` menjadi `.env`. Lewati langkah ini kalau `.env` sudah ada.

```bash
cp .env.example .env
```

Kalau memakai CMD di Windows, perintahnya `copy .env.example .env`.

Buka `.env` dan cari bagian database. Baris `DB_HOST` sampai `DB_PASSWORD` masih diawali tanda `#`, jadi hapus dulu tanda itu. Setelah itu ganti `nama_database`, `username_database`, dan `password_database` dengan data PostgreSQL di komputer Anda:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database
```

Database dengan nama di `DB_DATABASE` harus sudah ada. Kalau belum, buat dulu di PostgreSQL.

### 4. Buat app key

```bash
php artisan key:generate
```

### 5. Buat JWT secret

Token login ditandatangani dengan secret ini. Kalau langkah ini terlewat, login akan gagal.

```bash
php artisan jwt:secret
```

### 6. Buat tabel dan isi dummy data

```bash
php artisan migrate --seed
```

Perintah ini membuat semua tabel, lalu mengisinya dengan data contoh: 1 akun admin, 5 kategori, 5 buku, dan 3 peminjaman.

Akun admin yang bisa langsung dipakai:

| Email | Password |
| :--- | :--- |
| `admin@holywings.com` | `adminpusat` |

### 7. Jalankan server

```bash
php artisan serve
```

API sekarang bisa diakses di `http://127.0.0.1:8000`.

## Mencoba lewat Postman

File `postman_collection.json` di folder proyek berisi semua request yang dibahas di dokumen ini, lengkap dengan URL dan contoh body.

### Import koleksi

1. Buka Postman, lalu klik Import di kiri atas.
2. Pilih file `folder-project\hw_group\postman_collection.json`, atau seret file itu ke jendela Postman.
3. Koleksi Holywings muncul di panel kiri. Isinya enam folder: AUTH, KATEGORI BUKU, BUKU, PEMINJAMAN, PENGEMBALIAN, dan DAFTAR PEMINJAMAN.

### Token tidak perlu disalin

Setiap kali REGISTER, LOGIN, atau REFRESH berhasil, Postman menyimpan token baru ke variabel `jwt_token`.

| Variabel | Isi |
| :--- | :--- |
| `base_url` | Alamat server, awalnya `http://127.0.0.1:8000` |
| `jwt_token` | Token login, terisi sendiri setelah login |

### Urutan yang disarankan

1. Buka folder AUTH dan jalankan LOGIN. Akun admin sudah terisi di body-nya.
2. Jalankan ME. Kalau data admin muncul, token sudah tersimpan.
3. Buka folder KATEGORI BUKU dan jalankan GET ALL. Hasilnya 5 sample data kategori buku.
4. Coba CREATE, lalu UPDATE, lalu DELETE.
5. Lakukan hal yang sama di folder BUKU.
6. Buka folder DAFTAR PEMINJAMAN dan jalankan DAFTAR BUKU DIPINJAM untuk melihat buku yang belum dikembalikan.
7. Buka folder PEMINJAMAN dan jalankan CREATE untuk meminjam buku. Cek stoknya lewat GET SINGLE di folder BUKU, angkanya berkurang satu.
8. Buka folder PENGEMBALIAN dan jalankan PENGEMBALIAN BUKU.

Request yang memakai ID (GET SINGLE, UPDATE, DELETE, dan PENGEMBALIAN BUKU) mengarah ke ID 1 milik data contoh. Ganti angka di ujung URL kalau ingin memakai data lain.

## Aturan umum

### Istilah

| Istilah | Artinya |
| :--- | :--- |
| Endpoint | Alamat yang dipanggil, misalnya `/api/buku` |
| Body | Data JSON yang dikirim bersama request POST, PUT, atau PATCH |
| Field | Satu isian di dalam data, misalnya `judul` atau `stok` |
| Query parameter | Tambahan di ujung URL setelah tanda `?`, misalnya `?page=2` |
| Token | Kode autentikasi dari server setelah login, tanda bahwa Anda sudah masuk |

### Header

| Header | Nilai | Kapan dipakai |
| :--- | :--- | :--- |
| `Accept` | `application/json` | Di semua request. Tanpa header ini, server bisa membalas dengan halaman HTML. |
| `Content-Type` | `application/json` | Di request yang mengirim body (POST, PUT, PATCH). |
| `Authorization` | `Bearer <token>` | Di semua endpoint, kecuali register dan login. |

### Hal yang berlaku di semua endpoint

- Daftar data ditampilkan per halaman. Kalau tidak diatur, server menampilkan halaman 1 berisi 10 data. Atur dengan query parameter `page` (nomor halaman) dan `per_page` (jumlah data per halaman).
- PUT dan PATCH bekerja sama persis: hanya field yang dikirim yang berubah, field lain tetap.
- Tanggal dikirim dengan format `TTTT-BB-HH`, misalnya `2026-10-22`. Di respons, server menulis tanggal lengkap dengan jam, misalnya `2026-10-22T00:00:00.000000Z`.
- Kalau ID yang dicari tidak ada, server membalas `404 Not Found`.

## Autentikasi

| Di Postman | Method | Endpoint | Butuh token | Fungsi |
| :--- | :--- | :--- | :--- | :--- |
| REGISTER | POST | `/api/auth/register` | Tidak | Membuat akun baru |
| LOGIN | POST | `/api/auth/login` | Tidak | Masuk dan mendapat token |
| ME | GET | `/api/auth/me` | Ya | Melihat akun yang sedang login |
| REFRESH | POST | `/api/auth/refresh` | Ya | Memperbarui Token |
| LOGOUT | POST | `/api/auth/logout` | Ya | Mematikan token |

Token berlaku 60 menit. Panggil refresh sebelum waktunya habis untuk mendapat token baru. Kalau sudah terlanjur habis, login lagi. Lama berlakunya bisa diubah lewat `JWT_TTL` di `.env`, dalam hitungan menit.

### Register

`POST /api/auth/register`

Membuat akun baru. Kalau berhasil, Anda langsung mendapat token, jadi tidak perlu login lagi.

Aturan isian: `name` wajib diisi, `email` harus belum terdaftar, dan `password` minimal 6 karakter.

```json
{
  "name": "User Baru",
  "email": "userbaru@holywings.com",
  "password": "password123"
}
```

Kalau berhasil (`201 Created`):

```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

`expires_in` adalah sisa umur token dalam detik. 3600 detik sama dengan 60 menit.

### Login

`POST /api/auth/login`

```json
{
  "email": "admin@holywings.com",
  "password": "adminpusat"
}
```

Kalau berhasil (`200 OK`):

```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

Kalau email atau password salah (`401 Unauthorized`):

```json
{
  "message": "Unauthorized"
}
```

### Me

`GET /api/auth/me`

Menampilkan data akun yang sedang login. Password tidak ikut ditampilkan.

Kalau berhasil (`200 OK`):

```json
{
  "id": 1,
  "name": "Admin",
  "email": "admin@holywings.com",
  "email_verified_at": null,
  "created_at": "2026-10-22T12:00:00.000000Z",
  "updated_at": "2026-10-22T12:00:00.000000Z"
}
```

### Refresh

`POST /api/auth/refresh`

Memperbarui token yang masih berlaku dengan token baru. Setelah itu, pakai token yang baru.

Kalau berhasil (`200 OK`):

```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.token_baru...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Logout

`POST /api/auth/logout`

Mematikan token yang sedang dipakai. Setelah logout, token itu ditolak server dan Anda perlu login lagi.

Kalau berhasil (`200 OK`):

```json
{
  "message": "Successfully logged out"
}
```

## Kategori buku

Kategori dipakai untuk mengelompokkan buku. Nama kategori tidak boleh kembar.

| Di Postman | Method | Endpoint | Fungsi |
| :--- | :--- | :--- | :--- |
| GET ALL | GET | `/api/kategori-buku` | Melihat daftar kategori |
| GET SINGLE | GET | `/api/kategori-buku/{id}` | Melihat satu kategori beserta bukunya |
| CREATE | POST | `/api/kategori-buku` | Menambah kategori |
| UPDATE - PUT | PUT | `/api/kategori-buku/{id}` | Mengganti semua data field kategori |
| UPDATE - PATCH | PATCH | `/api/kategori-buku/{id}` | Mengganti sebagian data field kategori |
| DELETE | DELETE | `/api/kategori-buku/{id}` | Menghapus kategori |

### Lihat daftar kategori

`GET /api/kategori-buku`

Contoh: `http://127.0.0.1:8000/api/kategori-buku?page=1&per_page=10`

Kalau berhasil (`200 OK`). Contoh ini sudah dipotong. Respons aslinya juga memuat info halaman seperti `last_page`, `per_page`, dan `next_page_url`.

```json
{
  "message": "Berhasil mengambil daftar kategori buku",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "nama": "Fiksi",
        "deskripsi": "Karya sastra imajinatif seperti novel dan cerpen.",
        "created_at": "2026-10-22T12:00:00.000000Z",
        "updated_at": "2026-10-22T12:00:00.000000Z"
      }
    ],
    "total": 5
  }
}
```

### Lihat satu kategori

`GET /api/kategori-buku/{id}`

Menampilkan satu kategori beserta semua bukunya. Daftar bukunya ada di field `buku`.

Contoh: `http://127.0.0.1:8000/api/kategori-buku/1`

Kalau berhasil (`200 OK`):

```json
{
  "message": "Berhasil mengambil detail kategori buku",
  "data": {
    "id": 1,
    "nama": "Fiksi",
    "deskripsi": "Karya sastra imajinatif seperti novel dan cerpen.",
    "created_at": "2026-10-22T12:00:00.000000Z",
    "updated_at": "2026-10-22T12:00:00.000000Z",
    "buku": [
      {
        "id": 1,
        "kategori_buku_id": 1,
        "judul": "Laskar Pelangi",
        "penulis": "Andrea Hirata",
        "penerbit": "Bentang Pustaka",
        "tahun_terbit": 2005,
        "deskripsi": "Novel tentang kehidupan 10 anak di Belitung yang penuh semangat belajar.",
        "stok": 9,
        "created_at": "2026-10-22T12:00:00.000000Z",
        "updated_at": "2026-10-22T12:00:00.000000Z"
      }
    ]
  }
}
```

Kalau ID tidak ada (`404 Not Found`):

```json
{
  "message": "Kategori buku tidak ditemukan"
}
```

### Tambah kategori

`POST /api/kategori-buku`

Aturan isian: `nama` wajib diisi, maksimal 255 karakter, dan belum dipakai kategori lain. `deskripsi` boleh dikosongkan.

```json
{
  "nama": "Teknologi",
  "deskripsi": "Buku seputar komputer, teknologi, dan kecerdasan buatan."
}
```

Kalau berhasil (`201 Created`):

```json
{
  "message": "Kategori buku berhasil ditambahkan",
  "data": {
    "id": 6,
    "nama": "Teknologi",
    "deskripsi": "Buku seputar komputer, teknologi, dan kecerdasan buatan.",
    "created_at": "2026-10-22T12:00:00.000000Z",
    "updated_at": "2026-10-22T12:00:00.000000Z"
  }
}
```

### Ubah kategori

`PUT /api/kategori-buku/{id}` atau `PATCH /api/kategori-buku/{id}`

Aturan isiannya sama dengan tambah kategori. Di Postman, UPDATE - PUT mengirim semua field:

```json
{
  "nama": "Sains & Teknologi",
  "deskripsi": "Buku referensi sains modern dan teknologi terkini."
}
```

Sedangkan UPDATE - PATCH hanya mengganti sebagian data:

```json
{
  "nama": "Sastra & Fiksi Ilmiah"
}
```

Kalau keduanya dijalankan berurutan, hasil akhirnya seperti ini (`200 OK`):

```json
{
  "message": "Kategori buku berhasil diperbarui",
  "data": {
    "id": 1,
    "nama": "Sastra & Fiksi Ilmiah",
    "deskripsi": "Buku referensi sains modern dan teknologi terkini.",
    "created_at": "2026-10-22T12:00:00.000000Z",
    "updated_at": "2026-10-22T12:10:00.000000Z"
  }
}
```

### Hapus kategori

`DELETE /api/kategori-buku/{id}`

Menghapus kategori secara permanen. Buku di dalamnya tetap ada, hanya saja tidak lagi punya kategori (`kategori_buku_id` menjadi `null`).

Kalau berhasil (`200 OK`):

```json
{
  "message": "Kategori buku berhasil dihapus"
}
```

## Buku

Setiap buku punya stok dan boleh masuk ke satu kategori.

| Di Postman | Method | Endpoint | Fungsi |
| :--- | :--- | :--- | :--- |
| GET ALL | GET | `/api/buku` | Melihat daftar buku |
| GET SINGLE | GET | `/api/buku/{id}` | Melihat satu buku beserta kategorinya |
| CREATE | POST | `/api/buku` | Menambah buku |
| UPDATE - PUT | PUT | `/api/buku/{id}` | Mengganti semua data field buku |
| UPDATE - PATCH | PATCH | `/api/buku/{id}` | Mengganti sebagian data field buku |
| DELETE | DELETE | `/api/buku/{id}` | Menghapus buku |

### Lihat daftar buku

`GET /api/buku`

Contoh: `http://127.0.0.1:8000/api/buku?page=1&per_page=10`

Setiap buku ditampilkan bersama kategorinya di field `kategori`. Kalau berhasil, server membalas `200 OK` dengan pesan `Berhasil mengambil daftar buku`.

### Lihat satu buku

`GET /api/buku/{id}`

Menampilkan satu buku bersama kategorinya.

Kalau ID tidak ada (`404 Not Found`):

```json
{
  "message": "Buku tidak ditemukan"
}
```

### Tambah buku

`POST /api/buku`

| Field | Aturan |
| :--- | :--- |
| `judul` | Wajib, maksimal 255 karakter |
| `penulis` | Wajib, maksimal 255 karakter |
| `penerbit` | Wajib, maksimal 255 karakter |
| `tahun_terbit` | Wajib, berupa angka bulat |
| `kategori_buku_id` | Boleh kosong. Kalau diisi, kategorinya harus ada. |
| `deskripsi` | Boleh kosong |
| `stok` | Boleh kosong, nilainya jadi 1. Kalau diisi, minimal 0. |

```json
{
  "kategori_buku_id": 1,
  "judul": "Sapiens: Riwayat Singkat Umat Manusia",
  "penulis": "Yuval Noah Harari",
  "penerbit": "Kepustakaan Populer Gramedia",
  "tahun_terbit": 2011,
  "deskripsi": "Eksplorasi sejarah dan evolusi manusia.",
  "stok": 10
}
```

Kalau berhasil (`201 Created`):

```json
{
  "message": "Buku berhasil ditambahkan",
  "data": {
    "id": 6,
    "kategori_buku_id": 1,
    "judul": "Sapiens: Riwayat Singkat Umat Manusia",
    "penulis": "Yuval Noah Harari",
    "penerbit": "Kepustakaan Populer Gramedia",
    "tahun_terbit": 2011,
    "deskripsi": "Eksplorasi sejarah dan evolusi manusia.",
    "stok": 10,
    "created_at": "2026-10-22T12:00:00.000000Z",
    "updated_at": "2026-10-22T12:00:00.000000Z"
  }
}
```

### Ubah buku

`PUT /api/buku/{id}` atau `PATCH /api/buku/{id}`

Aturan isiannya sama dengan tambah buku. Di Postman, UPDATE - PUT mengirim semua field:

```json
{
  "kategori_buku_id": 1,
  "judul": "Sapiens: Edisi Grafis",
  "penulis": "Yuval Noah Harari",
  "penerbit": "Kepustakaan Populer Gramedia",
  "tahun_terbit": 2020,
  "deskripsi": "Adaptasi komik grafis dari buku Sapiens.",
  "stok": 12
}
```

Sedangkan UPDATE - PATCH hanya mengganti sebagian data:

```json
{
  "judul": "Sapiens (Revisi 2026)",
  "tahun_terbit": 2026,
  "stok": 15
}
```

Kalau berhasil, server membalas `200 OK` dengan pesan `Buku berhasil diperbarui` dan data buku terbaru.

### Hapus buku

`DELETE /api/buku/{id}`

Menghapus buku secara permanen. Semua catatan peminjaman untuk buku ini ikut terhapus.

Kalau berhasil (`200 OK`):

```json
{
  "message": "Buku berhasil dihapus"
}
```

## Peminjaman

Satu peminjaman mencatat siapa meminjam buku apa, kapan dipinjam, dan kapan dikembalikan. Statusnya selalu `dipinjam` atau `dikembalikan`.

Stok buku ikut berubah otomatis, jadi tidak perlu diubah sendiri lewat endpoint buku.

| Di Postman | Method | Endpoint | Fungsi |
| :--- | :--- | :--- | :--- |
| GET ALL | GET | `/api/peminjaman` | Melihat semua peminjaman |
| GET SINGLE | GET | `/api/peminjaman/{id}` | Melihat satu peminjaman |
| CREATE | POST | `/api/peminjaman` | Mencatat peminjaman baru |
| UPDATE - PUT | PUT | `/api/peminjaman/{id}` | Mengganti semua data field peminjaman |
| UPDATE - PATCH | PATCH | `/api/peminjaman/{id}` | Mengganti sebagian data field peminjaman |
| DELETE | DELETE | `/api/peminjaman/{id}` | Menghapus peminjaman |

### Lihat semua peminjaman

`GET /api/peminjaman`

Contoh: `http://127.0.0.1:8000/api/peminjaman?page=1&per_page=10`

Yang terbaru tampil paling atas. Setiap peminjaman ditampilkan bersama peminjamnya (`user`: id, name, email) dan bukunya (`buku`: id, judul, penulis, stok). Kalau berhasil, server membalas `200 OK` dengan pesan `Berhasil mengambil daftar peminjaman`.

### Lihat satu peminjaman

`GET /api/peminjaman/{id}`

Menampilkan satu peminjaman bersama peminjam, buku, dan kategori bukunya.

Contoh untuk peminjaman ID 1 dari data contoh (`200 OK`):

```json
{
  "message": "Berhasil mengambil detail peminjaman",
  "data": {
    "id": 1,
    "user_id": 1,
    "buku_id": 1,
    "tanggal_pinjam": "2026-10-20T00:00:00.000000Z",
    "tanggal_kembali": null,
    "status": "dipinjam",
    "created_at": "2026-10-22T12:00:00.000000Z",
    "updated_at": "2026-10-22T12:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@holywings.com"
    },
    "buku": {
      "id": 1,
      "kategori_buku_id": 1,
      "judul": "Laskar Pelangi",
      "penulis": "Andrea Hirata",
      "penerbit": "Bentang Pustaka",
      "tahun_terbit": 2005,
      "deskripsi": "Novel tentang kehidupan 10 anak di Belitung yang penuh semangat belajar.",
      "stok": 9,
      "created_at": "2026-10-22T12:00:00.000000Z",
      "updated_at": "2026-10-22T12:00:00.000000Z",
      "kategori": {
        "id": 1,
        "nama": "Fiksi",
        "deskripsi": "Karya sastra imajinatif seperti novel dan cerpen.",
        "created_at": "2026-10-22T12:00:00.000000Z",
        "updated_at": "2026-10-22T12:00:00.000000Z"
      }
    }
  }
}
```

Kalau ID tidak ada (`404 Not Found`):

```json
{
  "message": "Data peminjaman tidak ditemukan"
}
```

### Catat peminjaman

`POST /api/peminjaman`

Mencatat peminjaman baru. Kalau statusnya `dipinjam`, stok buku langsung berkurang satu.

| Field | Aturan |
| :--- | :--- |
| `buku_id` | Wajib, bukunya harus ada |
| `tanggal_pinjam` | Wajib, berupa tanggal |
| `user_id` | Boleh kosong. Kalau kosong, peminjamnya adalah akun yang sedang login. |
| `tanggal_kembali` | Boleh kosong. Kalau diisi, tidak boleh lebih awal dari `tanggal_pinjam`. |
| `status` | Boleh kosong, nilainya jadi `dipinjam`. Pilihannya `dipinjam` atau `dikembalikan`. |

```json
{
  "buku_id": 1,
  "tanggal_pinjam": "2026-10-22",
  "status": "dipinjam"
}
```

Kalau berhasil, server membalas `201 Created` dengan pesan `Transaksi peminjaman berhasil dibuat`, beserta data peminjam dan bukunya.

Kalau stok buku sudah 0, peminjaman ditolak (`400 Bad Request`):

```json
{
  "message": "Stok buku habis, tidak dapat melakukan peminjaman"
}
```

### Ubah peminjaman

`PUT /api/peminjaman/{id}` atau `PATCH /api/peminjaman/{id}`

Field yang bisa diubah: `user_id`, `buku_id`, `tanggal_pinjam`, `tanggal_kembali`, dan `status`.

Di Postman, UPDATE - PUT mengirim semua field:

```json
{
  "buku_id": 1,
  "tanggal_pinjam": "2026-10-22",
  "tanggal_kembali": "2026-10-29",
  "status": "dikembalikan"
}
```

Sedangkan UPDATE - PATCH hanya mengganti sebagian data:

```json
{
  "status": "dikembalikan",
  "tanggal_kembali": "2026-10-29"
}
```

Kalau status atau bukunya diganti, stok ikut disesuaikan:

| Yang diubah | Akibatnya |
| :--- | :--- |
| Status `dipinjam` menjadi `dikembalikan` | Stok buku bertambah satu. Kalau `tanggal_kembali` masih kosong, diisi tanggal hari ini. |
| Status `dikembalikan` menjadi `dipinjam` | Stok buku berkurang satu dan `tanggal_kembali` dikosongkan. Ditolak kalau stok sudah 0. |
| Buku diganti, status tetap `dipinjam` | Stok buku lama bertambah satu, stok buku baru berkurang satu. Ditolak kalau stok buku baru sudah 0. |

Kalau berhasil, server membalas `200 OK` dengan pesan `Transaksi peminjaman berhasil diperbarui`, beserta data terbaru.

Kalau ditolak karena stok habis (`400 Bad Request`), pesannya salah satu dari ini:

- `Stok buku habis, tidak dapat mengubah status menjadi dipinjam`
- `Stok buku pengganti habis`

### Hapus peminjaman

`DELETE /api/peminjaman/{id}`

Menghapus catatan peminjaman. Kalau bukunya belum dikembalikan, stoknya ditambah satu dulu sebelum catatan dihapus.

Kalau berhasil (`200 OK`):

```json
{
  "message": "Transaksi peminjaman berhasil dihapus"
}
```

## Pengembalian buku

| Di Postman | Method | Endpoint | Fungsi |
| :--- | :--- | :--- | :--- |
| PENGEMBALIAN BUKU | POST | `/api/peminjaman/{id}/kembalikan` | Mencatat buku sudah dikembalikan |

Dipakai saat peminjam mengembalikan buku. Status peminjaman berubah menjadi `dikembalikan`, dan stok buku bertambah satu.

Angka di URL adalah ID peminjaman, bukan ID buku.

Body boleh dikirim, boleh juga tidak:

```json
{
  "tanggal_kembali": "2026-10-22"
}
```

Kalau `tanggal_kembali` tidak dikirim, server mengisinya dengan tanggal hari ini.

Kalau berhasil, server membalas `200 OK` dengan pesan `Buku berhasil dikembalikan`, beserta data peminjaman terbaru.

Kalau buku itu sudah dikembalikan sebelumnya (`400 Bad Request`):

```json
{
  "message": "Buku sudah dikembalikan sebelumnya"
}
```

## Buku yang sedang dipinjam

| Di Postman | Method | Endpoint | Fungsi |
| :--- | :--- | :--- | :--- |
| DAFTAR BUKU DIPINJAM | GET | `/api/peminjaman/dipinjam` | Melihat peminjaman yang bukunya belum kembali |

Menampilkan peminjaman yang masih berstatus `dipinjam`, yang terbaru paling atas. Setiap data ditampilkan bersama peminjam, buku, dan kategori bukunya.

Contoh: `http://127.0.0.1:8000/api/peminjaman/dipinjam?page=1&per_page=10`

Selain `page` dan `per_page`, ada query parameter `user_id` untuk melihat pinjaman milik satu orang saja, misalnya `?user_id=1`. Parameter ini belum ada di koleksi Postman, jadi tambahkan sendiri kalau perlu.

Kalau berhasil, server membalas `200 OK` dengan pesan `Berhasil mengambil daftar buku yang sedang dipinjam`. Dengan data contoh, hasilnya dua peminjaman: ID 3 dan ID 1.

## Arti kode status

| Kode | Artinya |
| :--- | :--- |
| `200 OK` | Berhasil. |
| `201 Created` | Data baru berhasil disimpan. Muncul di register, tambah kategori, tambah buku, dan catat peminjaman. |
| `400 Bad Request` | Ditolak karena kondisi data, misalnya stok buku habis atau buku sudah dikembalikan. |
| `401 Unauthorized` | Token tidak dikirim, salah, sudah habis, atau sudah di-logout. Bisa juga karena email atau password salah saat login. |
| `404 Not Found` | Data atau alamat yang dicari tidak ada. |
| `422 Unprocessable Content` | Isian tidak memenuhi aturan, misalnya email sudah terdaftar, password kurang dari 6 karakter, atau field wajib masih kosong. |
| `500 Internal Server Error` | Ada masalah di server atau database. |

## Petunjuk Penanganan Respon Error

### Server membalas "Unauthenticated" atau "Unauthorized"

Artinya server tidak menerima token Anda. Penyebab yang paling sering:

- Belum login, atau token sudah lewat 60 menit. Jalankan LOGIN lagi.
- Baru saja logout. Token lama sudah dimatikan, jadi login lagi.
- Header `Authorization: Bearer <token>` tidak ikut terkirim.

Kalau pesan ini muncul saat LOGIN, berarti email atau password-nya salah.

Di Postman, token hanya disimpan kalau LOGIN berhasil dengan status 200 (untuk REGISTER, status 201). Kalau `jwt_token` masih kosong, cek dulu apakah LOGIN benar-benar berhasil.

### CREATE kategori gagal dengan `422` saat dijalankan kedua kali

Nama kategori tidak boleh kembar, sedangkan CREATE di Postman selalu mengirim nama "Teknologi". Ganti `nama` di body sebelum menjalankannya lagi.

### CREATE peminjaman gagal dengan `400`

Stok buku yang mau dipinjam sudah 0. Cek stoknya lewat GET SINGLE di folder BUKU. Pilih buku lain, atau tambah stoknya lewat UPDATE - PATCH di folder BUKU.

### PENGEMBALIAN BUKU gagal dengan `400`

Peminjaman itu sudah berstatus `dikembalikan`. Ini biasanya terjadi kalau UPDATE - PUT atau UPDATE - PATCH di folder PEMINJAMAN sudah dijalankan lebih dulu, karena keduanya juga mengubah peminjaman ID 1 menjadi `dikembalikan`.

Pakai peminjaman lain yang masih `dipinjam`, misalnya ID 3 dari data contoh. Bisa juga buat peminjaman baru lewat CREATE.

### Server membalas halaman HTML, bukan JSON

Tambahkan header `Accept: application/json`.

### `migrate` gagal tersambung ke database

- Cek apakah PostgreSQL sedang berjalan.
- Cek isi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`, dan pastikan tanda `#` di depannya sudah dihapus.
- Cek apakah database dengan nama itu sudah dibuat.

### Muncul peringatan JWT saat login

JWT secret belum dibuat. Jalankan `php artisan jwt:secret` di folder proyek.
