# Panduan kontribusi

Terima kasih tertarik pada tweetUSK. Dokumen ini menjelaskan aturan yang harus dipenuhi sebelum perubahan digabungkan.

## Sebelum mengirim perubahan

Tiga perintah ini harus lulus tanpa galat:

```bash
php artisan test
./vendor/bin/pint
php scripts/cek-komentar.php
```

Bila perubahan menyentuh tampilan, jalankan juga `npm run build` untuk memastikan aset masih dapat dibangun. CI menjalankan pemeriksaan yang sama: `pint --test` dan `cek-komentar.php`, ditambah seluruh tes.

## Gaya kode

- Komentar dan dokumentasi ditulis dalam bahasa Indonesia. Nama variabel, kolom, route, dan kelas memakai bahasa Inggris.
- Setiap baris pernyataan diberi komentar yang menjelaskan tujuan baris itu, bukan mengulang sintaksnya.
- Setiap kelas dan method public memiliki docblock berisi ringkasan, `@param`, dan `@return`. Tambahkan `@throws` bila method melempar pengecualian.
- Satu kelas satu tanggung jawab. Komponen Livewire dibuat dengan `php artisan make:livewire Nama --class`, bukan single-file component.
- Gaya mekanis diserahkan ke Pint. Jangan memperbaiki spasi atau urutan `use` secara manual.

## Komentar per baris

Pint tidak dapat memeriksa komentar, karena itu ada `scripts/cek-komentar.php`. Skrip ini memeriksa `app/`, `resources/js/`, dan `resources/views/`, serta melewati berkas bawaan starter kit yang belum diubah. Daftar berkas yang dilewati berada di dalam skrip; hapus satu baris dari daftar itu begitu berkasnya mulai diubah.

Baris yang sah tetapi ditandai keliru cukup diberi penanda di ujungnya:

```php
$query = $builder->toSql(); // cek-komentar: abaikan
```

Jalankan pemeriksa **setelah** Pint, karena Pint membetulkan akhir baris dan urutan `use` yang memengaruhi hasil pemeriksaan.

## Keputusan desain

Keputusan yang tidak terbaca dari kode ditulis sebagai satu berkas di [docs/adr](docs/adr), memakai format Nygard:

1. Judul dan status
2. Konteks
3. Keputusan
4. Alasan
5. Konsekuensi
6. Alternatif yang ditolak

Berkas yang sudah diterima tidak diubah. Keputusan yang berubah ditulis sebagai ADR baru yang menyebut ADR yang digantikannya.

## Dokumentasi

Dokumentasi mengikuti pembagian [Diátaxis](https://diataxis.fr): tutorial untuk belajar, how-to untuk tugas tertentu, reference untuk rujukan, dan explanation untuk alasan. Indeksnya ada di [docs/README.md](docs/README.md).

Perubahan pada perilaku aplikasi wajib disertai pembaruan dokumen yang bersangkutan dan satu baris di [CHANGELOG.md](CHANGELOG.md) pada bagian `Belum dirilis`.

## Menambah dependency

Proyek ini menahan diri dari penambahan pustaka. Sebelum menambah satu, pastikan:

1. Kemampuan itu belum ada di kode, di framework, atau di pustaka yang sudah terpasang.
2. Manfaatnya sepadan dengan biaya memelihara, meninjau, dan memperbarui pustaka itu.
3. Alternatif tanpa pustaka sudah dipertimbangkan dan ditolak dengan alasan.

Bila tetap ditambahkan, sebutkan alasan itu pada deskripsi perubahan.

## Melaporkan masalah

Sertakan langkah untuk mereproduksi, hasil yang diharapkan, hasil yang terjadi, dan versi PHP, Node, serta database yang dipakai. Untuk masalah tampilan, sebutkan peramban dan lebar layar.
