# Instalasi

Dari folder kosong sampai aplikasi bisa dibuka di browser.

## Yang perlu terpasang

PHP 8.3 sampai 8.5, Composer 2, Node 20 atau lebih baru, dan MySQL 8 dengan charset `utf8mb4`. Versi yang dipakai saat proyek ini dibuat: PHP 8.3.36, Composer 2.8.12, Node 24, MySQL 8.4.3.

## Langkah

### 1. Ambil kode dan dependency

```bash
git clone <alamat-repositori> tweetUSK
cd tweetUSK
composer install
npm install
```

### 2. Siapkan database

```bash
mysql -u root -e "CREATE DATABASE tweetusk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Charset `utf8mb4` wajib. Dengan `utf8` biasa, emoji di body post akan gagal disimpan.

### 3. Siapkan environment

```bash
cp .env.example .env
php artisan key:generate
```

Untuk MySQL, isi bagian database di `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tweetusk
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi dan tautkan storage

```bash
php artisan migrate
php artisan storage:link
```

`storage:link` wajib. Tanpa itu, gambar post dan avatar tersimpan tetapi tidak bisa dibuka browser.

Kalau ingin feed langsung berisi percakapan contoh, jalankan `php artisan migrate:fresh --seed`. Perintah itu mengosongkan database lebih dulu, jadi jangan dipakai setelah ada data sungguhan. Akun demo dan kata sandinya ada di `docs/reference/rute-dan-komponen.md`.

### 5. Build aset

```bash
npm run build
```

Untuk pengembangan, jalankan `npm run dev` di terminal terpisah.

### 6. Jalankan

```bash
php artisan serve
```

Buka `http://localhost:8000`.

## Memastikan semuanya benar

```bash
php artisan test
./vendor/bin/pint
php scripts/cek-komentar.php
```

Ketiganya harus lulus. Kalau tes gagal karena tabel tidak ditemukan, kemungkinan `phpunit.xml` kehilangan atribut `force="true"` pada variabel `DB_CONNECTION`, sehingga tes memakai MySQL alih-alih sqlite in-memory.
