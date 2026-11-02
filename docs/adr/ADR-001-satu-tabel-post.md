# ADR-001: Satu tabel `posts` untuk semua jenis kiriman

**Status:** diterima

## Konteks

Aplikasi perlu tweet, balasan, kutipan, dan repost. Pilihan yang paling jelas ada dua: satu tabel dengan kolom `type`, atau tabel terpisah per jenis.

## Keputusan

Satu tabel `posts` dengan kolom `type` (enum `PostType`) dan `parent_id` untuk menunjuk post lain.

## Alasan

Feed beranda menampilkan campuran keempat jenis dalam satu urutan waktu. Dengan satu tabel itu satu query. Dengan tabel terpisah, setiap perubahan urutan feed menuntut `union` atau penggabungan di PHP, dan setiap fitur baru (suka, media, hashtag) harus digandakan di semua tabel.

## Konsekuensi

- Setiap query yang membaca feed harus memfilter tipe lewat `scopeFeed()`. Tanpa itu balasan ikut muncul di beranda.
- Repost tidak punya teks, jadi kolom `body` boleh kosong hanya untuk tipe itu.
- Kunci unik repost memakai kolom terpisah `repost_key` karena MySQL menganggap `NULL` berbeda, sehingga indeks unik pada `parent_id` saja tidak menahan repost ganda.

## Alternatif yang ditolak

Tabel `tweets`, `replies`, `quotes`, `reposts` terpisah. Ditolak karena memaksa penggabungan di setiap layar dan menyulitkan relasi suka serta media yang berlaku untuk semua jenis.
