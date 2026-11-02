<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Livewire\Composer;
use App\Livewire\Thread;
use App\Livewire\Timeline;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Support\PostCardStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_tweet_with_images_and_hashtags_is_saved(): void
    {
        // Disk palsu supaya berkas uji tidak menumpuk di storage asli.
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        // Satu post dengan dua gambar dan dua tag.
        Livewire::test(Composer::class)
            ->set('body', 'Belajar #Laravel dan #Livewire hari ini')
            ->set('media', [
                UploadedFile::fake()->image('satu.jpg', 400, 300),
                UploadedFile::fake()->image('dua.jpg', 400, 300),
            ])
            ->call('kirim')
            ->assertHasNoErrors();

        $post = Post::firstOrFail();

        // Isi, gambar, dan tag tersimpan bersama.
        $this->assertSame('Belajar #Laravel dan #Livewire hari ini', $post->body);
        $this->assertCount(2, $post->media);
        $this->assertEqualsCanonicalizing(['laravel', 'livewire'], $post->hashtags->pluck('tag')->all());

        // Berkas gambar benar-benar ada di disk.
        Storage::disk('public')->assertExists($post->media->first()->path);
    }

    public function test_body_longer_than_250_graphemes_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        // 251 karakter melewati batas satu karakter.
        Livewire::test(Composer::class)
            ->set('body', str_repeat('a', 251))
            ->call('kirim')
            ->assertHasErrors('body');

        // Tidak ada baris yang tersimpan saat validasi gagal.
        $this->assertSame(0, Post::count());
    }

    public function test_emoji_are_counted_as_single_characters(): void
    {
        $this->actingAs(User::factory()->create());

        // Emoji keluarga terdiri dari beberapa karakter tetapi satu grapheme.
        Livewire::test(Composer::class)
            ->set('body', str_repeat('👨‍👩‍👧', 250))
            ->call('kirim')
            ->assertHasNoErrors();

        $this->assertSame(1, Post::count());
    }

    public function test_empty_post_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        // Tanpa teks dan tanpa gambar tidak ada yang bisa disimpan.
        Livewire::test(Composer::class)
            ->call('kirim')
            ->assertHasErrors('body');

        $this->assertSame(0, Post::count());
    }

    public function test_composer_resets_after_sending(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Composer::class)
            ->set('body', 'Satu post')
            ->call('kirim')
            ->assertSet('body', '')
            ->assertSet('media', []);
    }

    public function test_timeline_shows_posts(): void
    {
        // Post dari pengguna lain tetap muncul di feed global.
        Post::factory()->create(['body' => 'Halo dari pengguna lain']);

        $this->actingAs(User::factory()->create());

        Livewire::test(Timeline::class)->assertSee('Halo dari pengguna lain');
    }

    public function test_thread_shows_replies(): void
    {
        // Balasan menempel ke post induk lewat parent_id.
        $induk = Post::factory()->create(['body' => 'Post induk']);

        Post::factory()->create([
            'type' => PostType::Reply,
            'parent_id' => $induk->id,
            'body' => 'Balasan pertama',
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('posts.show', $induk))
            ->assertOk()
            ->assertSee('Balasan pertama');
    }

    public function test_thread_marks_deleted_parent(): void
    {
        // Balasan tetap bisa dibuka walau induknya sudah dihapus.
        $induk = Post::factory()->create();
        $balasan = Post::factory()->create([
            'type' => PostType::Reply,
            'parent_id' => $induk->id,
            'body' => 'Balasan yatim',
        ]);

        $induk->delete();

        $this->actingAs(User::factory()->create());

        // Halaman memberi tahu bahwa konteks induknya sudah hilang.
        $this->get(route('posts.show', $balasan))
            ->assertOk()
            ->assertSee('Post induk sudah dihapus.');
    }

    public function test_reply_from_thread_is_saved_under_parent(): void
    {
        $this->actingAs(User::factory()->create());

        // Post induk milik pengguna lain supaya jelas siapa membalas siapa.
        $induk = Post::factory()->create(['body' => 'Post induk']);

        // Formulir di halaman thread berperan sebagai balasan.
        Livewire::test(Composer::class, ['parent' => $induk, 'tipe' => 'reply'])
            ->set('body', 'Balasan baru')
            ->call('kirim')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'type' => PostType::Reply->value,
            'parent_id' => $induk->id,
            'body' => 'Balasan baru',
        ]);
    }

    public function test_quote_without_text_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $induk = Post::factory()->create();

        // Kutipan tanpa teks sama saja dengan repost.
        Livewire::test(Composer::class, ['parent' => $induk, 'tipe' => 'quote'])
            ->call('kirim')
            ->assertHasErrors('body');

        $this->assertSame(0, Post::where('type', PostType::Quote)->count());
    }

    public function test_quote_shows_quoted_post_in_feed(): void
    {
        $this->actingAs(User::factory()->create());

        $induk = Post::factory()->create(['body' => 'Isi yang dikutip']);

        // Kutipan menyimpan teks sendiri dan menunjuk post induk.
        Livewire::test(Composer::class, ['parent' => $induk, 'tipe' => 'quote'])
            ->set('body', 'Komentar saya')
            ->call('kirim')
            ->assertHasNoErrors();

        // Feed menampilkan teks kutipan sekaligus isi post yang dikutip.
        Livewire::test(Timeline::class)
            ->assertSee('Komentar saya')
            ->assertSee('Isi yang dikutip');
    }

    public function test_repost_disappears_on_second_click(): void
    {
        $this->actingAs(User::factory()->create());

        $induk = Post::factory()->create(['body' => 'Post yang direpost']);

        // Klik pertama membuat repost.
        Livewire::test(Timeline::class)->call('repost', $induk->id);

        $this->assertSame(1, Post::where('type', PostType::Repost)->count());

        // Klik kedua menghapus repost yang dibuat pengguna yang sama.
        Livewire::test(Timeline::class)->call('repost', $induk->id);

        $this->assertSame(0, Post::where('type', PostType::Repost)->count());
    }

    public function test_repost_is_shown_with_original_author(): void
    {
        $this->actingAs(User::factory()->create());

        // Penulis asli berbeda dari pengguna yang merepost.
        $penulis = User::factory()->create(['name' => 'Penulis Asli']);
        $induk = Post::factory()->for($penulis)->create(['body' => 'Tulisan asli']);

        Livewire::test(Thread::class, ['post' => $induk])->call('repost', $induk->id);

        // Baris repost menampilkan penanda dan isi post aslinya.
        Livewire::test(Timeline::class)
            ->assertSee('Direpost oleh')
            ->assertSee('Tulisan asli');
    }

    public function test_tweet_cannot_have_parent(): void
    {
        $this->actingAs(User::factory()->create());

        $induk = Post::factory()->create();

        // Tweet biasa tidak boleh menempel pada post lain.
        Livewire::test(Composer::class, ['parent' => $induk])
            ->set('body', 'Tweet nyasar')
            ->call('kirim')
            ->assertHasErrors('body');

        $this->assertSame(1, Post::count());
    }

    public function test_composer_continues_as_thread_after_first_send(): void
    {
        $this->actingAs(User::factory()->create());

        // Kiriman kedua di komponen yang sama menyambung kiriman pertama.
        Livewire::test(Composer::class)
            ->set('body', 'Bagian pertama')
            ->call('kirim')
            ->set('body', 'Bagian kedua')
            ->call('kirim');

        $pertama = Post::where('body', 'Bagian pertama')->firstOrFail();
        $kedua = Post::where('body', 'Bagian kedua')->firstOrFail();

        // Kiriman lanjutan tersimpan sebagai balasan ke kiriman sebelumnya.
        $this->assertSame(PostType::Reply, $kedua->type);
        $this->assertSame($pertama->id, $kedua->parent_id);
    }

    public function test_berhenti_merantai_returns_composer_to_plain_tweet(): void
    {
        $this->actingAs(User::factory()->create());

        // Rantai diakhiri sebelum kiriman ketiga.
        Livewire::test(Composer::class)
            ->set('body', 'Awal thread')
            ->call('kirim')
            ->call('berhentiMerantai')
            ->set('body', 'Post lepas')
            ->call('kirim');

        // Setelah diakhiri, kiriman berdiri sendiri sebagai tweet.
        $this->assertSame(PostType::Tweet, Post::where('body', 'Post lepas')->firstOrFail()->type);
    }

    public function test_thread_lists_replies_but_not_quotes(): void
    {
        $this->actingAs(User::factory()->create());

        $induk = Post::factory()->create();

        // Balasan dan kutipan sama-sama menunjuk induk, tetapi hanya balasan yang tampil.
        Post::factory()->create(['type' => PostType::Reply, 'parent_id' => $induk->id, 'body' => 'Balasan tampil']);
        Post::factory()->create(['type' => PostType::Quote, 'parent_id' => $induk->id, 'body' => 'Komentar kutipan']);

        $this->get(route('posts.show', $induk))
            ->assertOk()
            ->assertSee('Balasan tampil')
            ->assertDontSee('Komentar kutipan');
    }

    public function test_thread_shows_parent_chain_above_the_post(): void
    {
        $this->actingAs(User::factory()->create());

        // Rantai tiga tingkat: tweet awal, balasan, lalu balasan dari balasan.
        $awal = Post::factory()->create(['body' => 'Awal utas']);
        $tengah = Post::factory()->create(['type' => PostType::Reply, 'parent_id' => $awal->id, 'body' => 'Bagian tengah']);
        $akhir = Post::factory()->create(['type' => PostType::Reply, 'parent_id' => $tengah->id, 'body' => 'Bagian akhir']);

        $this->get(route('posts.show', $akhir))
            ->assertOk()
            ->assertSee('Awal utas')
            ->assertSee('Bagian tengah');
    }

    public function test_reply_from_thread_keeps_replying_to_same_post(): void
    {
        $this->actingAs(User::factory()->create());

        $induk = Post::factory()->create();

        // Dua kiriman berurutan dari kotak balas halaman utas.
        Livewire::test(Composer::class, ['parent' => $induk, 'tipe' => 'reply'])
            ->set('body', 'Balasan satu')
            ->call('kirim')
            ->set('body', 'Balasan dua')
            ->call('kirim')
            ->assertSet('merantai', false);

        // Keduanya tetap membalas post induk, bukan berantai ke balasan pertama.
        $this->assertSame(2, Post::where('parent_id', $induk->id)->where('type', PostType::Reply)->count());
    }

    public function test_card_status_counts_replies_and_reposts(): void
    {
        $pemilik = User::factory()->create();
        $induk = Post::factory()->for($pemilik)->create();

        // Dua balasan dan satu repost menempel pada post yang sama.
        Post::factory()->count(2)->create(['type' => PostType::Reply, 'parent_id' => $induk->id]);
        Post::factory()->create(['type' => PostType::Repost, 'parent_id' => $induk->id]);

        $status = PostCardStatus::untuk(collect([$induk]), $pemilik);

        // Hitungan diambil sekali untuk seluruh halaman.
        $this->assertSame(2, (int) $status['jumlahBalas'][$induk->id]);
        $this->assertSame(1, (int) $status['jumlahRepost'][$induk->id]);
    }

    public function test_owner_deletes_post_from_the_card_menu(): void
    {
        $pemilik = User::factory()->create();
        $post = Post::factory()->for($pemilik)->create(['body' => 'Post yang akan dihapus']);

        $this->actingAs($pemilik);

        // Menu tiga titik memakai aturan hapus yang sama dengan halaman edit.
        Livewire::test(Timeline::class)->call('hapus', $post->id);

        // Post hanya ditandai terhapus agar balasannya tetap punya induk.
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_other_user_cannot_delete_someone_elses_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create());

        // Aksi ditolak walau menunya tidak pernah tampil bagi orang lain.
        Livewire::test(Timeline::class)->call('hapus', $post->id);

        // Post tetap utuh karena policy menolak sebelum penghapusan.
        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_body_links_mentions_to_profiles(): void
    {
        $saya = User::factory()->create();
        $disebut = User::factory()->create(['username' => 'budi']);

        Post::factory()->create(['body' => 'Halo @budi apa kabar']);

        $this->actingAs($saya);

        // Sebutan berubah menjadi tautan ke profil pemiliknya.
        Livewire::test(Timeline::class)->assertSee('href="'.route('profile.show', $disebut).'"', false);
    }

    public function test_composer_can_drop_one_pending_image(): void
    {
        // Disk palsu supaya berkas uji tidak menumpuk di storage asli.
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $komponen = Livewire::test(Composer::class)
            ->set('media', [
                UploadedFile::fake()->image('satu.jpg', 400, 300),
                UploadedFile::fake()->image('dua.jpg', 400, 300),
            ])
            ->call('hapusMedia', 0);

        // Sisa satu gambar dan kuncinya sudah dinomori ulang dari nol.
        $komponen->assertCount('media', 1);
    }

    public function test_home_exposes_write_shortcut_for_the_composer(): void
    {
        $this->actingAs(User::factory()->create());

        // Tombol Post mengirim event yang dipakai kotak tulis untuk mengambil fokus.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('buka-tulis', false)
            ->assertSee('id="tulis"', false);
    }

    public function test_repost_of_deleted_post_no_longer_shows_its_text(): void
    {
        $penulis = User::factory()->create();
        $post = Post::factory()->for($penulis)->create(['body' => 'Isi yang akan ditarik']);

        // Pengguna lain merepost sebelum post aslinya dihapus.
        Post::factory()->create(['type' => PostType::Repost, 'parent_id' => $post->id, 'body' => null]);

        $this->actingAs(User::factory()->create());

        // Selama post aslinya ada, isinya terbaca lewat repost.
        Livewire::test(Timeline::class)->assertSee('Isi yang akan ditarik');

        // Penulis menarik postnya, sementara baris repost tetap tinggal.
        $post->delete();

        // Kartu repost hanya menyisakan keterangan, bukan isi yang sudah ditarik.
        Livewire::test(Timeline::class)
            ->assertDontSee('Isi yang akan ditarik')
            ->assertSee('Post ini sudah tidak tersedia.');
    }

    public function test_deleting_post_removes_its_images(): void
    {
        // Disk palsu supaya berkas uji tidak menumpuk di storage asli.
        Storage::fake('public');

        $pemilik = User::factory()->create();
        $post = Post::factory()->for($pemilik)->create(['body' => 'Post bergambar']);
        $post->media()->create(['path' => 'posts/contoh.jpg', 'position' => 0]);
        Storage::disk('public')->put('posts/contoh.jpg', 'isi');

        $this->actingAs($pemilik);

        Livewire::test(Timeline::class)->call('hapus', $post->id);

        // Baris media dan berkasnya ikut hilang, jadi gambarnya tidak bisa diunduh lagi.
        $this->assertSame(0, Media::where('post_id', $post->id)->count());
        Storage::disk('public')->assertMissing('posts/contoh.jpg');
    }

    public function test_image_larger_than_five_megabytes_is_rejected(): void
    {
        // Disk palsu supaya berkas uji tidak menumpuk di storage asli.
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        // Enam megabyte melewati batas 5 MB yang dipakai aturan unggahan.
        Livewire::test(Composer::class)
            ->set('body', 'Post dengan gambar besar')
            ->set('media', [UploadedFile::fake()->image('besar.jpg', 800, 600)->size(6000)])
            ->assertHasErrors('media')
            ->assertCount('media', 0);

        // Tidak ada post yang tersimpan saat gambarnya ditolak.
        $this->assertSame(0, Post::count());
    }

    public function test_non_image_upload_is_rejected(): void
    {
        // Disk palsu supaya berkas uji tidak menumpuk di storage asli.
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        // Berkas PDF bukan gambar, jadi ditolak sebelum sempat dipratinjau.
        Livewire::test(Composer::class)
            ->set('body', 'Post dengan dokumen')
            ->set('media', [UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')])
            ->assertHasErrors('media')
            ->assertCount('media', 0);

        $this->assertSame(0, Post::count());
    }

    public function test_composer_stops_after_thirty_posts_in_a_minute(): void
    {
        $this->actingAs(User::factory()->create());

        $komponen = Livewire::test(Composer::class);

        // Tiga puluh kiriman pertama masih dilayani.
        for ($ke = 1; $ke <= 30; $ke++) {
            $komponen->set('body', 'Post ke-'.$ke)->call('kirim')->assertHasNoErrors();
        }

        // Kiriman berikutnya ditolak sampai jendela satu menit lewat.
        $komponen->set('body', 'Post ke-31')->call('kirim')->assertHasErrors('body');

        $this->assertSame(30, Post::count());
    }
}
