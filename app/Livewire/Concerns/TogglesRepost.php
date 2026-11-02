<?php

namespace App\Livewire\Concerns;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use App\Support\PostWriter;
use Illuminate\Support\Facades\Auth;

/**
 * Tombol repost yang berlaku sebagai sakelar.
 *
 * Dipakai Timeline dan Thread supaya keduanya tidak menulis logika yang sama
 * dua kali. Klik pertama merepost, klik kedua milik pengguna yang sama dihapus.
 */
trait TogglesRepost
{
    /**
     * Membuat atau menghapus repost milik pengguna yang sedang login.
     */
    public function repost(int $postId, PostWriter $penulis): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Post yang direpost selalu dibaca walau sudah dihapus pemiliknya.
        $post = Post::withTrashed()->findOrFail($postId);

        // Cari repost lama milik pengguna ini untuk post yang sama.
        $lama = $user->posts()
            ->where('type', PostType::Repost)
            ->where('parent_id', $post->id)
            ->first();

        // Klik kedua membatalkan repost sebelumnya.
        if ($lama !== null) {
            // Dihapus permanen agar kunci unik repost_key bisa dipakai lagi.
            $lama->forceDelete();

            // Tidak ada yang perlu dibuat lagi.
            return;
        }

        // Post repost sendiri tidak boleh direpost berulang.
        if ($post->trashed() || $post->type === PostType::Repost) {
            return;
        }

        // Repost tanpa teks; isi yang tampil adalah post aslinya.
        $penulis->tulis($user, ['type' => PostType::Repost, 'parent_id' => $post->id]);
    }
}
