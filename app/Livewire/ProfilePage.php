<?php

namespace App\Livewire;

use App\Enums\PostType;
use App\Livewire\Concerns\DeletesPost;
use App\Livewire\Concerns\TogglesLike;
use App\Livewire\Concerns\TogglesRepost;
use App\Models\Post;
use App\Models\User;
use App\Support\PostCardStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman profil publik dengan empat tab post ditambah daftar pengikut dan mengikuti.
 *
 * Profil dibuka lewat username, bukan id, karena route model binding pada User
 * memakai kolom itu sebagai kunci.
 */
#[Layout('components.layouts.app')]
class ProfilePage extends Component
{
    // Menu hapus pada kartu post milik sendiri.
    use DeletesPost;

    // Tombol suka yang bisa dibatalkan dengan klik kedua.
    use TogglesLike;

    // Tombol repost yang bisa dibatalkan dengan klik kedua.
    use TogglesRepost;

    public User $user; // Pemilik profil yang sedang dibuka.

    /**
     * Tab yang sedang aktif.
     *
     * Disimpan di URL agar tautan ke tab tertentu bisa dibagikan.
     */
    #[Url(as: 'tab')]
    public string $tab = 'post';

    /**
     * Memuat profil pemilik dari route binding.
     */
    public function mount(User $user): void
    {
        // Model dipakai apa adanya; tidak ada relasi yang dibutuhkan header.
        $this->user = $user;
    }

    /**
     * Menghitung ulang jumlah pengikut setelah tombol ikuti ditekan.
     */
    #[On('follow-changed')]
    public function segarkan(): void
    {
        // Method kosong: Livewire merender ulang komponen setelah listener selesai.
    }

    /**
     * Menampilkan header profil dan isi tab yang aktif.
     */
    public function render(): View
    {
        // Relasi yang sama dipakai semua tab post, jadi ditulis satu kali.
        $relasi = ['user', 'media', 'hashtags', 'parent.user', 'parent.media'];

        // Tab pengikut dan mengikuti berisi daftar akun, bukan post.
        $orang = match ($this->tab) {
            // Pengikut adalah akun yang mengikuti pemilik profil.
            'pengikut' => $this->user->followers()->orderBy('name')->get(),
            // Mengikuti adalah akun yang dipilih pemilik profil.
            'mengikuti' => $this->user->following()->orderBy('name')->get(),
            default => null,
        };

        // Isi tab dipilih dari nama tab; tab tak dikenal jatuh ke post.
        $posts = $orang !== null ? collect() : match ($this->tab) {
            // Balasan hanya muncul di sini, bukan di feed.
            'balasan' => Post::where('user_id', $this->user->id)
                ->where('type', PostType::Reply)
                ->with($relasi)
                ->latest()
                ->get(),
            // Tab media hanya berisi post yang punya gambar.
            'media' => Post::where('user_id', $this->user->id)
                ->whereHas('media')
                ->with($relasi)
                ->latest()
                ->get(),
            // Tab suka berisi post yang disukai pemilik profil.
            'suka' => Post::feed()
                ->whereIn('id', $this->user->likes()->select('posts.id'))
                ->with($relasi)
                ->latest()
                ->get(),
            // Bawaan: post yang tampil di feed milik pemilik profil.
            default => Post::where('user_id', $this->user->id)
                ->feed()
                ->with($relasi)
                ->latest()
                ->get(),
        };

        // Status tombol dihitung sekali untuk seluruh kartu di tab ini.
        $status = PostCardStatus::untuk($posts, Auth::user());

        // Kirim isi tab, status tombol, dan angka profil ke view.
        return view('livewire.profile-page', [
            'posts' => $posts,
            'orang' => $orang,
            'status' => $status,
            'jumlahPost' => $this->jumlahPost(),
            'jumlahPengikut' => $this->user->followers()->count(),
            'jumlahMengikuti' => $this->user->following()->count(),
        ]);
    }

    /**
     * Menghitung post milik pemilik profil yang tampil di feed.
     */
    private function jumlahPost(): int
    {
        // Balasan tidak dihitung karena tidak pernah muncul di feed.
        return Post::where('user_id', $this->user->id)->feed()->count();
    }

    /**
     * Daftar tab beserta labelnya, dipakai view untuk merender pilihan tab.
     *
     * @return Collection<string, string>
     */
    public function tabs(): Collection
    {
        // Kunci adalah nilai yang masuk ke URL, isinya label yang dilihat pengguna.
        return collect([
            'post' => 'Postingan',
            'balasan' => 'Balasan',
            'media' => 'Media',
            'suka' => 'Suka',
            'pengikut' => 'Pengikut',
            'mengikuti' => 'Mengikuti',
        ]);
    }
}
