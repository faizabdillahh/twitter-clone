<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'location',
        'website',
        'avatar_path',
        'banner_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Username disimpan huruf kecil agar URL profil tidak peka besar-kecil huruf.
     */
    protected function username(): Attribute
    {
        // Nilai null dibiarkan null supaya kolom nullable tetap sah.
        return Attribute::make(set: fn (?string $value) => $value === null ? null : Str::lower($value));
    }

    /**
     * URL avatar siap pakai di tag img, atau null bila pengguna belum mengunggah.
     */
    public function avatarUrl(): ?string
    {
        // Path kosong berarti avatar digantikan inisial nama di komponen avatar.
        return $this->avatar_path === null ? null : Storage::disk('public')->url($this->avatar_path);
    }

    /**
     * URL banner siap pakai di tag img, atau null bila belum diunggah.
     */
    public function bannerUrl(): ?string
    {
        // Pola yang sama dengan avatar: null berarti komponen memakai latar kosong.
        return $this->banner_path === null ? null : Storage::disk('public')->url($this->banner_path);
    }

    /**
     * Profil dibuka lewat username, bukan id, agar URL-nya mudah dibaca dan dibagikan.
     */
    public function getRouteKeyName(): string
    {
        // Kolom ini yang dipakai route model binding untuk /{username}.
        return 'username';
    }

    /**
     * Post yang ditulis pengguna ini.
     */
    public function posts(): HasMany
    {
        // Post yang dihapus memakai soft delete, jadi tidak ikut hilang dari database.
        return $this->hasMany(Post::class);
    }

    /**
     * Post yang disukai pengguna ini.
     */
    public function likes(): BelongsToMany
    {
        // Tabel penghubung post_likes tanpa model karena hanya menyimpan pasangan id.
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    /**
     * Pengguna yang diikuti.
     */
    public function following(): BelongsToMany
    {
        // Kunci asing diberi nama eksplisit karena kedua sisi menunjuk tabel users.
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    /**
     * Pengguna yang mengikuti akun ini.
     */
    public function followers(): BelongsToMany
    {
        // Urutan kolom dibalik dari relasi following.
        return $this->belongsToMany(self::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }
}
