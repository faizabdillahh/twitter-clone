<?php

namespace App\Livewire\Concerns;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Tombol suka yang berlaku sebagai sakelar.
 *
 * Dipakai Timeline, Thread, dan halaman profil supaya ketiganya tidak
 * menulis logika yang sama. Klik pertama menyukai, klik kedua membatalkan.
 */
trait TogglesLike
{
    /**
     * Menyukai atau membatalkan suka pada satu post.
     */
    public function like(int $postId): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Post yang sudah dihapus tidak bisa disukai lagi.
        $post = Post::withTrashed()->findOrFail($postId);

        // Berhenti tanpa pesan karena kartunya memang sudah tidak tampil.
        if ($post->trashed()) {
            return;
        }

        // toggle() memasang relasi bila belum ada dan melepasnya bila sudah ada.
        $user->likes()->toggle($post->id);
    }
}
