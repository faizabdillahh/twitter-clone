<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_activities_lists_interactions_on_own_posts(): void
    {
        $saya = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $saya->id]);
        $lain = User::factory()->create(['name' => 'Akun Lain']);

        // Akun lain menyukai post tersebut.
        $post->likes()->attach($lain->id);

        // Akun lain membalas post tersebut.
        Post::factory()->create([
            'user_id' => $lain->id,
            'type' => PostType::Reply,
            'parent_id' => $post->id,
            'body' => 'Balasan untuk Anda',
        ]);

        // Akun lain merepost post tersebut.
        Post::factory()->create([
            'user_id' => $lain->id,
            'type' => PostType::Repost,
            'parent_id' => $post->id,
            'body' => null,
        ]);

        // Akun lain mulai mengikuti pemilik post.
        $lain->following()->attach($saya->id);

        $this->actingAs($saya);

        // Halaman aktivitas menyebut keempat kejadian tersebut.
        $this->get(route('activities'))
            ->assertOk()
            ->assertSee('Akun Lain')
            ->assertSee('menyukai post Anda')
            ->assertSee('membalas post Anda')
            ->assertSee('merepost post Anda')
            ->assertSee('mulai mengikuti Anda');
    }

    public function test_activities_ignores_other_posts_and_own_likes(): void
    {
        $saya = User::factory()->create();
        $postOrang = Post::factory()->create(['body' => 'Post orang lain']);
        $postSaya = Post::factory()->create(['user_id' => $saya->id]);

        // Suka pada post orang lain bukan aktivitas untuk pemilik akun ini.
        $postOrang->likes()->attach($saya->id);

        // Suka dari diri sendiri juga tidak ditampilkan.
        $postSaya->likes()->attach($saya->id);

        $this->actingAs($saya);

        // Tidak ada satu pun baris suka yang lolos.
        $this->get(route('activities'))->assertOk()->assertDontSee('menyukai post Anda');
    }
}
