<?php

namespace Tests\Feature;

use App\Livewire\Composer;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_post_by_body(): void
    {
        Post::factory()->create(['body' => 'Belajar Laravel malam ini']);
        Post::factory()->create(['body' => 'Post lain yang tidak relevan']);

        $this->actingAs(User::factory()->create());

        // Kata kunci bebas mencocokkan isi post.
        $this->get(route('search', ['q' => 'Laravel']))
            ->assertOk()
            ->assertSee('Belajar Laravel malam ini')
            ->assertDontSee('Post lain yang tidak relevan');
    }

    public function test_search_with_hash_uses_hashtag_relation(): void
    {
        $this->actingAs(User::factory()->create());

        // Post bertag dibuat lewat Composer supaya hashtagnya ikut tersinkron.
        Livewire::test(Composer::class)->set('body', 'Hari ini #Laravel')->call('kirim');

        // Post ini menyebut kata laravel tetapi tidak memakai tag.
        Post::factory()->create(['body' => 'Laravel tanpa tag']);

        // Pencarian bertanda # hanya menemukan post yang benar-benar bertag.
        $this->get(route('search', ['q' => '#laravel']))
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertDontSee('Laravel tanpa tag');
    }

    public function test_tag_page_lists_posts_with_that_tag(): void
    {
        $this->actingAs(User::factory()->create());

        // Dua post dengan tag berbeda.
        Livewire::test(Composer::class)->set('body', 'Post tentang #Livewire')->call('kirim');
        Livewire::test(Composer::class)->set('body', 'Post tentang #Laravel')->call('kirim');

        // Halaman tag hanya memuat post dengan tag tersebut.
        // Teks hashtag sudah berubah menjadi tautan, jadi dicocokkan potongan awalnya.
        $this->get(route('tag.show', ['tag' => 'laravel']))
            ->assertOk()
            ->assertSee('Tag #laravel')
            ->assertSee('Post tentang')
            ->assertSee('/tag/laravel', false)
            ->assertDontSee('Post tentang #Livewire');
    }

    public function test_hashtag_in_card_links_to_tag_page(): void
    {
        $this->actingAs(User::factory()->create());

        // Tag disimpan huruf kecil, tautannya juga.
        Livewire::test(Composer::class)->set('body', 'Belajar #Laravel')->call('kirim');

        // Kartu post memuat tautan menuju halaman tag.
        $this->get(route('home'))->assertOk()->assertSee('/tag/laravel', false);
    }
}
