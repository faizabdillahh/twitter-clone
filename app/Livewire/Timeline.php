<?php

namespace App\Livewire;

use App\Livewire\Concerns\DeletesPost;
use App\Livewire\Concerns\TogglesLike;
use App\Livewire\Concerns\TogglesRepost;
use App\Models\Post;
use App\Support\PostCardStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Feed beranda: post terbaru dari semua pengguna atau dari akun yang diikuti.
 */
#[Layout('components.layouts.app')]
class Timeline extends Component
{
    // Menu hapus pada kartu post milik sendiri.
    use DeletesPost;

    // Tombol suka yang bisa dibatalkan dengan klik kedua.
    use TogglesLike;

    // Tombol repost yang bisa dibatalkan dengan klik kedua.
    use TogglesRepost;

    /**
     * Jumlah post yang sudah dimuat; bertambah saat pengguna menggulir ke bawah.
     */
    public int $batas = 10;

    /**
     * Cakupan feed: semua pengguna atau hanya yang diikuti.
     *
     * Disimpan di URL agar tab bisa dibagikan dan bertahan saat dimuat ulang.
     */
    #[Url(as: 'lingkup')]
    public string $lingkup = 'semua';

    /**
     * Memuat ulang feed setelah Composer menyimpan post baru.
     */
    #[On('post-created')]
    public function segarkan(): void
    {
        // Method kosong: Livewire merender ulang komponen setelah listener selesai.
    }

    /**
     * Menambah sepuluh post berikutnya di bawah daftar yang sudah tampil.
     */
    public function muatLagi(): void
    {
        // Batas ditambah, bukan halaman diganti, supaya post lama tetap terlihat.
        $this->batas += 10;
    }

    /**
     * Mengembalikan daftar ke sepuluh post saat cakupan berganti.
     */
    public function updatedLingkup(): void
    {
        // Hasil tab lain dimulai dari awal lagi.
        $this->batas = 10;
    }

    /**
     * Menampilkan feed.
     */
    public function render(): View
    {
        // Feed hanya berisi tweet, kutipan, dan repost; balasan tampil di halaman induknya.
        $query = Post::feed();
        // Relasi dimuat sekaligus agar kartu post tidak memicu query per baris.
        $query->with(['user', 'media', 'hashtags', 'parent.user', 'parent.media'])->latest();

        // Tab mengikuti membatasi feed ke akun yang diikuti, ditambah post sendiri.
        if ($this->lingkup === 'ikuti') {
            // Daftar id diambil sekali, lalu dipakai sebagai saringan user_id.
            $ids = Auth::user()->following()->pluck('users.id')->push(Auth::id());
            // Saring feed ke pemilik id tersebut.
            $query->whereIn('user_id', $ids);
        }

        // Hasil query dibatasi sesuai jumlah yang sudah dimuat pengguna.
        $posts = $query->limit($this->batas)->get();

        // Status tombol dihitung sekali untuk semua kartu di layar.
        $status = PostCardStatus::untuk($posts, Auth::user());

        // Kirim daftar post dan status tombol ke view beranda.
        return view('livewire.timeline', ['posts' => $posts, 'status' => $status]);
    }
}
