<?php

namespace App\Livewire;

use App\Livewire\Concerns\DeletesPost;
use App\Livewire\Concerns\TogglesLike;
use App\Livewire\Concerns\TogglesRepost;
use App\Models\Post;
use App\Support\PostCardStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pencarian post dan halaman tag.
 *
 * Satu komponen dipakai dua route: `/cari` untuk kata kunci bebas dan
 * `/tag/{tag}` untuk hashtag tertentu.
 */
#[Layout('components.layouts.app')]
class SearchPosts extends Component
{
    // Menu hapus pada kartu post milik sendiri.
    use DeletesPost;

    // Tombol suka yang bisa dibatalkan dengan klik kedua.
    use TogglesLike;

    // Tombol repost yang bisa dibatalkan dengan klik kedua.
    use TogglesRepost;

    // Membagi hasil pencarian ke beberapa halaman.
    use WithPagination;

    /**
     * Kata kunci pencarian.
     *
     * Disimpan di URL agar hasil bisa dibagikan dan bertahan saat dimuat ulang.
     */
    #[Url(as: 'q')]
    public string $q = '';

    public ?string $tag = null; // Tag yang sedang dibuka, null bila mode pencarian.

    /**
     * Menyiapkan mode halaman: pencarian bebas atau satu tag.
     */
    public function mount(?string $tag = null): void
    {
        // Tag dinormalkan karena hashtag selalu disimpan huruf kecil.
        $this->tag = $tag === null ? null : Str::lower($tag);
    }

    /**
     * Mengembalikan hasil ke halaman pertama saat kata kunci berubah.
     */
    public function updatedQ(): void
    {
        // Halaman lama bisa berada di luar jumlah hasil yang baru.
        $this->resetPage();
    }

    /**
     * Menampilkan hasil pencarian atau isi satu tag.
     */
    public function render(): View
    {
        // Kata kunci dirapikan dari spasi di ujung.
        $kata = trim($this->q);

        // Semua hasil memakai bentuk yang sama dengan feed.
        $query = Post::feed()->with(['user', 'media', 'hashtags', 'parent.user', 'parent.media'])->latest();

        // Mode tag hanya memuat post yang menempel pada tag tersebut.
        if ($this->tag !== null) {
            $query->whereHas('hashtags', fn ($tag) => $tag->where('tag', $this->tag));
        }

        // Kata kunci kosong berarti tidak ada saringan tambahan.
        if ($kata !== '') {
            // Kata yang diawali tanda # diperlakukan sebagai nama tag.
            $bertandaTag = str_starts_with($kata, '#');

            // Pencarian tag memakai relasi hashtag supaya hasilnya tepat.
            if ($bertandaTag) {
                $tagDicari = Str::lower(ltrim($kata, '#'));
                // Saringan ini hanya menyisakan post yang benar-benar memakai tag itu.
                $query->whereHas('hashtags', fn ($tag) => $tag->where('tag', $tagDicari));
            }

            // Pencarian bebas mencocokkan isi post, nama, atau username.
            if (! $bertandaTag) {
                $query->where(fn ($cari) => $cari
                    ->where('body', 'like', "%{$kata}%")
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('name', 'like', "%{$kata}%")
                        ->orWhere('username', 'like', "%{$kata}%")));
            }
        }

        // Hasil dibagi per halaman.
        $posts = $query->paginate(10);

        // Status tombol dihitung sekali untuk semua kartu di halaman ini.
        $status = PostCardStatus::untuk($posts->getCollection(), Auth::user());

        // Kirim hasil, status tombol, dan kata kunci ke view.
        return view('livewire.search-posts', ['posts' => $posts, 'status' => $status, 'kata' => $kata]);
    }
}
