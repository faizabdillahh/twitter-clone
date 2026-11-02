<?php

namespace App\Livewire\Concerns;

use App\Models\Post;

/**
 * Menu hapus pada kartu post.
 *
 * Dipakai semua komponen yang menampilkan kartu supaya aturan hak akses tidak
 * disalin ulang di setiap halaman, sama seperti TogglesLike dan TogglesRepost.
 */
trait DeletesPost
{
    /**
     * Menghapus satu post setelah hak akses diperiksa.
     */
    public function hapus(int $postId): void
    {
        // Post yang sudah hilang tidak menyisakan apa pun untuk dihapus.
        $post = Post::find($postId);

        // Kartu usang setelah post dihapus di tab lain tidak perlu dilayani.
        if ($post === null) {
            return;
        }

        // Hanya pemilik yang boleh menghapus, aturannya sama dengan halaman edit.
        $this->authorize('delete', $post);

        // Soft delete menjaga balasan tetap punya jejak induknya.
        $post->delete();

        // Halaman rincian kehilangan isinya bila post yang dihapus adalah post itu sendiri.
        if (property_exists($this, 'post') && $this->post instanceof Post && $this->post->id === $post->id) {
            $this->redirect(route('home'), navigate: true);
        }
    }
}
