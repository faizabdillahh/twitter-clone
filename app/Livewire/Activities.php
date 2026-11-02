<?php

namespace App\Livewire;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Aktivitas terbaru pada post dan akun pengguna: suka, balasan, repost, dan pengikut baru.
 */
#[Layout('components.layouts.app')]
class Activities extends Component
{
    /**
     * Menampilkan gabungan aktivitas orang lain terhadap milik pengguna ini.
     */
    public function render(): View
    {
        // Semua sumber di bawah disaring ke post milik pengguna yang sedang login.
        $saya = Auth::user();

        // Id post sendiri dipakai sebagai saringan balasan, repost, dan suka.
        $idPost = $saya->posts()->pluck('id');

        // Suka: baris pivot pada post sendiri, tanpa suka dari diri sendiri.
        $suka = DB::table('post_likes')
            ->whereIn('post_id', $idPost)
            ->where('user_id', '!=', $saya->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Balasan dan repost orang lain yang menempel pada post sendiri.
        $tanggapan = Post::query()
            ->whereIn('parent_id', $idPost)
            ->where('user_id', '!=', $saya->id)
            ->whereIn('type', [PostType::Reply, PostType::Repost])
            ->with('user')
            ->latest()
            ->limit(30)
            ->get();

        // Pengikut baru, yaitu baris follows yang menunjuk pengguna ini.
        $pengikut = DB::table('follows')
            ->where('following_id', $saya->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Pelaku suka dan pengikut diambil sekali, dipakai untuk nama dan avatar.
        $pelaku = User::query()
            ->whereIn('id', $suka->pluck('user_id')->merge($pengikut->pluck('follower_id'))->unique())
            ->get()
            ->keyBy('id');

        // Tiga sumber digabung menjadi satu daftar yang bentuknya sama.
        $aktivitas = $this->gabung($suka, $tanggapan, $pengikut, $pelaku);

        // Kirim daftar yang sudah terurut ke view aktivitas.
        return view('livewire.activities', ['aktivitas' => $aktivitas]);
    }

    /**
     * Menyatukan suka, tanggapan, dan pengikut menjadi daftar terurut terbaru.
     *
     * @param  Collection<int, object>  $suka
     * @param  Collection<int, Post>  $tanggapan
     * @param  Collection<int, object>  $pengikut
     * @param  Collection<string, User>  $pelaku
     * @return Collection<int, array{waktu: Carbon, user: User, teks: string, url: string}>
     */
    private function gabung(Collection $suka, Collection $tanggapan, Collection $pengikut, Collection $pelaku): Collection
    {
        // Suka dibaca dari tabel pivot, jadi waktunya masih berupa teks.
        $dariSuka = $suka->map(fn (object $baris) => [
            'waktu' => Carbon::parse($baris->created_at),
            'user' => $pelaku->get($baris->user_id),
            'teks' => 'menyukai post Anda',
            'url' => route('posts.show', $baris->post_id),
        ]);

        // Balasan dan repost sudah berupa model, jadi relasi user bisa dipakai langsung.
        $dariTanggapan = $tanggapan->map(fn (Post $post) => [
            'waktu' => $post->created_at,
            'user' => $post->user,
            'teks' => $post->type === PostType::Repost ? 'merepost post Anda' : 'membalas post Anda',
            'url' => route('posts.show', $post),
        ]);

        // Pengikut baru membuka profil pengikutnya.
        $dariPengikut = $pengikut->map(fn (object $baris) => [
            'waktu' => Carbon::parse($baris->created_at),
            'user' => $pelaku->get($baris->follower_id),
            'teks' => 'mulai mengikuti Anda',
            'url' => route('profile.show', $pelaku->get($baris->follower_id) ?? $baris->follower_id),
        ]);

        // Akun yang sudah terhapus dilewati, lalu diambil tiga puluh terbaru.
        return $dariSuka
            ->merge($dariTanggapan)
            ->merge($dariPengikut)
            ->filter(fn (array $item) => $item['user'] !== null)
            ->sortByDesc('waktu')
            ->take(30)
            ->values();
    }
}
