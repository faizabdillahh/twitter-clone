<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_fills_feed_with_conversation(): void
    {
        $this->seed(DemoSeeder::class);

        // Tiga akun demo dengan username yang bisa dipanggil di URL.
        $this->assertSame(3, User::count());
        $this->assertNotNull(User::where('username', 'andi')->first());

        // Setiap pengguna membuat tepat 5 postingan, 3 quote, 3 repost, dan 3 reply.
        foreach (User::all() as $user) {
            $this->assertSame(5, $user->posts()->where('type', PostType::Tweet)->count());
            $this->assertSame(3, $user->posts()->where('type', PostType::Quote)->count());
            $this->assertSame(3, $user->posts()->where('type', PostType::Repost)->count());
            $this->assertSame(3, $user->posts()->where('type', PostType::Reply)->count());
        }

        // Feed tidak kosong dan berisi balasan serta repost.
        $this->assertTrue(Post::feed()->exists());
        $this->assertTrue(Post::where('type', PostType::Reply)->exists());
        $this->assertTrue(Post::where('type', PostType::Repost)->exists());

        // Follow dan suka ikut terisi supaya tab Mengikuti ada isinya.
        $this->assertDatabaseCount('follows', 4);
        $this->assertTrue(Post::first()->likes()->exists());
    }
}
