<?php

namespace App\Models;

use App\Enums\PostType;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Satu baris untuk tweet, balasan, repost, atau kutipan.
 *
 * Perbedaan antarjenis dijaga validasi dan PostWriter; skema hanya menjaga
 * keutuhan relasi dan mencegah repost ganda.
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang boleh diisi lewat mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'parent_id',
        'body',
        'repost_key',
        'edited_at',
    ];

    /**
     * Konversi tipe kolom ke tipe PHP.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'edited_at' => 'datetime',
        ];
    }

    /**
     * Penulis post.
     */
    public function user(): BelongsTo
    {
        // Pemilik selalu ada karena foreign key tidak boleh null.
        return $this->belongsTo(User::class);
    }

    /**
     * Post yang dibalas, dikutip, atau direpost.
     */
    public function parent(): BelongsTo
    {
        // Induk yang sudah dihapus tetap dibaca agar balasan tidak kehilangan konteks.
        return $this->belongsTo(self::class, 'parent_id')->withTrashed();
    }

    /**
     * Balasan yang menempel pada post ini.
     */
    public function replies(): HasMany
    {
        // Kutipan juga menunjuk induk, tetapi di X tidak dihitung sebagai balasan.
        return $this->hasMany(self::class, 'parent_id')->where('type', PostType::Reply);
    }

    /**
     * Repost yang menempel pada post ini.
     */
    public function reposts(): HasMany
    {
        // Hanya baris bertipe repost, bukan balasan atau kutipan.
        return $this->hasMany(self::class, 'parent_id')->where('type', PostType::Repost);
    }

    /**
     * Membatasi query ke post yang memang tampil di feed.
     */
    public function scopeFeed(Builder $query): void
    {
        // Balasan hanya tampil di halaman induknya, jadi tidak ikut feed.
        $query->whereIn('type', [PostType::Tweet, PostType::Quote, PostType::Repost]);
    }

    /**
     * Gambar yang menempel pada post, sesuai urutan unggahan.
     */
    public function media(): HasMany
    {
        // Urutan disimpan di kolom position supaya tampilan stabil.
        return $this->hasMany(Media::class)->orderBy('position');
    }

    /**
     * Hashtag yang disebut di dalam body.
     */
    public function hashtags(): BelongsToMany
    {
        // Tabel penghubung tanpa model, cukup pasangan id.
        return $this->belongsToMany(Hashtag::class);
    }

    /**
     * Pengguna yang menyukai post ini.
     */
    public function likes(): BelongsToMany
    {
        // Tabel penghubung post_likes hanya menyimpan pasangan id.
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    /**
     * Menyiapkan kunci unik repost sebelum baris disimpan.
     */
    protected static function booted(): void
    {
        // Kunci hanya berarti untuk repost yang punya post induk.
        static::saving(function (Post $post) {
            $post->repost_key = $post->type === PostType::Repost && $post->parent_id
                ? $post->user_id.':'.$post->parent_id
                : null;
        });

        // Gambar ikut dibuang agar isi yang ditarik penulisnya tidak bisa diunduh lagi.
        static::deleting(function (Post $post) {
            // Dihapus satu per satu supaya hook di Media ikut membuang berkasnya.
            $post->media->each->delete();
        });
    }

    /**
     * Menandai post yang body-nya pernah diubah.
     */
    public function sudahDiedit(): bool
    {
        // Kolom edited_at hanya diisi saat terjadi perubahan isi.
        return $this->edited_at !== null;
    }
}
