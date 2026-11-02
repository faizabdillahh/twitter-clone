# Arsitektur

Bagaimana tweetUSK disusun dan mengapa beberapa keputusannya diambil seperti itu.

## Satu tabel `posts` untuk empat jenis kiriman

Tabel `posts` menyimpan tweet, balasan, kutipan, dan repost sekaligus. Jenisnya dibedakan kolom `type` (`App\Enums\PostType`), dan hubungan ke post lain lewat `parent_id`.

Konsekuensinya feed cukup satu query `whereIn('type', ...)` dan tidak perlu menggabungkan beberapa tabel. Harganya, setiap query harus sadar tipe: `scopeFeed()` hanya mengambil tweet, kutipan, dan repost, sementara balasan hanya muncul di halaman thread. Alasan lengkapnya ada di `docs/adr/ADR-001-satu-tabel-post.md`.

Repost tidak menyimpan teks sendiri. Barisnya hanya `type = repost` plus `parent_id`. Kolom `repost_key` diisi model saat menyimpan (`user_id:parent_id`) dan diberi indeks unik, jadi satu orang hanya bisa merepost satu post satu kali. Membatalkan repost memakai `forceDelete()`, bukan `delete()`, karena baris yang terhapus lunak tetap menempati kunci unik itu.

## Induk dibaca bersama yang sudah dihapus

`Post::parent()` memakai `withTrashed()`. Post yang dihapus hilang dari feed, tetapi balasannya masih bisa menampilkan `Post induk sudah dihapus.` alih-alih kosong atau error. Kolom serupa tidak dipakai untuk pengguna: akun tidak punya penghapusan lunak di versi ini.

Karena `withTrashed()` juga mengembalikan post yang sudah ditarik penulisnya, setiap tempat yang menampilkan induk memeriksa `trashed()` lebih dulu. Kartu post menampilkan keterangan tidak tersedia, kotak kutipan tidak dirender, dan rantai induk di halaman utas berhenti. Tanpa pemeriksaan itu, isi post yang sudah dihapus tetap terbaca lewat repost atau kutipan orang lain.

## Komponen Livewire memegang satu layar

| Layar | Komponen |
| --- | --- |
| Feed beranda | `Timeline` |
| Detail post dan balasan | `Thread` |
| Kotak tulis | `Composer` |
| Edit dan hapus | `EditPost` |
| Profil | `ProfilePage` |
| Pencarian dan tag | `SearchPosts` |
| Tombol ikuti | `FollowButton` |

Aksi yang dipakai di lebih dari satu layar tinggal di trait: `TogglesLike`, `TogglesRepost`, `DeletesPost`, dan `ValidatesPostInput`. Kartu post memanggil `wire:click` ke komponen yang membungkusnya, jadi trait ini yang membuat tombol yang sama bekerja di beranda, utas, profil, dan pencarian.

## Penulisan post lewat satu pintu

Semua post baru dibuat lewat `App\Support\PostWriter`. Di dalam satu transaksi, kelas ini menyimpan baris post, menyimpan gambar lewat `MediaUploader`, lalu menyelaraskan hashtag lewat `HashtagSync`. Karena hashtag hanya dibaca dari body yang benar-benar tersimpan, edit post juga memanggil `HashtagSync` dengan body baru.

Repost juga lewat `PostWriter`; yang berbeda hanya isi `type` dan `parent_id`.

## Status tombol dihitung sekali per layar

Kalau setiap kartu menanyakan "sudah aku suka belum?" sendiri, satu halaman berisi sepuluh kartu berarti tiga puluh query. `App\Support\PostCardStatus::untuk()` mengumpulkan id post di layar, menjalankan lima query agregat, lalu mengembalikan lima peta id ke nilai: `sudahRepost`, `sudahLike`, `jumlahLike`, `jumlahBalas`, dan `jumlahRepost`. Kartu hanya membaca peta itu.

Daftar id sengaja memuat `parent_id` juga, karena kartu repost dan kutipan menampilkan post aslinya beserta angkanya sendiri.

## Hapus post bersifat lunak

Menghapus post mengisi `deleted_at`. Feed berhenti menampilkannya, halaman detailnya menjadi 404, tetapi balasan dan kutipannya tetap punya induk yang bisa dibaca. Ini alasan `parent()` memakai `withTrashed()`.

## Indeks dibuat mengikuti bentuk query

Feed beranda mengurutkan `created_at` tanpa menyaring pengguna, jadi indeks `['user_id', 'created_at']` tidak menolongnya: kolom depan indeks itu tidak ada di klausa `where`. `posts.created_at` karenanya punya indeks sendiri. Halaman aktivitas menyaring `post_likes.post_id` dan `follows.following_id`, dua kolom yang bukan kolom depan primary key, jadi keduanya diindeks terpisah. Migrasi `2026_11_02_000007_add_feed_and_activity_indexes` yang menambahkannya.

Rollback migrasi itu melepas foreign key lebih dulu, baru menghapus indeksnya, lalu memasang kunci kembali. MySQL menolak `DROP INDEX` selama kunci asing masih bersandar pada indeks tersebut.

## Peta folder

```
app/Enums        PostType
app/Livewire     komponen layar dan trait aksi bersama
app/Models       User, Post, Hashtag, Media
app/Policies     PostPolicy (edit dan hapus hanya oleh penulis)
app/Support      PostWriter, PostCardStatus, HashtagSync, MediaUploader
resources/views/components        layout, kartu post, badan post, keadaan kosong
scripts/cek-komentar.php          penjaga komentar per baris
```
