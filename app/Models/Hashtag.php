<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Tag yang bisa dicari, disimpan tanpa tanda # dan selalu huruf kecil.
 */
class Hashtag extends Model
{
    /**
     * Kolom yang boleh diisi lewat mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tag',
    ];

    /**
     * Post yang memakai tag ini.
     */
    public function posts(): BelongsToMany
    {
        // Satu tag bisa dipakai banyak post dan sebaliknya.
        return $this->belongsToMany(Post::class);
    }
}
