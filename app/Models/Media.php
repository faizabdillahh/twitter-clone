<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Lampiran yang menempel pada satu post: gambar atau berkas lain.
 *
 * Berkasnya disimpan di disk `public`; yang masuk database hanya path relatif dan nama asli.
 */
class Media extends Model
{
    /**
     * Kolom yang boleh diisi lewat mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'path',
        'name',
        'position',
    ];

    /**
     * Post pemilik gambar ini.
     */
    public function post(): BelongsTo
    {
        // Media tidak pernah berdiri sendiri tanpa post.
        return $this->belongsTo(Post::class);
    }

    /**
     * URL siap pakai di tag img atau tautan unduh.
     */
    public function url(): string
    {
        // Domain mengikuti konfigurasi disk, bukan disimpan di database.
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Menandai lampiran yang bisa ditampilkan sebagai gambar.
     */
    public function isImage(): bool
    {
        // Nama berkas di disk masih membawa ekstensi aslinya, jadi tidak perlu kolom tipe.
        return in_array(strtolower(pathinfo($this->path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    /**
     * Nama berkas yang ditampilkan pada kartu lampiran.
     */
    public function label(): string
    {
        // Baris lama belum punya nama asli, jadi nama berkas di disk dipakai sebagai gantinya.
        return $this->name ?: basename($this->path);
    }

    /**
     * Menghapus berkas saat barisnya hilang.
     */
    protected static function booted(): void
    {
        // Tanpa ini, gambar yatim menumpuk setiap kali post dihapus.
        static::deleted(function (Media $media) {
            Storage::disk('public')->delete($media->path);
        });
    }
}
