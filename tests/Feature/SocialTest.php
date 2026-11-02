<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Livewire\FollowButton;
use App\Livewire\ProfilePage;
use App\Livewire\Timeline;
use App\Livewire\WhoToFollow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_is_saved_and_survives_reload(): void
    {
        $saya = User::factory()->create();
        $post = Post::factory()->create(['body' => 'Post untuk disukai']);

        $this->actingAs($saya);

        // Klik suka menulis satu baris di tabel penghubung.
        Livewire::test(Timeline::class)->call('like', $post->id);

        $this->assertDatabaseHas('post_likes', ['user_id' => $saya->id, 'post_id' => $post->id]);

        // Muat ulang halaman: label tombol membuktikan suka masih tersimpan.
        $this->get(route('home'))->assertOk()->assertSee('Batalkan suka');
    }

    public function test_like_toggles_off_on_second_click(): void
    {
        $saya = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($saya);

        // Klik pertama menyukai.
        Livewire::test(Timeline::class)->call('like', $post->id);

        $this->assertDatabaseCount('post_likes', 1);

        // Klik kedua membatalkan suka yang sama.
        Livewire::test(Timeline::class)->call('like', $post->id);

        $this->assertDatabaseCount('post_likes', 0);
    }

    public function test_following_feed_only_shows_followed_users(): void
    {
        $saya = User::factory()->create();

        // Satu akun diikuti, satu akun tidak.
        $diikuti = User::factory()->create();
        $lain = User::factory()->create();

        Post::factory()->for($diikuti)->create(['body' => 'Dari akun yang diikuti']);
        Post::factory()->for($lain)->create(['body' => 'Dari akun asing']);
        Post::factory()->for($saya)->create(['body' => 'Post saya sendiri']);

        $saya->following()->attach($diikuti->id);

        $this->actingAs($saya);

        // Tab mengikuti hanya memuat akun yang diikuti dan post sendiri.
        Livewire::test(Timeline::class)
            ->set('lingkup', 'ikuti')
            ->assertSee('Dari akun yang diikuti')
            ->assertSee('Post saya sendiri')
            ->assertDontSee('Dari akun asing');
    }

    public function test_follow_button_toggles_relation(): void
    {
        $saya = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($saya);

        // Klik pertama memasang relasi mengikuti; label mengikuti tampil seterusnya.
        Livewire::test(FollowButton::class, ['user' => $target])
            ->call('ikuti')
            ->assertSee('Berhenti mengikuti');

        $this->assertDatabaseHas('follows', ['follower_id' => $saya->id, 'following_id' => $target->id]);

        // Klik kedua melepas relasi yang sama.
        Livewire::test(FollowButton::class, ['user' => $target])
            ->call('ikuti')
            ->assertSee('Ikuti');

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $saya = User::factory()->create();

        $this->actingAs($saya);

        // Akun sendiri ditolak tanpa error, cukup tidak terjadi apa-apa.
        Livewire::test(FollowButton::class, ['user' => $saya])->call('ikuti');

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_profile_tabs_show_the_right_posts(): void
    {
        $pemilik = User::factory()->create();
        $pengunjung = User::factory()->create();

        // Empat jenis isi: post biasa, balasan, post bergambar, dan post yang disukai.
        $post = Post::factory()->for($pemilik)->create(['body' => 'Post di profil']);
        Post::factory()->for($pemilik)->create([
            'type' => PostType::Reply,
            'parent_id' => $post->id,
            'body' => 'Balasan di profil',
        ]);
        $bergambar = Post::factory()->for($pemilik)->create(['body' => 'Post bergambar']);
        $bergambar->media()->create(['path' => 'media/contoh.jpg', 'position' => 0]);

        $disukai = Post::factory()->create(['body' => 'Post yang disukai pemilik']);
        $pemilik->likes()->attach($disukai->id);

        $this->actingAs($pengunjung);

        // Tab bawaan hanya berisi post yang tampil di feed.
        $this->get(route('profile.show', $pemilik))
            ->assertOk()
            ->assertSee('Post di profil')
            ->assertDontSee('Balasan di profil');

        // Tab balasan memuat balasan yang tidak tampil di tab lain.
        $this->get(route('profile.show', [$pemilik, 'tab' => 'balasan']))
            ->assertOk()
            ->assertSee('Balasan di profil');

        // Tab media hanya memuat post yang punya gambar.
        $this->get(route('profile.show', [$pemilik, 'tab' => 'media']))
            ->assertOk()
            ->assertSee('Post bergambar')
            ->assertDontSee('Post di profil');

        // Tab suka memuat post orang lain yang disukai pemilik profil.
        $this->get(route('profile.show', [$pemilik, 'tab' => 'suka']))
            ->assertOk()
            ->assertSee('Post yang disukai pemilik');
    }

    public function test_profile_followers_and_following_tabs_list_accounts(): void
    {
        $pemilik = User::factory()->create(['name' => 'Pemilik Profil', 'username' => 'pemilik']);
        $pengikut = User::factory()->create(['name' => 'Akun Pengikut', 'username' => 'pengikut']);
        $diikuti = User::factory()->create(['name' => 'Akun Diikuti', 'username' => 'diikuti']);

        // Satu akun mengikuti pemilik, pemilik mengikuti satu akun lain.
        $pengikut->following()->attach($pemilik->id);
        $pemilik->following()->attach($diikuti->id);

        $this->actingAs(User::factory()->create());

        // Tab pengikut hanya memuat akun yang mengikuti pemilik.
        Livewire::test(ProfilePage::class, ['user' => $pemilik, 'tab' => 'pengikut'])
            ->assertSee('Akun Pengikut')
            ->assertDontSee('Akun Diikuti');

        // Tab mengikuti hanya memuat akun yang dipilih pemilik.
        Livewire::test(ProfilePage::class, ['user' => $pemilik, 'tab' => 'mengikuti'])
            ->assertSee('Akun Diikuti')
            ->assertDontSee('Akun Pengikut');
    }

    public function test_timeline_loads_more_on_scroll(): void
    {
        $saya = User::factory()->create();

        $this->actingAs($saya);

        // Dua belas post: sepuluh terbaru tampil dulu, dua tertua menunggu.
        foreach (range(0, 11) as $urutan) {
            // Waktu dibuat dibedakan supaya urutan feed pasti, bukan seri.
            Post::factory()->create(['body' => sprintf('Post urutan %02d', $urutan)])
                ->forceFill(['created_at' => now()->subMinutes(11 - $urutan)])
                ->saveQuietly();
        }

        Livewire::test(Timeline::class)
            ->assertSee('Post urutan 11')
            ->assertDontSee('Post urutan 00')
            ->call('muatLagi')
            ->assertSee('Post urutan 00');
    }

    public function test_who_to_follow_recommends_other_users(): void
    {
        $saya = User::factory()->create(['username' => 'sayapribadi']);
        $target1 = User::factory()->create(['name' => 'Target Satu', 'username' => 'target1']);
        $target2 = User::factory()->create(['name' => 'Target Dua', 'username' => 'target2']);

        $this->actingAs($saya);

        Livewire::test(WhoToFollow::class)
            ->assertSee('Siapa untuk diikuti')
            ->assertSee('Target Satu')
            ->assertSee('Target Dua')
            ->assertDontSee('@sayapribadi');
    }

    public function test_who_to_follow_renders_when_no_other_users(): void
    {
        $saya = User::factory()->create();

        $this->actingAs($saya);

        Livewire::test(WhoToFollow::class)
            ->assertDontSee('Siapa untuk diikuti')
            ->assertOk();

        $this->get(route('home'))->assertOk();
    }
}
