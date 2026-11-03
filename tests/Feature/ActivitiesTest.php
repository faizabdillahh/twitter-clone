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

    public function test_own_likes_are_listed_as_outgoing_rows(): void
    {
        $saya = User::factory()->create();
        $postOrang = Post::factory()->create(['body' => 'Post orang lain']);
        $postSaya = Post::factory()->create(['user_id' => $saya->id]);

        // Suka pada post orang lain tercatat sebagai tindakan sendiri.
        $postOrang->likes()->attach($saya->id);

        // Suka pada post sendiri juga tercatat, tetapi bukan sebagai aktivitas masuk.
        $postSaya->likes()->attach($saya->id);

        $this->actingAs($saya);

        // Baris keluar menyebut pemilik post; baris masuk dari diri sendiri tidak dibuat.
        $this->get(route('activities'))
            ->assertOk()
            ->assertSee('menyukai post @'.$postOrang->user->username)
            ->assertDontSee('menyukai post Anda');
    }

    public function test_own_replies_and_follows_appear_in_activities(): void
    {
        $saya = User::factory()->create();
        $lain = User::factory()->create(['username' => 'akun_lain']);
        $postLain = Post::factory()->create(['user_id' => $lain->id]);

        // Pengguna membalas post orang lain.
        Post::factory()->create([
            'user_id' => $saya->id,
            'type' => PostType::Reply,
            'parent_id' => $postLain->id,
            'body' => 'Balasan saya',
        ]);

        // Pengguna juga mengikuti pemilik post tersebut.
        $saya->following()->attach($lain->id);

        $this->actingAs($saya);

        // Kedua tindakan itu muncul sebagai baris milik sendiri.
        $this->get(route('activities'))
            ->assertOk()
            ->assertSee('membalas post @akun_lain')
            ->assertSee('mulai mengikuti @akun_lain');
    }

    public function test_mention_tab_lists_posts_that_mention_me(): void
    {
        $saya = User::factory()->create(['username' => 'saya_ku']);
        $lain = User::factory()->create(['name' => 'Akun Lain']);

        // Akun lain menyebut username pemilik halaman, termasuk garis bawahnya.
        Post::factory()->create(['user_id' => $lain->id, 'body' => 'Halo @saya_ku apa kabar']);

        $this->actingAs($saya);

        // Tab sebutan hanya memuat post yang menyebut akun ini.
        $this->get(route('activities', ['tab' => 'sebutan']))
            ->assertOk()
            ->assertSee('menyebut Anda')
            ->assertSee('Halo @saya_ku apa kabar');
    }

    public function test_mention_tab_ignores_other_usernames(): void
    {
        $saya = User::factory()->create(['username' => 'saya']);
        $lain = User::factory()->create();

        // Sebutan untuk akun yang mirip tidak boleh ikut terbawa.
        Post::factory()->create(['user_id' => $lain->id, 'body' => 'Halo @sayang']);

        $this->actingAs($saya);

        // Tab sebutan tetap kosong karena tidak ada sebutan yang tepat.
        $this->get(route('activities', ['tab' => 'sebutan']))
            ->assertOk()
            ->assertSee('Belum ada sebutan');
    }
}
