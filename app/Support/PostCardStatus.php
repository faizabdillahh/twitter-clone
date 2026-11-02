<?php

namespace App\Support;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Status tombol kartu post untuk satu halaman sekaligus.
 *
 * Dikumpulkan sekali per halaman supaya setiap kartu tidak menembak query
 * sendiri. Isinya: post yang sudah direpost, post yang sudah disukai, serta
 * jumlah balasan, repost, dan suka tiap post.
 */
final class PostCardStatus
{
    /**
     * Menghitung status untuk daftar post yang akan dirender.
     *
     * @param  Collection<int, Post>  $posts
     * @return array{sudahRepost: list<int>, sudahLike: list<int>, jumlahLike: array<int, int>, jumlahBalas: array<int, int>, jumlahRepost: array<int, int>}
     */
    public static function untuk(Collection $posts, User $user): array
    {
        // Post induk ikut dihitung karena kartu repost menampilkan post aslinya.
        $ids = $posts->flatMap(fn (Post $post) => [$post->id, $post->parent_id])->filter()->unique()->values();

        // Satu query untuk jumlah balasan tiap post di halaman ini.
        $jumlahBalas = self::hitungAnak($ids, PostType::Reply);

        // Satu query tambahan untuk jumlah repost tiap post.
        $jumlahRepost = self::hitungAnak($ids, PostType::Repost);

        // Satu query untuk semua jumlah suka di halaman ini.
        $jumlahLike = DB::table('post_likes')
            ->whereIn('post_id', $ids)
            ->groupBy('post_id')
            ->selectRaw('post_id, count(*) as jumlah')
            ->pluck('jumlah', 'post_id')
            ->all();

        // Satu query untuk post yang sudah disukai pengguna ini.
        $sudahLike = DB::table('post_likes')
            ->where('user_id', $user->id)
            ->whereIn('post_id', $ids)
            ->pluck('post_id')
            ->all();

        // Satu query untuk repost milik pengguna ini di halaman yang sama.
        $sudahRepost = $user->posts()
            ->where('type', PostType::Repost)
            ->whereIn('parent_id', $ids)
            ->pluck('parent_id')
            ->all();

        // Kelimanya dipakai kartu lewat satu variabel status.
        return [
            'sudahRepost' => $sudahRepost,
            'sudahLike' => $sudahLike,
            'jumlahLike' => $jumlahLike,
            'jumlahBalas' => $jumlahBalas,
            'jumlahRepost' => $jumlahRepost,
        ];
    }

    /**
     * Menghitung anak bertipe tertentu untuk sekumpulan post induk.
     *
     * @param  Collection<int, int>  $ids
     * @return array<int, int>
     */
    private static function hitungAnak(Collection $ids, PostType $tipe): array
    {
        // Satu query untuk semua induk di halaman, bukan satu query per kartu.
        return Post::query()
            ->whereIn('parent_id', $ids)
            ->where('type', $tipe)
            ->groupBy('parent_id')
            ->selectRaw('parent_id, count(*) as jumlah')
            ->pluck('jumlah', 'parent_id')
            ->all();
    }
}
