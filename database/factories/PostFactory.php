<?php

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Model yang dibuat factory ini.
     *
     * @var class-string<Post>
     */
    protected $model = Post::class;

    /**
     * Bentuk data post bawaan: tweet biasa dengan satu kalimat teks.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => PostType::Tweet,
            'body' => fake()->sentence(10),
        ];
    }
}
