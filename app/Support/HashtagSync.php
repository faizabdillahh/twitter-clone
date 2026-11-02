<?php

namespace App\Support;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Support\Str;

/**
 * Menyelaraskan relasi hashtag sebuah post dengan tag yang disebut di body.
 */
class HashtagSync
{
    /**
     * Mengganti seluruh tag post dengan tag yang benar-benar ada di body.
     */
    public function sync(Post $post, ?string $body): void
    {
        // Tag lama dibuang supaya hasil edit tidak menyisakan tag yang sudah dihapus.
        $post->hashtags()->sync($this->idUntuk($body));
    }

    /**
     * Mengubah tag yang ditemukan menjadi id, membuat baris hashtag bila perlu.
     *
     * @return array<int, int>
     */
    private function idUntuk(?string $body): array
    {
        // Kumpulkan id unik untuk setiap tag.
        $ids = [];

        // Tag yang belum pernah dipakai dibuat sekali di sini.
        foreach ($this->ekstrak($body) as $tag) {
            $ids[] = Hashtag::firstOrCreate(['tag' => $tag])->id;
        }

        // Kembalikan daftar id untuk sync().
        return $ids;
    }

    /**
     * Mengambil tag dari body dalam bentuk huruf kecil tanpa tanda #.
     *
     * @return array<int, string>
     */
    private function ekstrak(?string $body): array
    {
        // Tag harus didahului spasi atau awal teks, bukan bagian dari kata lain.
        preg_match_all('/(?<![\w#])#([\p{L}\p{N}_]{1,50})/u', (string) $body, $cocok);

        // #Laravel dan #laravel dianggap tag yang sama.
        return array_values(array_unique(array_map(fn (string $tag) => Str::lower($tag), $cocok[1])));
    }
}
