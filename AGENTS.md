# AGENTS.md

Petunjuk kerja untuk asisten kode yang bekerja di repositori ini. Isinya aturan yang tidak terbaca dari kode: perintah yang menjadi gerbang, jebakan yang sudah pernah menggigit, dan konvensi yang disepakati.

## Perintah

- Tes: `php artisan test`
- Satu berkas tes: `php artisan test --filter=NamaTest`
- Format: `./vendor/bin/pint`
- Cek komentar: `php scripts/cek-komentar.php`
- Build aset: `npm run build`

Sebuah perubahan dianggap selesai kalau tiga perintah pertama lulus tanpa galat. Pint dijalankan **sebelum** `cek-komentar.php`, karena Pint membetulkan akhir baris dan urutan `use` yang memengaruhi hasil pemeriksaan.

## Jebakan proyek ini

- Rute `/{user}` harus terdaftar paling akhir di `routes/web.php`, setelah `require auth.php`. Bila didahulukan, pola itu menelan `/login` dan `/register`. Nama parameternya `user` agar cocok dengan `mount(User $user)`; nama lain membuat Livewire menerima model kosong.
- Aksi Livewire masuk lewat `/livewire/update`, bukan lewat rute halaman, sehingga `throttle:` pada rute tidak berlaku. Batas laju penulisan karena itu dipasang di dalam `Composer::kirim()` dengan `RateLimiter::attempt('tulis:'.Auth::id(), 30, ..., 60)`.
- `posts.repost_key` diisi model saat event `saving`, bukan manual di komponen. Nilainya unik per pasangan pengguna dan post induk.
- Relasi ke post induk selalu memakai `withTrashed()`. Tanpa itu balasan kehilangan konteks saat induknya dihapus. Setiap tempat yang menampilkan induk memeriksa `trashed()` lebih dulu agar isi post yang ditarik tidak bocor lewat balasan atau repost.
- Batas 250 karakter memakai `grapheme_strlen()`, bukan `strlen()`, karena emoji dihitung satu karakter.
- Layout berada di `resources/views/components/layouts`, dan `config/livewire.php` sengaja mengarahkan namespace `layouts` ke sana.
- `resources/views/components/layouts/app/sidebar.blade.php` adalah layout utuh: rail kiri, kolom tengah 600px, rail kanan 350px, dan navigasi bawah untuk ponsel. Layout itu sudah punya tag `<main>` sendiri, jadi `layouts/app.blade.php` hanya meneruskan slot tanpa `flux:main`; menambah `flux:main` menyisipkan padding ke seluruh kolom.
- Header aplikasi di ponsel menempati 53px pertama (`sticky top-0 z-30`), jadi header halaman di dalam kolom memakai `sticky top-[53px] sm:top-0`.
- View Blade memerlukan `{{-- --}}` pada setiap baris yang diperiksa, termasuk tag pembuka. Direktif `@if` dan tag penutup dilewati otomatis.
- `Storage::delete()` menolak argumen `null`. Bungkus path lama dengan `array_filter([$path])` saat mengganti berkas unggahan.
- Username diturunkan ke huruf kecil sebelum validasi. SQLite membandingkan peka huruf sedangkan MySQL tidak, jadi normalisasi di depan membuat keduanya berperilaku sama.
- Repost yang dibatalkan memakai `forceDelete()`. Soft delete menyisakan `repost_key` unik, sehingga repost ulang untuk post yang sama akan gagal.
- Menghapus post harus lewat model. Hook `deleting` di `Post` menghapus media satu per satu supaya hook `deleted` di `Media` sempat membuang berkasnya; `Post::where(...)->delete()` melewati event dan meninggalkan gambar yatim.
- Kombinasi jenis dan induk divalidasi di `ValidatesPostInput::pastikanKombinasi()`, bukan di skema. Menambah tipe post berarti menambah satu cabang di sana.
- Kutipan juga menyimpan `parent_id`, jadi setiap query balasan wajib menyaring `type = reply`. `Post::replies()` sudah melakukannya; jangan diganti `hasMany` polos.
- `x-post-body` meng-escape teks lebih dulu, baru menyisipkan tautan hashtag dan sebutan. Urutan sebaliknya membuka celah XSS. Regex sebutan memakai `(?<![\w@/])` agar alamat surel tidak ikut tertaut.
- Indeks pada kolom yang menopang foreign key (`post_likes.post_id`, `follows.following_id`) tidak dapat dihapus selama kuncinya ada. `down()` melepas kunci itu lebih dulu, menghapus indeks, lalu memasangnya kembali.
- Beranda memakai `$batas` dan `limit()`, bukan paginasi, karena penanda `x-intersect` menambah batas alih-alih mengganti halaman. Penanda hanya dirender saat `count === batas`; bila selalu dirender, halaman memanggil `muatLagi` terus-menerus.
- `DemoSeeder` memakai username dan email tetap, jadi `migrate:fresh --seed` harus dijalankan ulang, bukan `db:seed`, agar tidak bentrok.
- `Composer` menyambung utas dengan mengisi `parent` dan `tipe` setelah kirim, lalu `berhentiMerantai()` melepasnya kembali.

## Konvensi yang tidak terbaca dari kode

- Menu tiga titik pada `x-post-card` memanggil `hapus()` milik komponen induk, jadi setiap komponen yang menampilkan kartu wajib memakai trait `App\Livewire\Concerns\DeletesPost`. Tanpa itu Livewire gagal menemukan method-nya.
- `$flux.toast()` hanya tampil bila layout memiliki `<flux:toast />`. Elemen itu sudah dipasang sekali di `layouts/app/sidebar.blade.php`; jangan dipasang ulang per komponen.
- Aset masuk lewat `resources/css/app.css` saja. `resources/js/app.js` sudah dihapus karena kosong; menambah JavaScript berarti menambah entri di `vite.config.js` dan memanggilnya dari `partials/head.blade.php`.
- Berkas bawaan starter kit dikecualikan di `scripts/cek-komentar.php` lewat daftar `$warisan`. Hapus satu baris daftar itu begitu berkasnya diubah; `resources/views/partials/` sudah keluar dari daftar tersebut.
- Komentar dan dokumentasi bahasa Indonesia. Nama variabel, kolom, rute, dan kelas bahasa Inggris.
- Komentar per baris menjelaskan tujuan baris, bukan mengulang sintaks.
- Komponen Livewire dibuat dengan `php artisan make:livewire Nama --class`, bukan single-file component. Halaman autentikasi tetap memakai Volt dan itu disengaja.
- Suite tes hanya berisi `tests/Feature`. Menambah uji unit berarti menambah direktori `tests/Unit` beserta satu `<testsuite>` di `phpunit.xml`.
- Tidak menambah dependency tanpa alasan yang ditulis. Aturan lengkapnya ada di `CONTRIBUTING.md`.
