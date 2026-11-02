<?php

namespace App\Support;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Satu pintu untuk membuat post apa pun jenisnya.
 *
 * Komponen tidak menyentuh tabel posts secara langsung, sehingga aturan
 * "media dan hashtag selalu ikut tersimpan" cukup dijaga di sini.
 */
class PostWriter
{
    public function __construct(
        // Menyelaraskan relasi hashtag dengan isi body.
        private HashtagSync $hashtags,
        // Menyimpan gambar dan mencatat urutannya.
        private MediaUploader $media,
    ) {}

    /**
     * Menyimpan post baru beserta media dan hashtagnya.
     *
     * @param  array<string, mixed>  $data  Berisi type, parent_id, dan body.
     * @param  array<int, TemporaryUploadedFile>  $berkas  Gambar yang diunggah.
     */
    public function tulis(User $user, array $data, array $berkas = []): Post
    {
        // Semua baris tersimpan bersama atau tidak sama sekali.
        return DB::transaction(function () use ($user, $data, $berkas) {
            // Relasi posts() mengisi user_id secara otomatis.
            $post = $user->posts()->create([
                'type' => $data['type'] ?? PostType::Tweet,
                'parent_id' => $data['parent_id'] ?? null,
                'body' => $data['body'] ?? null,
            ]);

            // Gambar disimpan setelah post punya id.
            $this->media->simpan($post, $berkas);

            // Hashtag dibaca dari body yang benar-benar tersimpan.
            $this->hashtags->sync($post, $post->body);

            // Post dikembalikan agar pemanggil bisa memakai id-nya.
            return $post;
        });
    }
}
