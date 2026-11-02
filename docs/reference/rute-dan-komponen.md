# Rute dan komponen

Daftar rute, komponen Livewire, kelas pendukung, dan perintah yang dipakai sehari-hari.

## Rute

Semua rute di bawah butuh login, kecuali yang ditandai publik.

| URI | Nama | Komponen | Catatan |
| --- | --- | --- | --- |
| `/` | `home` | `Timeline` | Query `?lingkup=semua\|ikuti` |
| `/posts/{post}` | `posts.show` | `Thread` | 404 bila post terhapus |
| `/posts/{post}/edit` | `posts.edit` | `EditPost` | 403 bila bukan penulis |
| `/cari` | `search` | `SearchPosts` | Query `?q=` |
| `/tag/{tag}` | `tag.show` | `SearchPosts` | Tag tanpa tanda `#` |
| `/aktivitas` | `activities` | `Activities` | Suka, balasan, repost, dan pengikut baru |
| `/dashboard` | `dashboard` | — | Dialihkan ke `/`, dipakai tujuan setelah daftar dan verifikasi email |
| `/settings/profile` | `settings.profile` | `Profile` |  |
| `/settings/password` | `settings.password` | Volt |  |
| `/settings/appearance` | `settings.appearance` | Volt |  |
| `/{user}` | `profile.show` | `ProfilePage` | Query `?tab=post\|balasan\|media\|suka\|pengikut\|mengikuti` |
| `/login`, `/register`, `/logout` |  | Laravel auth | Publik |

Rute `/{user}` didaftarkan **setelah** `require __DIR__.'/auth.php'`. Kalau didahulukan, pola itu menelan `/login` dan `/register`. Nama parameternya juga harus `user` karena `ProfilePage::mount(User $user)` mengikat berdasarkan nama.

## Komponen Livewire

| Komponen | Tugas | Method publik |
| --- | --- | --- |
| `Timeline` | Feed dua tab, muat otomatis per sepuluh post | `muatLagi`, `like`, `repost`, `hapus` |
| `Activities` | Aktivitas orang lain pada post dan akun sendiri | — |
| `WhoToFollow` | Tiga rekomendasi akun di rail kanan | — |
| `Thread` | Rantai induk, post yang dibuka, balasan, kotak balas | `like`, `repost`, `hapus` |
| `Composer` | Menulis tweet, balasan, kutipan, lanjutan thread | `kirim`, `updatedMedia`, `hapusMedia`, `berhentiMerantai` |
| `EditPost` | Mengubah isi dan menghapus post sendiri | `simpan`, `hapus` |
| `ProfilePage` | Profil publik dengan empat tab post dan dua tab daftar akun | `like`, `repost`, `hapus` |
| `SearchPosts` | Pencarian bebas dan halaman tag | `like`, `repost`, `hapus` |
| `FollowButton` | Tombol ikuti dengan keadaan awal | `ikuti` |

`hapus` pada `Timeline`, `Thread`, `ProfilePage`, dan `SearchPosts` berasal dari trait `App\Livewire\Concerns\DeletesPost`. Komponen baru yang menampilkan `x-post-card` wajib memakai trait itu, karena menu tiga titik di kartu memanggil method tersebut.

`Post::deleting` membuang baris media dan berkasnya setiap kali post dihapus. Penghapusan lewat query massal melewati hook itu dan meninggalkan gambar yatim.

## Kelas pendukung

| Kelas | Tanggung jawab |
| --- | --- |
| `App\Support\PostWriter` | Membuat post beserta media dan hashtag dalam satu transaksi |
| `App\Support\HashtagSync` | Menyelaraskan relasi hashtag dengan isi body |
| `App\Support\MediaUploader` | Menyimpan gambar dan mencatat urutannya |
| `App\Support\PostCardStatus` | Menghitung status tombol dan jumlah balasan, repost, serta suka untuk semua kartu di satu layar |
| `App\Policies\PostPolicy` | Aturan `update` dan `delete` |

## Komponen Blade

| Komponen | Tugas |
| --- | --- |
| `x-post-card` | Kartu post dengan menu tiga titik, menu repost dan bagikan, serta bilah aksi berikon |
| `x-post-body` | Mengubah hashtag dan sebutan `@username` menjadi tautan |
| `x-avatar` | Foto profil bulat atau inisial nama |
| `x-empty-state` | Judul besar dan satu kalimat penjelas untuk daftar kosong |
| `x-error-page` | Kode status besar, judul, penjelas, dan tautan kembali ke beranda untuk halaman galat |

`x-post-card` menerima tiga prop: `post` (model yang dirender), `status` (peta status dan hitungan dari `PostCardStatus`), dan `tampilkanInduk` (menampilkan baris "Membalas @user" pada kartu balasan). `x-empty-state` menerima `judul` dan `teks`. `x-error-page` menerima `kode`, `judul`, dan `pesan`.

## Halaman auth

Halaman auth memakai `x-layouts.auth`, yaitu kerangka dua kolom: panel merek gelap di kiri berisi lambang, nama aplikasi, dan satu kalimat penjelas; formulir di panel kanan selebar 400px. Panel merek hanya muncul mulai ukuran `lg`, jadi lambang versi ponsel disediakan di atas formulir. Isian memakai `flux:input` dengan label di atas, dan tombol kirim memakai `flux:button variant="primary"` selebar kolom. `x-auth-header` dipakai halaman lupa dan atur ulang sandi.

## Halaman galat

`resources/views/errors/` berisi `403`, `404`, `419`, `429`, `500`, dan `503`. Laravel memilih berkas itu otomatis dari kode status, tanpa konfigurasi tambahan. Semuanya memakai `x-layouts.auth` karena layout aplikasi menuntut pengguna yang login, sedangkan halaman galat harus tetap terbaca oleh tamu.

## Enum `PostType`

| Nilai | Kolom `type` | Body | Wajib `parent_id` |
| --- | --- | --- | --- |
| `Tweet` | `tweet` | ya | tidak |
| `Reply` | `reply` | ya | ya |
| `Quote` | `quote` | ya | ya |
| `Repost` | `repost` | tidak | ya |

## Perintah

```bash
php artisan test                        # seluruh tes
php artisan test --filter=PostTest      # satu berkas tes
./vendor/bin/pint                       # rapikan gaya kode
./vendor/bin/pint --test                # cek tanpa mengubah berkas
php scripts/cek-komentar.php            # pastikan setiap baris berkode berkomentar
npm run build                           # aset untuk produksi
php artisan migrate:fresh --seed        # reset database dan isi data demo
```

`php scripts/cek-komentar.php` harus dijalankan **setelah** Pint. Pint membetulkan akhir baris dan urutan `use`, dan keduanya memengaruhi hasil pemeriksaan.

## Palet tampilan

Tampilan mengikuti web X dan nilainya ditulis langsung per elemen, bukan lewat token `@theme`. `@theme` di `resources/css/app.css` hanya menyisakan huruf Instrument Sans dan warna aksen Flux.

| Kebutuhan | Nilai |
| --- | --- |
| Teks utama | `#0f1419` terang, `#e7e9ea` gelap |
| Latar halaman | `bg-white` terang, `bg-black` gelap |
| Garis pemisah | `border-zinc-200` terang, `#2f3336` gelap |
| Teks sekunder | `text-zinc-500` |
| Biru aksi dan tautan | `#1d9bf0`, tautan tagar `text-sky-500` |
| Suka aktif | `#f91880` |
| Repost aktif | `#00ba7c` |

## Akun data demo

`php artisan migrate:fresh --seed` membuat tiga akun dengan kata sandi `password`:

| Email | Username |
| --- | --- |
| `andi@example.com` | `andi` |
| `bunga@example.com` | `bunga` |
| `cakra@example.com` | `cakra` |
