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
 * Batas 250 karakter memakai grapheme, jadi emoji dihitung satu karakter.
 */
#[Layout('components.layouts.app')]
class Composer extends Component
{
    // Aturan body dan penghitung panjang dipakai bersama EditPost nanti.
    use ValidatesPostInput;

    // Menyediakan properti media dan pembersihan berkas sementara.
    use WithFileUploads;

    /**
     * Gambar yang dipilih tetapi belum dikirim.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $media = [];

    public bool $merantai = false; // True bila kiriman berikutnya menyambung thread.

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
     * Memvalidasi isi lalu menyerahkan penyimpanan ke PostWriter.
     */
    public function kirim(PostWriter $penulis): void
    {
        // Spasi di ujung tidak dihitung sebagai isi.
        $this->body = trim($this->body);

        // Aturan body ditambah aturan khusus gambar.
        $this->validate($this->aturanPost());

        // Panjang dihitung per grapheme setelah validasi dasar lolos.
        $this->pastikanPanjangBody();

        // Jenis dan induk harus cocok sebelum apa pun disimpan.
        $this->pastikanKombinasi();

        // Post tanpa teks dan tanpa gambar tidak bermakna.
        if ($this->body === '' && $this->media === []) {
            // Pesan ditaruh di body supaya muncul di bawah kotak tulis.
            $this->addError('body', 'Tulis sesuatu atau lampirkan gambar.');

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
        ], $this->media);

        // Formulir dikosongkan supaya siap dipakai lagi.
        $this->reset('body', 'media');

        // Kutipan menjadi post baru di feed, jadi pengguna dibawa ke sana.
        if ($this->jenis() === PostType::Quote) {
            $this->redirect(route('home'), navigate: true);

            // Tidak perlu memuat ulang halaman thread yang sedang ditinggalkan.
            return;
        }

        // Rantai hanya berlanjut untuk post yang baru dibuat dari mode tweet atau rantai berjalan.
        if ($this->merantai || $post->type === PostType::Tweet) {
            // Kiriman berikutnya menyambung sebagai balasan ke post yang baru dibuat.
            $this->parent = $post;
            // Mode berganti ke balasan supaya aturan kombinasi tetap sah.
            $this->tipe = PostType::Reply->value;
            // Penanda di view menyala selama rantai berlangsung.
            $this->merantai = true;
        }
        // Balasan di halaman utas tetap membalas post yang sama, seperti di X.

        // Feed dan daftar balasan mendengarkan event ini untuk memuat ulang.
        $this->dispatch('post-created', postId: $post->id);
    }

    /**
     * Menyaring lampiran yang tidak memenuhi aturan gambar.
     */
    public function updatedMedia(): void
    {
        // Hanya gambar sampai 5 MB yang boleh menunggu di kotak tulis.
        foreach ($this->media as $indeks => $berkas) {
            // Berkas yang tidak bisa dipratinjau atau kelebihan ukuran langsung dibuang.
            if (! $berkas->isPreviewable() || $berkas->getSize() > 5 * 1024 * 1024) {
                unset($this->media[$indeks]);

                // Satu pesan untuk semua lampiran yang gugur.
                $this->addError('media', 'Lampiran harus berupa gambar maksimal 5 MB.');
            }
        }

        // Indeks dinomori ulang agar tombol buang menunjuk gambar yang benar.
        $this->media = array_values($this->media);
    }

    /**
     * Membuang satu gambar dari pratinjau sebelum post dikirim.
     */
    public function hapusMedia(int $indeks): void
    {
        // Kunci dilepas lebih dulu supaya urutan array tetap rapat.
        unset($this->media[$indeks]);

        // Indeks dinomori ulang agar tombol gambar berikutnya tetap menunjuk gambar yang benar.
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
     * Menggabungkan aturan body dengan aturan unggahan gambar.
     *
     * @return array<string, mixed>
     */
    private function aturanPost(): array
    {
        return array_merge($this->aturanBody(), [
            // Empat gambar per post, masing-masing maksimal 5 MB.
            'media' => ['array', 'max:4'],
            'media.*' => ['image', 'max:5120'],
        ]);
    }

    /**
     * Menampilkan kotak tulis.
     */
    public function render(): View
    {
        return view('livewire.composer');
    }
}
