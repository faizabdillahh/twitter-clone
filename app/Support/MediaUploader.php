<?php

namespace App\Support;

use App\Models\Post;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Memindahkan berkas unggahan sementara menjadi baris media milik post.
 */
class MediaUploader
{
    /**
     * Menyimpan setiap berkas ke disk public dan mencatat urutannya.
     *
     * @param  array<int, TemporaryUploadedFile>  $berkas
     */
    public function simpan(Post $post, array $berkas): void
    {
        // Urutan unggahan dipakai apa adanya sebagai posisi tampil.
        foreach ($berkas as $posisi => $satu) {
            // Berkas masuk disk public; database hanya menyimpan path relatif.
            $post->media()->create(['path' => $satu->store('posts', 'public'), 'position' => $posisi]);
        }
    }
}
