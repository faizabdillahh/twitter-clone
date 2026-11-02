<?php

namespace App\Policies;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;

/**
 * Aturan siapa yang boleh mengubah dan menghapus post.
 *
 * Hanya penulisnya. Repost tidak bisa diedit karena tidak punya teks sendiri.
 */
class PostPolicy
{
    /**
     * Menentukan apakah pengguna boleh mengubah post.
     */
    public function update(User $user, Post $post): bool
    {
        // Pemilik boleh mengedit, kecuali pada repost yang isinya milik orang lain.
        return $post->user_id === $user->id && $post->type !== PostType::Repost;
    }

    /**
     * Menentukan apakah pengguna boleh menghapus post.
     */
    public function delete(User $user, Post $post): bool
    {
        // Pemilik boleh menghapus repost maupun post biasa.
        return $post->user_id === $user->id;
    }
}
