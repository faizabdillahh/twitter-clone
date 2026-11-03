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
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman aktivitas bergaya notifikasi X: dua tab (semua dan sebutan), tiap baris
 * memuat ikon kejadian, pelaku, potongan post terkait, dan waktu.
 */
#[Layout('components.layouts.app')]
class Activities extends Component
{
    /**
     * Tab yang sedang dibuka, disimpan di URL supaya bisa dibagikan.
     */
    #[Url(as: 'tab')]
    public string $tab = 'semua';

    /**
     * Berpindah tab tanpa memuat ulang halaman.
     */
    public function pilihTab(string $tab): void
    {
        // Nilai di luar dua pilihan yang dikenal dikembalikan ke tab semua.
        $this->tab = in_array($tab, ['semua', 'sebutan'], true) ? $tab : 'semua';
    }

    /**
     * Menampilkan isi tab yang sedang dibuka.
     */
    public function render(): View
    {
        // Akun yang sedang login menjadi pusat semua saringan.
        $saya = Auth::user();

        // Tab sebutan hanya memuat post yang menyebut akun ini.
        $aktivitas = $this->tab === 'sebutan' ? $this->sebutan($saya) : $this->gabungan($saya);

        // Daftar siap dipakai view aktivitas.
        return view('livewire.activities', ['aktivitas' => $aktivitas]);
    }

    /**
     * Post orang lain yang menyebut @username pemilik halaman.
     *
     * @return Collection<int, array{waktu: Carbon, user: ?User, nama: ?string, handle: ?string, teks: string, cuplikan: ?string, url: string}>
     */
    private function sebutan(User $saya): Collection
    {
        // Pencocokan LIKE hanya saringan kasar, batas nama akunnya diperiksa di bawah.
        $pola = '%@'.$saya->username.'%';

        // Post yang menyebut saya, diurutkan dari yang paling baru.
        return Post::query()
            ->where('body', 'like', $pola)
            ->where('user_id', '!=', $saya->id)
            ->with('user')
            ->latest()
            ->limit(30)
            ->get()
            // LIKE juga cocok untuk nama yang cuma berawalan sama (@saya vs @sayang), jadi disaring lagi.
            ->filter(fn (Post $post) => preg_match('/@'.preg_quote($saya->username, '/').'(?![\w.])/u', $post->body) === 1)
            ->map(fn (Post $post) => [
                'waktu' => $post->created_at,
                'user' => $post->user,
                'nama' => $post->user->name,
                'handle' => $post->user->username,
                'teks' => 'menyebut Anda',
                'cuplikan' => $post->body,
                'url' => route('posts.show', $post),
            ]);
    }

    /**
     * Aktivitas masuk dan tindakan sendiri sebagai satu daftar terurut.
     *
     * @return Collection<int, array{waktu: Carbon, user: ?User, nama: ?string, handle: ?string, teks: string, cuplikan: ?string, url: string}>
     */
    private function gabungan(User $saya): Collection
    {
        // Id post sendiri dipakai sebagai saringan balasan, repost, dan suka.
        $idPost = $saya->posts()->pluck('id');

        // Suka: baris pivot pada post sendiri, tanpa suka dari diri sendiri.
        $sukaMasuk = DB::table('post_likes')
            ->whereIn('post_id', $idPost)
            ->where('user_id', '!=', $saya->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Balasan dan repost orang lain yang menempel pada post sendiri.
        $tanggapanMasuk = Post::query()
            ->whereIn('parent_id', $idPost)
            ->where('user_id', '!=', $saya->id)
            ->whereIn('type', [PostType::Reply, PostType::Repost])
            ->with(['user', 'parent'])
            ->latest()
            ->limit(30)
            ->get();

        // Pengikut baru, yaitu baris follows yang menunjuk pengguna ini.
        $pengikutMasuk = DB::table('follows')
            ->where('following_id', $saya->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Suka yang pengguna berikan ke post orang lain.
        $sukaKeluar = DB::table('post_likes')
            ->where('user_id', $saya->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Post yang disukai diambil sekali: isinya untuk cuplikan, pemiliknya untuk baris keluar.
        $postDisukai = Post::query()
            ->whereIn('id', $sukaMasuk->pluck('post_id')->merge($sukaKeluar->pluck('post_id'))->unique())
            ->with('user')
            ->get()
            ->keyBy('id');

        // Balasan, kutipan, dan repost buatan pengguna sendiri.
        $tanggapanKeluar = Post::query()
            ->where('user_id', $saya->id)
            ->whereIn('type', [PostType::Reply, PostType::Quote, PostType::Repost])
            ->with(['parent', 'parent.user'])
            ->latest()
            ->limit(30)
            ->get();

        // Akun yang pengguna ikuti, yaitu baris follows dari dirinya sendiri.
        $mengikuti = DB::table('follows')
            ->where('follower_id', $saya->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Pelaku suka dan pengikut diambil sekali, dipakai untuk nama dan username.
        $pelaku = User::query()
            ->whereIn('id', $sukaMasuk->pluck('user_id')->merge($pengikutMasuk->pluck('follower_id'))->unique())
            ->get()
            ->keyBy('id');

        // Akun yang diikuti diambil sekali untuk menyebut namanya.
        $diikuti = User::query()
            ->whereIn('id', $mengikuti->pluck('following_id'))
            ->get()
            ->keyBy('id');

        // Kedua arah digabung, akun yang hilang dibuang, lalu diambil tiga puluh terbaru.
        return collect()
            ->merge($this->dariMasuk($sukaMasuk, $tanggapanMasuk, $pengikutMasuk, $pelaku, $postDisukai))
            ->merge($this->dariKeluar($saya, $sukaKeluar, $postDisukai, $tanggapanKeluar, $mengikuti, $diikuti))
            ->filter(fn (array $item) => $item['user'] !== null)
            ->sortByDesc('waktu')
            ->take(30)
            ->values();
    }

    /**
     * Aktivitas orang lain terhadap post dan akun pengguna ini.
     *
     * @param  Collection<int, object>  $suka
     * @param  Collection<int, Post>  $tanggapan
     * @param  Collection<int, object>  $pengikut
     * @param  Collection<string, User>  $pelaku
     * @param  Collection<string, Post>  $postDisukai
     * @return Collection<int, array{waktu: Carbon, user: ?User, nama: ?string, handle: ?string, teks: string, cuplikan: ?string, url: string}>
     */
    private function dariMasuk(Collection $suka, Collection $tanggapan, Collection $pengikut, Collection $pelaku, Collection $postDisukai): Collection
    {
        // Suka dibaca dari tabel pivot, jadi waktunya masih berupa teks.
        $dariSuka = $suka->map(function (object $baris) use ($pelaku, $postDisukai) {
            // Akun pelaku bisa sudah dihapus, jadi namanya diperiksa lebih dulu.
            $akun = $pelaku->get($baris->user_id);

            // Bentuk baris sama untuk semua jenis kejadian.
            return [
                'waktu' => Carbon::parse($baris->created_at),
                'user' => $akun,
                'nama' => $akun?->name,
                'handle' => $akun?->username,
                'teks' => 'menyukai post Anda',
                'cuplikan' => $postDisukai->get($baris->post_id)?->body,
                'url' => route('posts.show', $baris->post_id),
            ];
        });

        // Balasan dan repost sudah berupa model, jadi relasinya bisa dipakai langsung.
        $dariTanggapan = $tanggapan->map(fn (Post $post) => [
            'waktu' => $post->created_at,
            'user' => $post->user,
            'nama' => $post->user->name,
            'handle' => $post->user->username,
            'teks' => $post->type === PostType::Repost ? 'merepost post Anda' : 'membalas post Anda',
            'cuplikan' => $post->body ?: $post->parent?->body,
            'url' => route('posts.show', $post),
        ]);

        // Pengikut baru membuka profil pengikutnya dan tidak punya cuplikan post.
        $dariPengikut = $pengikut->map(function (object $baris) use ($pelaku) {
            // Akun pengikut bisa sudah dihapus, jadi namanya diperiksa lebih dulu.
            $akun = $pelaku->get($baris->follower_id);

            // Baris pengikut baru memakai bentuk yang sama dengan aktivitas lain.
            return [
                'waktu' => Carbon::parse($baris->created_at),
                'user' => $akun,
                'nama' => $akun?->name,
                'handle' => $akun?->username,
                'teks' => 'mulai mengikuti Anda',
                'cuplikan' => null,
                'url' => route('profile.show', $akun ?? $baris->follower_id),
            ];
        });

        // Ketiga sumber masuk dikembalikan sebagai satu kumpulan.
        return $dariSuka->merge($dariTanggapan)->merge($dariPengikut);
    }

    /**
     * Tindakan pengguna sendiri, supaya halaman ini terisi walau belum ada yang
     * berinteraksi dengan akun ini.
     *
     * @param  User  $saya  Pemilik halaman.
     * @param  Collection<int, object>  $suka
     * @param  Collection<int, Post>  $postDisukai
     * @param  Collection<int, Post>  $tanggapan
     * @param  Collection<int, object>  $mengikuti
     * @param  Collection<string, User>  $diikuti
     * @return Collection<int, array{waktu: Carbon, user: ?User, nama: ?string, handle: ?string, teks: string, cuplikan: ?string, url: string}>
     */
    private function dariKeluar(User $saya, Collection $suka, Collection $postDisukai, Collection $tanggapan, Collection $mengikuti, Collection $diikuti): Collection
    {
        // Baris milik sendiri memakai kata "Anda", jadi tidak perlu username.
        $dariSuka = $suka->map(function (object $baris) use ($saya, $postDisukai) {
            // Post tujuan bisa sudah dihapus, jadi pemiliknya diperiksa lebih dulu.
            $tujuan = $postDisukai->get($baris->post_id);

            // Bentuk baris sama dengan aktivitas masuk, hanya pelakunya diri sendiri.
            return [
                'waktu' => Carbon::parse($baris->created_at),
                'user' => $saya,
                'nama' => 'Anda',
                'handle' => null,
                'teks' => $tujuan?->user ? 'menyukai post @'.$tujuan->user->username : 'menyukai sebuah post',
                'cuplikan' => $tujuan?->body,
                'url' => route('posts.show', $baris->post_id),
            ];
        });

        // Kata kerja dipilih dari jenis post yang dibuat sendiri.
        $dariTanggapan = $tanggapan->map(function (Post $post) use ($saya) {
            // Username pemilik post induk dipakai sebagai tujuan tindakan.
            $induk = $post->parent?->user?->username;

            // Baris tindakan sendiri memakai kata "Anda" sebagai pelaku.
            return [
                'waktu' => $post->created_at,
                'user' => $saya,
                'nama' => 'Anda',
                'handle' => null,
                'teks' => match ($post->type) {
                    PostType::Repost => $induk ? 'merepost post @'.$induk : 'merepost sebuah post',
                    PostType::Quote => $induk ? 'mengutip post @'.$induk : 'mengutip sebuah post',
                    default => $induk ? 'membalas post @'.$induk : 'membalas sebuah post',
                },
                'cuplikan' => $post->body ?: $post->parent?->body,
                'url' => route('posts.show', $post),
            ];
        });

        // Akun yang diikuti membuka profilnya, sama seperti baris pengikut baru.
        $dariMengikuti = $mengikuti->map(function (object $baris) use ($saya, $diikuti) {
            // Akun tujuan bisa sudah dihapus, jadi namanya diperiksa lebih dulu.
            $tujuan = $diikuti->get($baris->following_id);

            // Baris ikutan menyebut akun yang dituju.
            return [
                'waktu' => Carbon::parse($baris->created_at),
                'user' => $saya,
                'nama' => 'Anda',
                'handle' => null,
                'teks' => 'mulai mengikuti @'.($tujuan->username ?? 'akun lain'),
                'cuplikan' => null,
                'url' => $tujuan ? route('profile.show', $tujuan) : route('home'),
            ];
        });

        // Ketiga sumber keluar dikembalikan sebagai satu kumpulan.
        return $dariSuka->merge($dariTanggapan)->merge($dariMengikuti);
    }
}
