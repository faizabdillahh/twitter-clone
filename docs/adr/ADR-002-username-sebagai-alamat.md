# ADR-002: Username sebagai alamat profil

**Status:** diterima

## Konteks

Profil bisa diberi alamat `/users/{id}` atau `/{username}`. Alamat berbasis id stabil tetapi tidak bisa diingat dan tidak bisa dibagikan dengan enak dibaca.

## Keputusan

Profil dibuka lewat `/{username}`. `User::getRouteKeyName()` mengembalikan `username`, dan nama parameter rutenya tetap `user`.

## Alasan

Alamat yang bisa dibaca lebih berguna untuk dibagikan, dan aplikasi ini memang meniru Twitter yang memakai `@username` sebagai identitas.

## Konsekuensi

- Rute `/{user}` adalah pola penangkap apa pun, jadi harus didaftarkan paling akhir, setelah `require __DIR__.'/auth.php'`. Kalau tidak, `/login` dan `/register` ikut tertelan.
- Nama parameter harus `user` supaya cocok dengan `mount(User $user)`. Kalau berbeda, Livewire menerima model kosong dan halaman gagal saat memanggil `created_at->format()`.
- Username tidak boleh diubah menjadi angka saja di masa depan tanpa menambah awalan, karena bisa bentrok dengan rute lain.

## Alternatif yang ditolak

`/users/{id}`. Ditolak karena tautan profil menjadi tidak bisa ditebak isinya saat dibagikan.
