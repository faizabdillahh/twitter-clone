# Changelog

Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), versi mengikuti [Semantic Versioning](https://semver.org/lang/id/).

## [Belum dirilis]

### Ditambahkan

- Fondasi aplikasi: Laravel 13, Livewire 4, Flux UI, Tailwind CSS v4, MySQL.
- `AGENTS.md` berisi perintah, jebakan, dan konvensi untuk agen.
- `scripts/cek-komentar.php` untuk menegakkan komentar per baris.
- Dokumentasi Diátaxis di `docs/`, termasuk `ruang-lingkup.md` dan `alur-kerja.md` yang menggantikan `PRD.md` dan `workflow.md`, serta catatan keputusan di `docs/adr/`.
- Kolom profil di `users`: `username`, `bio`, `location`, `website`, `avatar_path`, `banner_path`.
- Kolom username pada formulir pendaftaran, disimpan huruf kecil dan unik.
- Komponen `x-avatar` yang menampilkan foto atau inisial nama.
- Unggah avatar dan banner di halaman pengaturan profil.
- Tabel `posts`, `media`, `hashtags`, dan `hashtag_post`.
- `PostType`, model `Post`, `Media`, `Hashtag`, serta `PostWriter`, `HashtagSync`, dan `MediaUploader`.
- `Composer` di beranda: teks, sampai empat gambar, dan penghitung 250 grapheme.
- `Timeline` untuk feed dan `Thread` untuk halaman detail post.
- Komponen `x-post-card` yang dipakai feed dan halaman detail.
- Balasan dari halaman detail, disimpan sebagai `type=reply` di bawah post induk.
- Kutipan lewat parameter `kutip=1`, disimpan sebagai `type=quote` dan tampil dengan kotak post yang dikutip.
- Tombol repost di feed dan halaman detail yang berfungsi sebagai sakelar.
- Penanda `Direpost oleh` pada baris repost dan `Post induk sudah dihapus` pada halaman balasan yatim.
- `scopeFeed` pada `Post` dan relasi `reposts()` untuk menghitung repost per post.
- Tabel `post_likes` dan `follows` beserta relasi `likes`, `following`, dan `followers`.
- Tombol suka dengan jumlah suka di setiap kartu post.
- `FollowButton` dengan dua keadaan: Ikuti dan Berhenti ikuti.
- Halaman profil publik `/{user}` dengan empat tab: Post, Balasan, Media, dan Suka.
- Tab `Mengikuti` di beranda yang hanya memuat post akun yang diikuti dan post sendiri.
- `SearchPosts` untuk `/cari` dan `/tag/{tag}`, dengan kata kunci bertanda `#` dicari lewat relasi hashtag.
- Komponen `x-post-body` yang mengubah hashtag di isi post menjadi tautan halaman tag.
- Menu Cari di sidebar.
- Halaman `aktivitas` berisi suka, balasan, repost, dan pengikut baru pada post maupun akun sendiri.
- Navigasi berisi Beranda, Cari, Aktivitas, dan tombol Post yang membuka beranda lalu memfokuskan kotak tulis.
- Halaman edit `posts/{post}/edit` dengan `EditPost`, `PostPolicy`, dan tombol hapus berkonfirmasi.
- Penanda `diedit` di kartu post yang isinya pernah diubah.
- Lanjutan thread di `Composer`: kiriman berikutnya otomatis menjadi balasan ke kiriman sebelumnya, dengan tombol Selesai untuk mengakhiri.
- Muat otomatis di beranda: sepuluh post berikutnya dimuat saat penanda bawah daftar terlihat.
- `DemoSeeder` berisi tiga akun, thread, kutipan, repost, follow, dan suka.
- `docs/explanation/arsitektur.md`, `docs/reference/rute-dan-komponen.md`, `docs/how-to/menulis-thread-dan-kutipan.md`, dan dua ADR di `docs/adr/`.
- Tab `Pengikut` dan `Mengikuti` di profil, masing-masing berisi daftar akun dengan avatar, bio singkat, tautan ke profil, dan tombol ikuti.
- Trait `DeletesPost` sebagai pemilik aksi hapus untuk semua komponen yang menampilkan kartu post.
- Menu tiga titik pada kartu post milik sendiri, berisi Sunting post dan Hapus post berkonfirmasi.
- Menu repost berisi Repost/Batalkan repost dan Kutip post, serta menu bagikan berisi Salin tautan dan Kutip post.
- `flux:toast` di layout untuk pemberitahuan singkat setelah tautan post disalin.
- Hitungan balasan, repost, dan suka pada bilah aksi kartu; angka nol disembunyikan seperti X.
- Galeri tiga gambar dengan gambar pertama memanjang, mengikuti pola X.
- Pratinjau post yang dikutip di dalam kotak tulis dan tombol buang untuk setiap gambar yang menunggu dikirim.
- Baris hitungan Balasan, Repost, dan Suka di halaman rincian post.
- Penanda "Membalas @user" pada kartu di tab balasan profil.
- Tautan otomatis untuk sebutan `@username` di isi post.
- Komponen `x-empty-state` sebagai keadaan kosong di beranda, utas, profil, pencarian, dan aktivitas.
- Item Pengaturan di menu akun, karena halaman pengaturan sudah ada tetapi belum bisa dijangkau dari navigasi.
- Halaman galat kustom di `resources/views/errors/` untuk 403, 404, 419, 429, 500, dan 503, memakai komponen `x-error-page` dan kerangka halaman tamu agar tetap terbaca tanpa login.
- Widget `Trends` di rail kanan berisi empat hashtag dengan jumlah post terbanyak, tiap baris menampilkan `#tag`, jumlah post, dan tautan ke halaman tag; tag tanpa post tidak ditampilkan.

### Dihapus

- `no-ai-slop.md` dan direktori `.qoder/`, yang merupakan salinan instruksi alat dan hasil generate IDE, bukan bagian proyek.
- Direktori `decisions/`, karena isinya sudah tergantikan oleh `docs/adr/`.
- `tests/Pest.php` yang tidak pernah dipakai karena Pest tidak terpasang, serta `tests/Unit/ExampleTest.php` yang hanya menguji nilai benar.
- Komponen Blade `placeholder-pattern` dan `app-logo` yang tidak dirujuk view mana pun, beserta lima override komponen Flux di `resources/views/flux/`.
- `resources/js/app.js` yang kosong beserta entri Vite-nya; aset kini hanya CSS.
- Dependency `axios`, `autoprefixer`, dan `laravel/sail` yang tidak dipakai.
- Rujukan ke `App\Providers\FolioServiceProvider` pada `bootstrap/providers.php`; kelas itu tidak ada dan selama ini dilewati diam-diam oleh Laravel.
- Tab dan menu `Jelajah` beserta `Post::explore()`, karena cakupannya tidak dipakai lagi.
- Menu Pengaturan di sidebar; pengaturan dibuka dari profil sendiri lewat tombol Edit profil.
- Tombol pengikut dan mengikuti di header profil, karena tabnya sudah menyediakan daftar yang sama.
- Layout auth `card` dan `split` yang tidak pernah dipakai.
- Partial `settings-heading` yang membuat judul halaman pengaturan tampil dua kali.
- Layout `layouts/app/header.blade.php` sisa starter kit yang tidak dipakai lagi.

### Diubah

- `laravel/framework` naik dari 13.0.0 ke 13.29 dan `phpunit/phpunit` ke 12.5.8; `composer audit` kini bersih dari tujuh advisory, termasuk satu bypass autentikasi.
- CI menjalankan `pint --test` dan `cek-komentar.php`, bukan lagi Pint yang mengubah berkas.
- Judul tab memakai `config('app.name')`, tidak lagi tertulis "Laravel".
- `.env.example` memakai `APP_NAME=tweetUSK`, locale `id`, dan koneksi MySQL `tweetusk`, sesuai README.
- README, CONTRIBUTING, dan AGENTS ditulis ulang dengan nada publik: prasyarat, gerbang mutu, dan indeks dokumentasi di satu tempat.
- `tests/Feature/ExampleTest.php` menjadi `tests/Feature/TimelineTest.php` agar namanya menyebut apa yang diuji.
- `laravel/livewire-starter-kit` dinaikkan dari Laravel 12 ke Laravel 13 dan PHPUnit 12.
- Namespace layout Livewire diarahkan ke `resources/views/components/layouts`.
- Environment PHPUnit diberi `force="true"` dan `server` agar tes memakai sqlite in-memory.
- Pengaturan profil pindah dari halaman Volt ke komponen kelas `App\Livewire\Settings\Profile`.
- Teks antarmuka pendaftaran dan pengaturan profil memakai Bahasa Indonesia.
- Pemeriksa komentar mengenali `{{-- --}}` dan memperlakukan `.blade.php` sebagai Blade, bukan PHP.
- `/` sekarang menampilkan feed; `/dashboard` menjadi pengalihan ke beranda.
- Sidebar dan menu pengguna memakai bahasa Indonesia, tanpa tautan bawaan starter kit.
- `welcome.blade.php` dan `dashboard.blade.php` dihapus karena sudah tidak dipakai.
- Feed kini memuat tweet, kutipan, dan repost; balasan tetap hanya tampil di halaman induknya.
- `ValidatesPostInput` menolak kombinasi jenis dan induk yang salah, misalnya tweet berinduk atau kutipan tanpa teks.
- Repost yang dibatalkan dihapus permanen agar `repost_key` unik bisa dipakai lagi.
- Status tombol kartu (repost, suka, jumlah suka) dihitung sekali per halaman di `PostCardStatus`, bukan per kartu.
- Sidebar mendapat menu Profil yang menuju profil publik milik sendiri.
- Isi post dirender lewat `x-post-body`, jadi hashtag bisa diklik di kartu mana pun.
- Edit post menyinkronkan ulang hashtag dan mengisi `edited_at` hanya bila isi benar-benar berubah.
- Hapus post memakai soft delete, jadi balasannya tetap menampilkan penanda induk sudah dihapus; baris media dan berkas gambarnya ikut dibuang lewat hook `deleting` di `Post`.
- Kotak tulis dibatasi tiga puluh kiriman per menit per akun lewat `RateLimiter` di `Composer::kirim()`, karena `throttle:` pada rute tidak berlaku untuk aksi Livewire.
- Tautan Edit hanya muncul untuk pemilik post, dan tidak untuk repost.
- Feed beranda memakai batas `$batas` yang bertambah, bukan paginasi, supaya post lama tetap terlihat saat pengguna menggulir.
- Semua elemen mendapat cincin fokus `:focus-visible`, dan kotak tulis serta tombol tab punya label aksesibilitas.
- Menu pengaturan, judul tab, dan pilihan tema memakai bahasa Indonesia.
- Menu Profil pindah ke menu pengguna, karena navigasi utama berisi Beranda, Cari, dan Aktivitas.
- Tombol Post menjadi pintas menulis: mengirim event `buka-tulis` yang dipakai kotak tulis untuk mengambil fokus, termasuk saat halaman dibuka dengan `#tulis`.
- Feed global dan halaman aktivitas memakai indeks tambahan di `posts.created_at`, `post_likes.post_id`, dan `follows.following_id` lewat migrasi `2026_11_02_000007_add_feed_and_activity_indexes`.
- Tampilan mengikuti web X: rail kiri berisi menu ikon, kolom tengah 600px, rail kanan berisi pencarian dan `WhoToFollow`, nav bawah ponsel, dan tombol tulis bulat.
- `layouts/app.blade.php` tidak lagi membungkus slot dengan `flux:main`, karena layout sidebar sudah punya tag `main` sendiri.
- Header halaman memakai `sticky top-[53px] sm:top-0` agar di ponsel tidak tertutup header aplikasi.
- Layout auth tidak lagi mengunci tema gelap, jadi halaman masuk dan daftar mengikuti pilihan tema pengguna.
- Label tab profil pertama menjadi Postingan, menyamai sebutan di X.
- Tombol ikuti menampilkan Berhenti mengikuti hanya saat kursor menyorot, tidak lagi selalu menampilkan label berhenti.
- Baris aktivitas menaruh avatar di kiri teks, dan waktu tayang pada kartu post menjadi tautan ke halaman rincian.
- Placeholder kotak tulis mengikuti mode (tweet, balasan, kutipan), dan penghitung karakter muncul hanya saat ada teks.
- Judul widget rekomendasi akun memakai bahasa Indonesia: Siapa untuk diikuti.
- Kartu post menampilkan jumlah balasan dan repost, dihitung sekali per halaman lewat `PostCardStatus`.
- Halaman utas menampilkan rantai induk di atas post, maksimal lima level.
- Kotak balas di halaman utas tetap membalas post yang sama setelah kirim, tidak lagi berantai ke balasan pertama.
- `Post::replies()` hanya mengembalikan balasan; kutipan tidak lagi muncul di daftar balasan halaman utas.
- Halaman masuk dan daftar memakai tata letak dua kolom yang lazim di aplikasi besar: panel merek gelap dengan lambang dan satu kalimat penjelas di kiri, formulir berlabel 400px di kanan, tombol kirim selebar kolom. Panel merek disembunyikan di ponsel.
- Halaman Aktivitas disusun ulang mengikuti halaman notifikasi X: dua tab Semua dan Sebutan yang disimpan di `?tab=`, tiap baris berisi ikon kejadian, nama pelaku, keterangan, waktu singkat, dan potongan post terkait, dengan keadaan kosong berbeda per tab.

### Diperbaiki

- Repost atas post yang sudah dihapus tidak lagi menampilkan isi post itu; kartunya hanya menyisakan keterangan "Post ini sudah tidak tersedia.", dan kotak kutipan yang induknya terhapus tidak dirender.
- Menghapus post sekarang ikut menghapus baris media dan berkas gambarnya, sehingga tautan gambar lama tidak dapat dibuka lagi.
- Kata kunci baru di halaman pencarian mengembalikan hasil ke halaman pertama, tidak lagi bertahan di halaman lama.
- Lampiran yang bukan gambar tidak lagi menggagalkan render kotak tulis; berkas semacam itu ditolak saat dipilih dengan pesan di bawah kotak tulis.
- Gambar di atas 5 MB ditolak saat dipilih, bukan menunggu tombol Posting ditekan.
- Jalur lambang X memakai bentuk resmi 24 piksel, sehingga sudut kiri bawah huruf X tidak lagi terpotong di sidebar dan nav ponsel.
- `x-app-logo-icon` dihapus karena hanya dipakai kerangka auth lama dan isinya lambang Laravel, bukan lambang X.
- `x-auth-header` kini rata kiri mengikuti formulir panel kanan, dan `x-auth-field` dihapus karena isian kembali memakai `flux:input` berlabel.
- Halaman Aktivitas kini juga memuat tindakan sendiri (suka, balasan, kutipan, repost, dan akun yang diikuti) dengan kata "Anda", sehingga tidak lagi tampak kosong saat belum ada orang lain yang berinteraksi dengan akun itu.
- Tab Sebutan hanya mencocokkan sebutan yang namanya persis, sehingga `@saya` tidak lagi ikut menangkap post yang menyebut `@sayang`.
