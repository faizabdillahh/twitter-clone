# Alur kerja pengembangan

Catatan cara kerja yang dipakai membangun proyek ini. Ditulis agar dapat dibaca ulang, dinilai, lalu dipakai lagi di proyek berikutnya.

## Bentuk umum

Lima langkah, diulang per fase:

1. Siapkan alat dan periksa versi runtime.
2. Tetapkan ruang lingkup dalam satu pesan atau satu dokumen.
3. Tulis spesifikasi sebelum menulis kode, lalu review.
4. Kerjakan satu fase, berhenti di pemeriksaan lulus atau gagal.
5. Catat apa yang meleset, perbaiki aturannya, lanjut.

Dua hal yang membuat urutan ini bekerja: tidak ada kode ditulis sebelum spesifikasi disetujui, dan setiap fase punya cara membuktikan dirinya selesai.

## 1. Periksa runtime lebih dulu

`php -v`, `composer -V`, `mysql --version`, dan `node -v` dijalankan sebelum menentukan tumpukan. Laravel 13 membutuhkan PHP 8.3 sampai 8.5; rencana yang ditulis tanpa memeriksa itu akan batal pada baris pertama. Satu perintah di awal lebih murah daripada satu revisi rencana.

## 2. Tetapkan ruang lingkup

Satu dokumen berisi semuanya: fitur, tumpukan beserta versi, bahasa antarmuka, bahasa penamaan, bahasa komentar, batasan angka dan format, hal non-fungsional, dan cara verifikasi.

Bagian yang mahal bila terlupa: arti "komentar di setiap kelas". Kata itu dapat berarti komentar penjelas per baris atau docblock ringkas, dua pekerjaan yang sangat berbeda. Arti yang menentukan pekerjaan besar ditanyakan lebih dulu.

## 3. Spesifikasi sebelum kode

Bagian minimum yang terbukti dipakai proyek ini:

| Bagian | Kegunaan |
| --- | --- |
| Keputusan teknis dan sumbernya | Versi tidak ditebak |
| Model data | Satu tabel `posts` atau empat tabel menentukan semua query sesudahnya |
| Arsitektur dan route | Menentukan batas pekerjaan tiap fase |
| Spesifikasi per fitur | Menjadi definisi selesai, bukan ringkasan |
| Aturan lintas fitur | Batas karakter, hashtag, media, otorisasi |
| Keamanan | Diperiksa sekali di awal, bukan ditambal belakangan |
| Dokumentasi | Termasuk cara menegakkannya |
| Fase dan kriteria selesai | Agar berhenti di titik yang jelas |
| Rencana pengujian | Kasus batas yang harus ada |
| Yang sengaja tidak dikerjakan | Mencegah lingkup melebar diam-diam |
| Risiko dan mitigasi | Tempat menulis hal yang belum pasti |

Dokumentasi pustaka diperiksa langsung ke sumber resminya sebelum masuk spesifikasi. Contoh nyata: sebuah starter kit mencantumkan batas versi framework yang berbeda dari yang tertulis di README-nya. Membaca satu sumber saja menghasilkan rencana berisi komponen yang salah.

## 4. Review dan revisi

Revisi dilakukan per bagian, bukan dengan menulis ulang seluruh dokumen.

- Sebut bagian yang salah, bukan seluruh dokumen.
- Perbaiki di tempat, lalu minta daftar bagian yang tersentuh agar dapat diperiksa.
- Setelah perubahan besar, sisir ulang dokumen untuk sisa istilah lama. Saat arsitektur berubah dari controller ke komponen Livewire, satu kalimat masih menyebut pola lama setelah seluruh dokumen diubah.

## 5. Eksekusi per fase

Satu fase selesai bila tiga perintah ini lulus:

```bash
php artisan test
./vendor/bin/pint
php scripts/cek-komentar.php
```

Aturan yang hanya ditulis di dokumen akan bocor cepat atau lambat. Kalau satu aturan sudah dikoreksi dua kali, pindahkan ke alat yang memaksa, bukan ke kalimat yang lebih tebal. Itu sebabnya pemeriksa komentar ada meski Pint sudah berjalan.

## 6. Refleksi

Dijalankan di akhir setiap fase, dengan tiga pertanyaan:

1. Apa yang dikoreksi lebih dari sekali? Itu kandidat aturan baru atau skrip.
2. Bagian spesifikasi mana yang ternyata salah? Perbaiki dokumen dan ADR-nya, bukan hanya kodenya.
3. Apa yang ditunda? Masukkan ke daftar "sengaja tidak dikerjakan", jangan biarkan menjadi utang tanpa catatan.

## Pelajaran yang terbukti

| Masalah | Perbaikan |
| --- | --- |
| Ruang lingkup berubah di tengah jalan | Putuskan arsitektur interaktif sebelum menulis spesifikasi |
| Istilah "komentar" ambigu | Tanyakan arti kata yang menentukan pekerjaan besar |
| Dokumentasi dianggap satu berkas besar | Riset standar penulisan dokumentasi (Diátaxis, ADR) sebelum menuliskannya |
| Perintah salin dan hapus dijalankan bersamaan | Verifikasi tujuan lebih dulu, hapus sumber belakangan |
| Kode berlebih masuk tanpa disadari | Tetapkan batas: tidak menambah abstraksi, opsi, atau pustaka tanpa alasan |
| Aturan proyek hanya hidup di kepala penulis | Tulis di `AGENTS.md` dan tegakkan lewat skrip |

## Untuk kontributor baru

Baca [CONTRIBUTING.md](../../CONTRIBUTING.md) untuk aturan yang harus dipenuhi sebelum mengirim perubahan, lalu [ruang-lingkup.md](ruang-lingkup.md) untuk mengetahui batas aplikasi. Keduanya lebih penting daripada membaca seluruh kode.
