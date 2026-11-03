# tweetUSK

Aplikasi media sosial bergaya X (dahulu Twitter) dengan nuansa Threads. Satu basis kode untuk menulis post, membalas, mengutip, merepost, menyukai, mengikuti akun lain, dan menelusuri hashtag.

Dibangun dengan Laravel 13, Livewire 4, Flux UI, Tailwind CSS v4, dan MySQL 8.

## Fitur

- **Post dan utas.** Menulis sampai 250 karakter; emoji dihitung satu karakter karena panjang diukur per grapheme. Satu post dapat membawa sampai empat lampiran (gambar, PDF, dokumen kantor, teks, CSV, atau ZIP) dengan batas 5 MB per berkas. Kiriman berikutnya dapat disambung menjadi utas.
- **Balasan, kutipan, dan repost.** Balasan hanya muncul di halaman induknya, kutipan membawa post lain sebagai lampiran, dan repost tidak menyimpan teks sendiri.
- **Suka dan ikuti.** Keduanya berlaku sebagai sakelar: klik kedua membatalkan. Feed punya tab `Semua` dan `Ikuti`.
- **Profil publik.** Dibuka lewat `/{username}` dengan tab Postingan, Balasan, Media, Suka, Pengikut, dan Mengikuti.
- **Pencarian.** Pencarian bebas pada isi post, nama, dan username; kata berawalan `#` dicari lewat relasi hashtag.
- **Aktivitas.** Halaman bergaya notifikasi X dengan tab `Semua` dan `Sebutan`. Tab pertama memuat aktivitas orang lain pada post serta akun sendiri sekaligus tindakan sendiri, tab kedua memuat post yang menyebut `@username` Anda.
- **Tren.** Widget di rail kanan berisi empat hashtag dengan jumlah post terbanyak, masing-masing menuju halaman tag.
- **Edit dan hapus.** Hanya penulisnya. Hapus bersifat lunak, sehingga balasan tetap dapat menampilkan konteks induk yang sudah dihapus.
- **Antarmuka.** Tata letak tiga kolom seperti web X dengan rail kanan berisi pencarian, tren, dan rekomendasi akun, navigasi bawah di ponsel, serta mode terang dan gelap. Halaman masuk dan daftar memakai panel merek di kiri dan formulir di kanan.

## Kebutuhan

| Perangkat | Versi |
| --- | --- |
| PHP | 8.3 sampai 8.5 |
| Composer | 2 |
| Node.js | 20 atau lebih baru |
| MySQL | 8, charset `utf8mb4` |

## Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Buat database `tweetusk` lebih dulu dengan charset `utf8mb4`, lalu sesuaikan blok `DB_*` pada `.env`. Petunjuk lengkap beserta penyebab kegagalan yang sering muncul ada di [docs/tutorial/instalasi.md](docs/tutorial/instalasi.md).

Untuk mengisi database dengan data contoh:

```bash
php artisan migrate:fresh --seed
```

Perintah itu mengosongkan database lebih dulu. Akun demo memakai kata sandi `password`; daftarnya ada di [docs/reference/rute-dan-komponen.md](docs/reference/rute-dan-komponen.md).

## Perintah

| Perintah | Kegunaan |
| --- | --- |
| `php artisan test` | Menjalankan seluruh tes |
| `./vendor/bin/pint` | Merapikan gaya kode |
| `php scripts/cek-komentar.php` | Memeriksa komentar per baris |
| `npm run dev` | Vite mode pengembangan |
| `npm run build` | Build aset untuk produksi |
| `php artisan migrate:fresh --seed` | Mengulang database dengan data demo |

Tiga perintah pertama adalah gerbang mutu proyek ini. Ketiganya harus lulus sebelum perubahan dianggap selesai.

## Struktur proyek

```
app/Enums                PostType
app/Livewire             komponen layar dan trait aksi bersama
app/Models               User, Post, Hashtag, Media
app/Policies             PostPolicy
app/Support              PostWriter, PostCardStatus, HashtagSync, MediaUploader
resources/views          layout, komponen Blade, dan view Livewire
scripts/cek-komentar.php penjaga komentar per baris
docs                     dokumentasi Diátaxis dan catatan keputusan
```

## Dokumentasi

Dokumentasi mengikuti pembagian [Diátaxis](https://diataxis.fr). Indeksnya ada di [docs/README.md](docs/README.md).

- [docs/tutorial/instalasi.md](docs/tutorial/instalasi.md): dari folder kosong sampai aplikasi berjalan
- [docs/how-to/menulis-thread-dan-kutipan.md](docs/how-to/menulis-thread-dan-kutipan.md): memakai kotak tulis untuk percakapan lebih dari satu post
- [docs/reference/rute-dan-komponen.md](docs/reference/rute-dan-komponen.md): rute, komponen, palet warna, dan perintah
- [docs/explanation/arsitektur.md](docs/explanation/arsitektur.md): alasan di balik bentuk kode
- [docs/explanation/ruang-lingkup.md](docs/explanation/ruang-lingkup.md): ruang lingkup, aturan lintas fitur, dan rencana pengujian
- [docs/explanation/alur-kerja.md](docs/explanation/alur-kerja.md): alur kerja pengembangan yang dipakai proyek ini
- [docs/adr](docs/adr): catatan keputusan arsitektur

## Kontribusi

Baca [CONTRIBUTING.md](CONTRIBUTING.md) sebelum mengirim perubahan. Ringkasnya: gerbang mutu harus lulus, komentar ditulis dalam bahasa Indonesia, dan penamaan kode memakai bahasa Inggris.

## Lisensi

MIT, mengikuti skeleton Laravel.
