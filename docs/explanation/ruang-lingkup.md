# Ruang lingkup dan spesifikasi

Dokumen ini menjelaskan apa yang dikerjakan tweetUSK, bagaimana datanya disusun, dan aturan yang berlaku lintas fitur. Isinya menggambarkan aplikasi yang ada di repositori ini, bukan rencana ke depan.

## Ringkasan

Satu aplikasi, satu tata letak responsif. Post, balasan, kutipan, dan repost disimpan pada satu tabel sehingga feed, suka, dan penghapusan memakai jalur yang sama. Karakter Threads diwakili oleh penyambungan utas di kotak tulis; karakter X diwakili oleh repost, kutipan, dan tab feed `Ikuti`.

Bahasa antarmuka Indonesia. Nama variabel, kolom, route, dan kelas bahasa Inggris. Komentar serta dokumentasi bahasa Indonesia.

## Tumpukan

| Bagian | Pilihan |
| --- | --- |
| Framework | Laravel 13, PHP 8.3 |
| Interaksi | Livewire 4 komponen kelas, dengan Volt untuk halaman autentikasi |
| Komponen UI | Flux UI 2 dan Tailwind CSS v4 |
| Database | MySQL 8, charset `utf8mb4` |
| Pengujian | PHPUnit 12 |
| Formatter | Laravel Pint, preset `laravel` |

## Model data

```
users
  id, name, username (unik), email (unik), password
  bio, location, website, avatar_path, banner_path
  email_verified_at, remember_token, timestamps

posts
  id, user_id -> users, type enum(tweet, reply, repost, quote)
  parent_id -> posts (nullable), body (nullable)
  repost_key (nullable, unik), edited_at (nullable)
  timestamps, deleted_at

media
  id, post_id -> posts, path, position

hashtags
  id, tag (unik, huruf kecil tanpa tanda #)

hashtag_post
  post_id, hashtag_id

post_likes
  id, post_id, user_id, timestamps

follows
  id, follower_id, following_id, timestamps
```

Repost menyimpan `repost_key` berisi `user_id:parent_id` yang diisi model saat menyimpan. Kolom itu diberi indeks unik, sehingga satu pengguna hanya dapat merepost satu post satu kali. Kunci tidak diisi untuk tipe lain, dan MySQL mengizinkan banyak `NULL` pada indeks unik.

## Aturan per tipe post

| Tipe | `parent_id` | Body | Gambar |
| --- | --- | --- | --- |
| `tweet` | harus kosong | wajib | boleh |
| `reply` | wajib | wajib | boleh |
| `quote` | wajib | wajib | boleh |
| `repost` | wajib | harus kosong | tidak |

Kombinasi yang melanggar ditolak `ValidatesPostInput::pastikanKombinasi()`, bukan oleh skema. Skema sengaja longgar karena satu tabel menampung empat tipe.

## Penghapusan

Semua post memakai `SoftDeletes`. Menghapus post tidak menghapus balasan orang lain. Relasi `Post::parent()` memakai `withTrashed()`, sehingga balasan yang induknya sudah dihapus tetap menampilkan "Post induk sudah dihapus." alih-alih galat. Karena relasi itu juga mengembalikan post yang sudah ditarik, kartu memeriksa `trashed()` lebih dulu: isi post terhapus tidak muncul kembali lewat balasan atau repost orang lain.

Repost yang dibatalkan dihapus permanen dengan `forceDelete()`. Baris yang terhapus lunak tetap menempati `repost_key`, sehingga merepost post yang sama untuk kedua kalinya akan gagal bila baris lama hanya dihapus lunak.

Menghapus post ikut membuang baris media dan berkas gambarnya. Hook `deleting` pada `Post` menghapus media satu per satu supaya hook `deleted` di `Media` sempat membuang berkasnya; penghapusan massal sengaja dihindari karena melewati event model.

## Arsitektur kode

Halaman adalah komponen Livewire satu halaman, bukan controller.

```
routes/web.php  ->  komponen Livewire
                      -> validasi dan authorize() di komponen
                      -> kelas App\Support untuk logika bersama
                      -> model dan query scope
                      -> Blade, Flux, dan komponen Blade
```

Batasan yang dipegang: tidak ada query di view, tidak ada aturan validasi yang disalin ke beberapa tempat, dan tidak ada logika bisnis di model.

| Lapisan | Isi |
| --- | --- |
| `app/Enums` | `PostType` |
| `app/Livewire` | `Timeline`, `Thread`, `Composer`, `EditPost`, `ProfilePage`, `SearchPosts`, `Activities`, `WhoToFollow`, `FollowButton` |
| `app/Livewire/Concerns` | `TogglesLike`, `TogglesRepost`, `DeletesPost`, `ValidatesPostInput` |
| `app/Support` | `PostWriter`, `PostCardStatus`, `HashtagSync`, `MediaUploader` |
| `app/Policies` | `PostPolicy` untuk `update` dan `delete` |

## Route

| URI | Nama | Komponen |
| --- | --- | --- |
| `/` | `home` | `Timeline` |
| `/posts/{post}` | `posts.show` | `Thread` |
| `/posts/{post}/edit` | `posts.edit` | `EditPost` |
| `/cari` | `search` | `SearchPosts` |
| `/tag/{tag}` | `tag.show` | `SearchPosts` |
| `/aktivitas` | `activities` | `Activities` |
| `/dashboard` | `dashboard` | pengalihan ke `/` |
| `/settings/profile` | `settings.profile` | `Settings\Profile` |
| `/settings/password` | `settings.password` | Volt |
| `/settings/appearance` | `settings.appearance` | Volt |
| `/{user}` | `profile.show` | `ProfilePage` |
| `/login`, `/register`, dan rute auth lain |  | Volt |

Daftar rute lengkap beserta catatan urutannya ada di [reference/rute-dan-komponen.md](../reference/rute-dan-komponen.md).

## Spesifikasi fitur

**Autentikasi.** Memakai auth bawaan starter kit Livewire. Pendaftaran menambah kolom `username`: 3 sampai 20 karakter, `^[a-z0-9_]+$`, unik. Semua halaman kecuali halaman auth memerlukan login, termasuk profil publik.

**Profil.** Kolom `name`, `username`, `bio`, `location`, `website`, avatar, dan banner. Avatar dan banner disimpan di disk `public`. Profil publik menampilkan avatar, banner, nama, username, bio, lokasi, tautan website, tanggal bergabung, jumlah pengikut dan mengikuti, serta tombol ikuti atau tombol edit bila profil itu milik sendiri.

**Membuat post.** Satu kotak tulis dipakai untuk tweet, balasan, dan kutipan. Mode ditentukan lewat parameter `tipe` saat komponen dipasang, atau lewat parameter `kutip` di halaman utas. Isi dan gambar disimpan oleh `PostWriter` dalam satu transaksi.

**Utas.** Setelah tweet terkirim, kotak tulis tetap terbuka dan kiriman berikutnya menjadi balasan ke kiriman sebelumnya. Tombol Selesai mengakhiri rantai. Balasan di halaman utas tidak berantai; kotak balas di sana tetap membalas post yang dibuka.

**Feed.** Urut terbaru, berisi tweet, kutipan, dan repost. Tab `Ikuti` menyaring ke akun yang diikuti ditambah post sendiri. Pemuatan berikutnya menambah `$batas` sebesar sepuluh saat penanda di bawah daftar terlihat, bukan mengganti halaman.

**Halaman utas.** Menampilkan rantai induk maksimal lima level di atas post yang dibuka, lalu balasan urut terlama ke terbaru. Baris hitungan balasan, repost, dan suka berada di bawah post utama.

**Repost dan kutipan.** Repost dan kutipan dibuka dari menu pada bilah aksi. Repost bersifat sakelar; kutipan menyimpan teks baru dengan post asli sebagai lampiran dan butuh teks.

**Suka.** Sakelar per pengguna. Indeks unik `post_likes(post_id, user_id)` mencegah suka ganda di tingkat database.

**Ikuti.** Satu tombol dua keadaan. Mengikuti diri sendiri ditolak di dalam method, bukan sekadar disembunyikan dari tampilan. Setelah berhasil, komponen mengirim `follow-changed` sehingga halaman profil menghitung ulang jumlah pengikut.

**Pencarian.** `/cari?q=` menerima kata biasa maupun `#tag`. Kata berawalan `#` dicari lewat relasi hashtag; kata biasa dicocokkan ke isi post, nama, dan username. `/tag/{tag}` menampilkan semua post bertag tersebut dengan pagination.

**Edit dan hapus.** Hanya penulisnya, diperiksa `PostPolicy`. Edit mengubah body, menyinkronkan hashtag, dan mengisi `edited_at` hanya bila isi benar-benar berubah. Repost tidak dapat diedit karena tidak punya teks sendiri. Hapus memakai soft delete dan tersedia dari menu kartu maupun halaman edit.

**Profil bertab.** Postingan, Balasan, Media, Suka, Pengikut, dan Mengikuti. Tab disimpan di URL lewat `?tab=`.

**Aktivitas.** Gabungan suka, balasan, repost, dan pengikut baru pada post serta akun sendiri, diurutkan mundur dan dibatasi tiga puluh baris terbaru.

## Aturan lintas fitur

**Batas 250 karakter.** Dihitung dengan `grapheme_strlen()` setelah spasi di ujung dipangkas, sehingga emoji keluarga dihitung satu karakter. Aturan `max` bawaan Laravel tidak dipakai karena menghitung karakter, bukan grapheme.

**Hashtag.** `HashtagSync` mengambil tag dari body dengan `/(?<![\w#])#([\p{L}\p{N}_]{1,50})/u`, menurunkan huruf, membuang duplikat, lalu menyinkronkan relasi setiap kali post dibuat atau diubah.

**Sebutan.** `x-post-body` meng-escape teks lebih dulu, lalu menautkan hashtag dan `@username`. Escape sebelum penautan, bukan sesudahnya, agar isi post tidak dapat menyisipkan HTML.

**Gambar.** Maksimal empat gambar per post, masing-masing maksimal 5 MB, disimpan di disk `public` dengan nama acak hasil `store()`. Hanya tipe gambar yang diterima; lampiran berkas tidak didukung. Berkas yang bukan gambar atau melebihi batas ukuran dibuang sejak dipilih di kotak tulis, dengan pesan di bawah kotak tulis.

**Otorisasi.** Setiap aksi ubah dan hapus melewati `PostPolicy`. Aksi Livewire dapat datang tanpa melewati `mount()`, jadi pemeriksaan diulang di dalam method.

## Keamanan

Escaping Blade dipakai untuk semua keluaran. Mass assignment dibatasi `$fillable` dan data masuk lewat `$this->validate()` pada komponen. Livewire menangani CSRF pada setiap permintaan `wire:`. Nama berkas unggahan dibuat acak sehingga nama asli tidak pernah menjadi path. Lampiran yang tidak dapat dipratinjau tidak pernah dirender sebagai gambar. `{!! !!}` hanya dipakai untuk keluaran `x-post-body` yang sudah di-escape sebelum ditautkan.

## Rencana pengujian

Uji otomatis menyasar logika berisiko, bukan setiap baris. Sebagian besar memakai `Livewire::test()` karena logikanya berada di komponen.

Sudah tercakup: penyimpanan post beserta gambar dan hashtag, penolakan body di atas 250 grapheme, hitungan emoji, penolakan post kosong, penolakan gambar di atas 5 MB dan berkas non-gambar, batas tiga puluh kiriman per menit, pembersihan gambar saat post dihapus, isi post terhapus tidak tampil lewat repost, pengosongan formulir setelah kirim, feed dan rantai induk, penanda induk terhapus, balasan di bawah induk yang benar, aturan kutipan, sakelar repost, penolakan tweet berinduk, penyambungan utas, suka yang bertahan setelah muat ulang dan dapat dibatalkan, feed `Ikuti`, tombol ikuti, penolakan mengikuti diri sendiri, keenam tab profil, muat otomatis beranda, pencarian, hak akses edit dan hapus, serta tautan sebutan.

Belum tercakup dan layak ditambahkan: uji XSS pada body, uji batas laju lintas jendela waktu, dan uji beban untuk feed.

## Yang sengaja tidak dikerjakan

Notifikasi, pesan langsung, bookmark, jumlah tayangan, halaman tren, panel admin, WebSocket, unggah video, pemotong gambar, multi-bahasa antarmuka, API token, dan pengiriman email sungguhan (driver `log` saat pengembangan). Semuanya dapat ditambahkan tanpa membongkar skema.

## Risiko dan batasan

| Risiko | Sikap |
| --- | --- |
| Batas laju disimpan di cache | Tiga puluh kiriman per menit per akun, diperiksa di dalam `Composer::kirim()`. Dengan `CACHE_STORE=array` batas itu berlaku per proses, jadi produksi memakai cache bersama seperti `database` atau Redis |
| Soft delete menyisakan baris post | Disengaja. Isi dan gambarnya sudah tidak bisa dibaca atau diunduh |
| Jumlah balasan, repost, dan suka dihitung dengan query agregat per halaman | Memadai sampai puluhan ribu post; bila melambat, tambahkan kolom penghitung |
| Induk yang dihapus tidak dapat dibaca isinya | Disengaja agar post terhapus tidak bocor lewat balasannya |
| Komentar per baris membuat berkas lebih panjang | Diterima sebagai harga dari keterbacaan; komentar yang bertentangan dengan kode dianggap bug |
