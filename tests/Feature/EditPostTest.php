<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Livewire\Composer;
use App\Livewire\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_and_hashtags_resync(): void
    {
        $pemilik = User::factory()->create();

        $this->actingAs($pemilik);

        // Post awal memakai satu tag yang nanti harus hilang.
        Livewire::test(Composer::class)->set('body', 'Awal tentang #Laravel')->call('kirim');

        $post = Post::firstOrFail();

        // Isi diganti dengan tag yang berbeda.
        Livewire::test(EditPost::class, ['post' => $post])
            ->set('body', 'Setelah diedit #PHP')
            ->call('simpan')
            ->assertHasNoErrors();

        $post->refresh();

        // Isi baru tersimpan dan post ditandai pernah diedit.
        $this->assertSame('Setelah diedit #PHP', $post->body);
        $this->assertTrue($post->sudahDiedit());

        // Tag lama dibuang, tag baru dipasang.
        $this->assertEqualsCanonicalizing(['php'], $post->hashtags->pluck('tag')->all());
    }

    public function test_other_user_cannot_open_edit_page(): void
    {
        $post = Post::factory()->create(['body' => 'Milik orang lain']);

        $this->actingAs(User::factory()->create());

        // Halaman edit menolak pengunjung yang bukan pemilik.
        $this->get(route('posts.edit', $post))->assertForbidden();
    }

    public function test_owner_can_delete_and_reply_keeps_parent_marker(): void
    {
        $pemilik = User::factory()->create();

        $this->actingAs($pemilik);

        // Post induk dengan satu balasan.
        Livewire::test(Composer::class)->set('body', 'Post induk untuk dihapus')->call('kirim');
        $induk = Post::firstOrFail();

        $balasan = Post::factory()->create([
            'type' => PostType::Reply,
            'parent_id' => $induk->id,
            'body' => 'Balasan yang bertahan',
        ]);

        // Hapus dari halaman edit milik sendiri.
        Livewire::test(EditPost::class, ['post' => $induk])->call('hapus');

        // Post terhapus lunak, jadi balasannya masih punya jejak induk.
        $this->assertSoftDeleted('posts', ['id' => $induk->id]);

        // Feed tidak lagi memuat post yang sudah dihapus.
        $this->get(route('home'))->assertOk()->assertDontSee('Post induk untuk dihapus');

        // Halaman balasan memberi tahu bahwa induknya sudah hilang.
        $this->get(route('posts.show', $balasan))->assertOk()->assertSee('Post induk sudah dihapus.');
    }

    public function test_repost_cannot_be_edited(): void
    {
        $pemilik = User::factory()->create();
        $induk = Post::factory()->create();

        // Repost tidak punya teks sendiri.
        $repost = Post::factory()->create([
            'user_id' => $pemilik->id,
            'type' => PostType::Repost,
            'parent_id' => $induk->id,
            'body' => null,
        ]);

        // Kebijakan menolak edit repost, tetapi tetap mengizinkan hapus.
        $this->assertFalse($pemilik->can('update', $repost));
        $this->assertTrue($pemilik->can('delete', $repost));
    }
}
