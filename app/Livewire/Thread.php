<?php

namespace App\Livewire;

use App\Enums\PostType;
use App\Livewire\Concerns\DeletesPost;
use App\Livewire\Concerns\TogglesLike;
use App\Livewire\Concerns\TogglesRepost;
use App\Models\Post;
use App\Support\PostCardStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Halaman detail satu post beserta balasannya.
 *
 * Formulir di halaman ini bisa berperan sebagai balasan atau kutipan,
 * tergantung parameter `kutip` di URL.
 */
#[Layout('components.layouts.app')]
class Thread extends Component
{
    // Menu hapus pada kartu post milik sendiri.
    use DeletesPost;

    // Tombol suka yang bisa dibatalkan dengan klik kedua.
    use TogglesLike;

    // Tombol repost yang bisa dibatalkan dengan klik kedua.
    use TogglesRepost;

    public Post $post; // Post induk yang sedang dibuka.

    /**
     * Memuat relasi post induk sekali saja.
     */
    public function mount(Post $post): void
    {
        // Tanpa eager load, kartu post memicu query tambahan saat dirender.
        $post->load(['user', 'media', 'hashtags', 'parent']);

        // Simpan model yang sudah siap dipakai view.
        $this->post = $post;
    }

    /**
     * Memuat ulang balasan setelah Composer menyimpan post baru.
     */
    #[On('post-created')]
    public function segarkan(): void
    {
        // Method kosong: Livewire merender ulang komponen setelah listener selesai.
    }

    /**
     * Menampilkan post induk, formulir, dan balasannya.
     */
    public function render(): View
    {
        // Rantai induk ditampilkan di atas post supaya balasan punya konteks.
        $induk = $this->rantaiInduk();

        // Balasan tertua di atas agar percakapan terbaca berurutan.
        $balasan = $this->post->replies()->with(['user', 'media'])->oldest()->get();

        // Induk, rantai, dan balasan dihitung statusnya sekali untuk seluruh halaman.
        $status = PostCardStatus::untuk(collect([$this->post])->merge($induk)->merge($balasan), Auth::user());

        // Parameter kutip memindahkan formulir dari mode balasan ke mode kutipan.
        $mode = request()->boolean('kutip') ? PostType::Quote->value : PostType::Reply->value;

        // Kirim daftar balasan, status tombol, dan mode formulir ke view.
        return view('livewire.thread', [
            'induk' => $induk,
            'balasan' => $balasan,
            'status' => $status,
            'mode' => $mode,
        ]);
    }

    /**
     * Mengumpulkan post induk dari yang paling tua sampai induk langsung.
     *
     * @return Collection<int, Post>
     */
    private function rantaiInduk(): Collection
    {
        $rantai = collect();
        // Titik awal penelusuran adalah induk langsung post yang sedang dibuka.
        $sekarang = $this->post->parent;

        // Rantai berhenti di induk yang sudah dihapus karena isinya tidak bisa dibaca.
        while ($sekarang !== null && ! $sekarang->trashed() && $rantai->count() < 5) {
            // Relasi yang dipakai kartu dimuat per level agar view tidak menembak query.
            $sekarang->loadMissing(['user', 'media', 'hashtags']);
            // Induk tertua harus berada paling atas.
            $rantai->prepend($sekarang);
            // Naik satu level ke induk berikutnya.
            $sekarang = $sekarang->parent;
        }

        // Urutannya sudah dari induk tertua ke induk langsung.
        return $rantai;
    }
}
