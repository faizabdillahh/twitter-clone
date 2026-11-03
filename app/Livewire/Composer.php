<?php

namespace App\Livewire;

use App\Enums\PostType;
use App\Livewire\Concerns\ValidatesPostInput;
use App\Models\Post;
use App\Support\PostWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Kotak penulisan post di bagian atas feed.
 *
 * Mendukung unggah gambar dan berkas secara terpisah dengan batas 250 karakter.
 */
#[Layout('components.layouts.app')]
class Composer extends Component
{
    // Aturan body dan penghitung panjang dipakai bersama EditPost nanti.
    use ValidatesPostInput;

    // Menyediakan fitur unggah berkas dan pengelolaan berkas sementara.
    use WithFileUploads;

    /**
     * Ekstensi berkas gambar yang diterima sistem.
     *
     * @var list<string>
     */
    private const EKSTENSI_GAMBAR = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Ekstensi dokumen dan berkas arsip yang diterima sistem.
     *
     * @var list<string>
     */
    private const EKSTENSI_BERKAS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip'];

    /**
     * Seluruh ekstensi lampiran yang diizinkan sistem.
     *
     * @var list<string>
     */
    private const EKSTENSI_LAMPIRAN = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip'];

    /**
     * Berkas gambar yang dipilih pengguna melalui input gambar.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $gambar = [];

    /**
     * Berkas dokumen atau arsip yang dipilih melalui input berkas.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $berkas = [];

    /**
     * Lampiran gabungan (dipertahankan untuk kompatibilitas uji).
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $media = [];

    public bool $merantai = false; // True bila kiriman berikutnya menyambung thread.

    public bool $merantaiOtomatis = true; // False untuk dialog: tiap kiriman berdiri sendiri.

    public bool $fokusOtomatis = false; // True untuk dialog supaya kursor siap di textarea.

    /**
     * Menyiapkan mode tulis: tweet biasa, balasan, atau kutipan.
     */
    public function mount(?Post $parent = null, string $tipe = 'tweet'): void
    {
        // Komponen yang dipanggil tanpa atribut tetap menulis tweet baru.
        $this->parent = $parent;

        // Tipe dipaksa salah satu nilai enum supaya mode tak dikenal jatuh ke tweet.
        $this->tipe = PostType::tryFrom($tipe)?->value ?? PostType::Tweet->value;
    }

    /**
     * Mengumpulkan seluruh lampiran dari gambar, dokumen, dan properti media.
     *
     * @return array<int, TemporaryUploadedFile>
     */
    public function semuaBerkas(): array
    {
        // Satukan ketiga sumber berkas ke dalam satu array tanpa celah indeks.
        return array_values(array_merge($this->media, $this->gambar, $this->berkas));
    }

    /**
     * Memvalidasi isi lalu menyerahkan penyimpanan ke PostWriter.
     */
    public function kirim(PostWriter $penulis): void
    {
        // Spasi di ujung tidak dihitung sebagai isi.
        $this->body = trim($this->body);

        // Ambil seluruh lampiran aktif.
        $lampiran = $this->semuaBerkas();

        // Aturan body ditambah aturan khusus gambar dan berkas.
        $this->validate($this->aturanPost());

        // Batasi total akumulasi berkas maksimal 4 lampiran.
        if (count($lampiran) > 4) {
            // Berikan pesan galat bila total berkas melebihi kuota.
            $this->addError('media', 'Maksimal 4 lampiran per postingan.');

            // Batalkan pengiriman kiriman.
            return;
        }

        // Panjang dihitung per grapheme setelah validasi dasar lolos.
        $this->pastikanPanjangBody();

        // Jenis dan induk harus cocok sebelum apa pun disimpan.
        $this->pastikanKombinasi();

        // Post tanpa teks dan tanpa lampiran tidak bermakna.
        if ($this->body === '' && $lampiran === []) {
            // Pesan ditaruh di body supaya muncul di bawah kotak tulis.
            $this->addError('body', 'Tulis sesuatu atau lampirkan berkas.');

            // Berhenti sebelum PostWriter dipanggil.
            return;
        }

        // Batas laju menahan skrip pengirim banjir: tiga puluh kiriman per menit per akun.
        if (! RateLimiter::attempt('tulis:'.Auth::id(), 30, fn () => true, 60)) {
            // Pesan ditaruh di body supaya muncul di bawah kotak tulis.
            $this->addError('body', 'Terlalu banyak post. Tunggu sebentar lalu coba lagi.');

            // Berhenti sebelum PostWriter dipanggil.
            return;
        }

        // Body kosong disimpan sebagai null agar kolom nullable tetap bersih.
        $post = $penulis->tulis(Auth::user(), [
            'type' => $this->jenis(),
            'parent_id' => $this->parent?->id,
            'body' => $this->body === '' ? null : $this->body,
        ], $lampiran);

        // Formulir dikosongkan supaya siap dipakai lagi.
        $this->reset('body', 'media', 'gambar', 'berkas');

        // Kutipan menjadi post baru di feed, jadi pengguna dibawa ke sana.
        if ($this->jenis() === PostType::Quote) {
            // Arahkan ke beranda setelah mengutip post.
            $this->redirect(route('home'), navigate: true);

            // Tidak perlu memuat ulang halaman thread yang sedang ditinggalkan.
            return;
        }

        // Rantai hanya berlanjut untuk post yang baru dibuat dari mode tweet atau rantai berjalan.
        if ($this->merantaiOtomatis && ($this->merantai || $post->type === PostType::Tweet)) {
            // Kiriman berikutnya menyambung sebagai balasan ke post yang baru dibuat.
            $this->parent = $post;
            // Mode berganti ke balasan supaya aturan kombinasi tetap sah.
            $this->tipe = PostType::Reply->value;
            // Penanda di view menyala selama rantai berlangsung.
            $this->merantai = true;
        }

        // Feed dan daftar balasan mendengarkan event ini untuk memuat ulang.
        $this->dispatch('post-created', postId: $post->id);
    }

    /**
     * Menyaring unggahan gambar yang tidak sesuai format atau batas ukuran.
     */
    public function updatedGambar(): void
    {
        // Periksa setiap berkas gambar yang dipilih.
        foreach ($this->gambar as $indeks => $satu) {
            // Ekstensi harus termasuk kategori gambar yang diizinkan dan maksimal 5 MB.
            if (! in_array(strtolower($satu->getClientOriginalExtension()), self::EKSTENSI_GAMBAR, true) || $satu->getSize() > 5 * 1024 * 1024) {
                // Buang gambar yang tidak lolos filter.
                unset($this->gambar[$indeks]);

                // Pasang pesan galat untuk input gambar.
                $this->addError('gambar', 'Gambar harus berformat JPG, PNG, WEBP, atau GIF maksimal 5 MB.');
            }
        }

        // Rapikan kembali indeks array gambar.
        $this->gambar = array_values($this->gambar);
    }

    /**
     * Menyaring unggahan dokumen yang tidak sesuai format atau batas ukuran.
     */
    public function updatedBerkas(): void
    {
        // Periksa setiap berkas dokumen yang dipilih.
        foreach ($this->berkas as $indeks => $satu) {
            // Ekstensi harus termasuk kategori berkas dokumen/arsip dan maksimal 5 MB.
            if (! in_array(strtolower($satu->getClientOriginalExtension()), self::EKSTENSI_BERKAS, true) || $satu->getSize() > 5 * 1024 * 1024) {
                // Buang berkas yang tidak lolos filter.
                unset($this->berkas[$indeks]);

                // Pasang pesan galat untuk input berkas.
                $this->addError('berkas', 'Berkas harus berupa dokumen atau arsip maksimal 5 MB.');
            }
        }

        // Rapikan kembali indeks array berkas.
        $this->berkas = array_values($this->berkas);
    }

    /**
     * Menyaring lampiran media umum untuk kompatibilitas pengujian.
     */
    public function updatedMedia(): void
    {
        // Lampiran harus bertipe dikenal dan tidak lebih dari 5 MB.
        foreach ($this->media as $indeks => $satu) {
            // Berkas bertipe asing atau kelebihan ukuran langsung dibuang.
            if (! in_array(strtolower($satu->getClientOriginalExtension()), self::EKSTENSI_LAMPIRAN, true) || $satu->getSize() > 5 * 1024 * 1024) {
                // Buang berkas yang tidak lolos aturan.
                unset($this->media[$indeks]);

                // Satu pesan untuk semua lampiran yang gugur.
                $this->addError('media', 'Lampiran harus berupa gambar, dokumen, atau arsip maksimal 5 MB.');
            }
        }

        // Indeks dinomori ulang agar tombol buang menunjuk lampiran yang benar.
        $this->media = array_values($this->media);
    }

    /**
     * Membuang satu gambar dari pratinjau sebelum dikirim.
     */
    public function hapusGambar(int $indeks): void
    {
        // Hapus elemen array pada indeks target.
        unset($this->gambar[$indeks]);

        // Urutkan ulang indeks array gambar.
        $this->gambar = array_values($this->gambar);
    }

    /**
     * Membuang satu berkas dokumen dari pratinjau sebelum dikirim.
     */
    public function hapusBerkas(int $indeks): void
    {
        // Hapus elemen array pada indeks target.
        unset($this->berkas[$indeks]);

        // Urutkan ulang indeks array berkas.
        $this->berkas = array_values($this->berkas);
    }

    /**
     * Membuang satu lampiran dari pratinjau sebelum post dikirim.
     */
    public function hapusMedia(int $indeks): void
    {
        // Kunci dilepas lebih dulu supaya urutan array tetap rapat.
        unset($this->media[$indeks]);

        // Indeks dinomori ulang agar tombol lampiran berikutnya tetap menunjuk berkas yang benar.
        $this->media = array_values($this->media);
    }

    /**
     * Mengakhiri rantai dan kembali ke mode tweet biasa.
     */
    public function berhentiMerantai(): void
    {
        // Induk dilepas supaya kiriman berikutnya berdiri sendiri.
        $this->parent = null;
        // Kembali ke mode tweet biasa.
        $this->tipe = PostType::Tweet->value;
        // Penanda rantai dimatikan.
        $this->merantai = false;
    }

    /**
     * Menggabungkan aturan body dengan aturan unggahan lampiran.
     *
     * @return array<string, mixed>
     */
    private function aturanPost(): array
    {
        // Validasi body digabung dengan validasi ketiga properti berkas.
        return array_merge($this->aturanBody(), [
            'gambar' => ['array', 'max:4'],
            'gambar.*' => ['file', 'mimes:'.implode(',', self::EKSTENSI_GAMBAR), 'max:5120'],
            'berkas' => ['array', 'max:4'],
            'berkas.*' => ['file', 'mimes:'.implode(',', self::EKSTENSI_BERKAS), 'max:5120'],
            'media' => ['array', 'max:4'],
            'media.*' => ['file', 'mimes:'.implode(',', self::EKSTENSI_LAMPIRAN), 'max:5120'],
        ]);
    }

    /**
     * Menampilkan kotak tulis.
     */
    public function render(): View
    {
        // Render view blade untuk formulir composer.
        return view('livewire.composer');
    }
}
