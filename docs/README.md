# Dokumentasi tweetUSK

Indeks dokumentasi. Pembagiannya mengikuti [Diátaxis](https://diataxis.fr), yaitu empat jenis dokumen dengan tujuan berbeda. Bila Anda baru mengenal proyek ini, mulai dari tutorial.

## Tutorial

Belajar dengan mengerjakan, dari kondisi kosong sampai berjalan.

- [instalasi.md](tutorial/instalasi.md) — menyiapkan PHP, Node, MySQL, dan menjalankan aplikasi sampai bisa dibuka di peramban

## How-to

Resep untuk satu tugas tertentu. Pembaca dianggap sudah bisa memakai aplikasi.

- [menulis-thread-dan-kutipan.md](how-to/menulis-thread-dan-kutipan.md) — menulis tweet, menyambung utas, membalas, mengutip, lalu menyunting atau menghapusnya

## Reference

Rujukan yang dibaca sambil bekerja, bukan dari awal sampai akhir.

- [rute-dan-komponen.md](reference/rute-dan-komponen.md) — daftar rute, komponen Livewire dan Blade, kelas pendukung, enum, palet warna, dan perintah

## Explanation

Alasan di balik bentuk kode dan cakupan aplikasi.

- [arsitektur.md](explanation/arsitektur.md) — mengapa satu tabel post, mengapa status tombol dihitung per halaman, dan mengapa penghapusan bersifat lunak
- [ruang-lingkup.md](explanation/ruang-lingkup.md) — cakupan fitur, model data, aturan lintas fitur, keamanan, dan rencana pengujian
- [alur-kerja.md](explanation/alur-kerja.md) — alur kerja pengembangan dan cara gerbang mutu ditegakkan

## Keputusan arsitektur

Catatan keputusan dalam format Nygard. Berkas yang sudah diterima tidak diubah.

- [ADR-001](adr/ADR-001-satu-tabel-post.md) — satu tabel `posts` untuk semua jenis kiriman
- [ADR-002](adr/ADR-002-username-sebagai-alamat.md) — username sebagai alamat profil

## Di luar direktori ini

- [README.md](../README.md) — gambaran proyek, kebutuhan, dan cara memasang
- [CONTRIBUTING.md](../CONTRIBUTING.md) — aturan kontribusi dan gerbang mutu
- [CHANGELOG.md](../CHANGELOG.md) — riwayat perubahan
- [AGENTS.md](../AGENTS.md) — petunjuk kerja untuk asisten kode yang bekerja di repositori ini
