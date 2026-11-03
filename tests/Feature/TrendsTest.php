<?php

namespace Tests\Feature;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendsTest extends TestCase
{
    use RefreshDatabase;

    public function test_trends_lists_most_used_hashtags_first(): void
    {
        $saya = User::factory()->create();
        $laravel = Hashtag::create(['tag' => 'laravel']);
        $desain = Hashtag::create(['tag' => 'desain']);

        // Tiga post memakai #laravel dan satu post memakai #desain.
        $laravel->posts()->attach(Post::factory()->count(3)->create()->pluck('id')->all());
        $desain->posts()->attach(Post::factory()->create()->id);

        $this->actingAs($saya);

        // Widget tren menyebut kedua tag, dengan jumlah post terbanyak lebih dulu.
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Tren untuk Anda', '#laravel', '3 post', '#desain', '1 post']);
    }

    public function test_trend_links_to_its_tag_page(): void
    {
        $saya = User::factory()->create();
        $tag = Hashtag::create(['tag' => 'usk']);
        $tag->posts()->attach(Post::factory()->create(['body' => 'Kuliah di #usk'])->id);

        $this->actingAs($saya);

        // Setiap baris tren membuka halaman tag yang sama dengan tautan hashtag.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('tag.show', 'usk'));
    }

    public function test_trends_shows_only_four_hashtags(): void
    {
        $saya = User::factory()->create();
        $post = Post::factory()->count(6)->create();

        // Tag diberi jumlah post menurun supaya urutan peringkatnya pasti.
        foreach (['satu', 'dua', 'tiga', 'empat', 'lima', 'enam'] as $i => $nama) {
            Hashtag::create(['tag' => $nama])->posts()->attach($post->take(6 - $i)->pluck('id')->all());
        }

        $this->actingAs($saya);

        // Widget hanya memuat empat baris, jadi dua tag terbawah tidak tampil.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('#satu')
            ->assertSee('#dua')
            ->assertSee('#tiga')
            ->assertSee('#empat')
            ->assertDontSee('#lima')
            ->assertDontSee('#enam');
    }
}
