<?php

namespace App\Livewire;

use App\Livewire\Concerns\ValidatesPostInput;
use App\Models\Post;
use App\Support\HashtagSync;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman edit satu post milik sendiri, sekaligus tempat menghapusnya.
 *
 * Hak akses diperiksa dua kali: saat halaman dibuka dan saat aksi dijalankan,
 * karena aksi Livewire bisa datang tanpa melewati mount.
 */
#[Layout('components.layouts.app')]
class EditPost extends Component
{
    // Aturan body, panjang, dan kombinasi jenis dipakai bersama Composer.
    use ValidatesPostInput;

    public Post $post; // Post yang sedang diedit.

    /**
     * Menolak pengunjung yang bukan pemilik, lalu menyiapkan isi formulir.
     */
    public function mount(Post $post): void
    {
        // Pengunjung selain pemilik mendapat 403 sebelum melihat apa pun.
        $this->authorize('update', $post);

        // Post disimpan sebagai properti supaya view bisa memakainya.
        $this->post = $post;

        // Isi awal formulir adalah body yang tersimpan.
        $this->body = $post->body ?? '';

        // Jenis dan induk dibutuhkan aturan kombinasi di trait.
        $this->tipe = $post->type->value;
        // Induk hanya ada pada balasan dan kutipan.
        $this->parent = $post->parent;
    }

    /**
     * Menyimpan perubahan isi beserta hashtag yang baru.
     */
    public function simpan(HashtagSync $hashtags): void
    {
        // Hak akses diperiksa ulang sebelum menyimpan.
        $this->authorize('update', $this->post);

        // Spasi di ujung tidak dihitung sebagai isi.
        $this->body = trim($this->body);

        // Aturan body dan panjang berlaku sama seperti saat menulis.
        $this->validate($this->aturanBody());
        // Panjang dihitung per grapheme, sama dengan Composer.
        $this->pastikanPanjangBody();
        // Jenis dan induk tetap harus cocok setelah diedit.
        $this->pastikanKombinasi();

        // Post yang diedit tidak boleh menjadi kosong.
        if ($this->body === '') {
            $this->addError('body', 'Isi post tidak boleh kosong.');

            // Berhenti sebelum menyentuh database.
            return;
        }

        // Tanpa perubahan, tidak ada yang perlu ditulis maupun ditandai.
        if ($this->body === $this->post->body) {
            $this->redirect(route('posts.show', $this->post), navigate: true);

            // Pengguna langsung dibawa kembali ke halaman detail.
            return;
        }

        // Isi dan hashtag berubah bersama atau tidak sama sekali.
        DB::transaction(function () use ($hashtags) {
            $this->post->update(['body' => $this->body, 'edited_at' => now()]);
            // Tag lama dibuang dan diganti tag dari body yang baru.
            $hashtags->sync($this->post, $this->post->body);
        });

        // Kembali ke halaman detail setelah tersimpan.
        $this->redirect(route('posts.show', $this->post), navigate: true);
    }

    /**
     * Menghapus post milik sendiri.
     */
    public function hapus(): void
    {
        // Hanya pemilik yang boleh menghapus.
        $this->authorize('delete', $this->post);

        // Soft delete menjaga balasan tetap punya jejak induknya.
        $this->post->delete();

        // Feed menjadi tujuan setelah post hilang dari daftar.
        $this->redirect(route('home'), navigate: true);
    }

    /**
     * Menampilkan formulir edit.
     */
    public function render(): View
    {
        // View hanya butuh post dan body yang sedang diketik.
        return view('livewire.edit-post');
    }
}
