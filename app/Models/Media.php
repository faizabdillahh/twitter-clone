<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Gambar yang menempel pada satu post.
 *
 * Berkasnya disimpan di disk `public`; yang masuk database hanya path relatif.
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
     * URL siap pakai di tag img.
     */
    public function url(): string
    {
        // Domain mengikuti konfigurasi disk, bukan disimpan di database.
        return Storage::disk('public')->url($this->path);
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
