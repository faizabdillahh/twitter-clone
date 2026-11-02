<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel posts untuk tweet, balasan, repost, dan kutipan.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            // Pemilik post; akun yang dihapus membawa serta seluruh postnya.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Tipe menentukan aturan validasi dan tampilan, bukan skema.
            $table->enum('type', ['tweet', 'reply', 'repost', 'quote'])->default('tweet');
            // Post induk untuk balasan, kutipan, dan repost.
            $table->foreignId('parent_id')->nullable()->constrained('posts')->cascadeOnDelete();
            // Repost tidak punya teks sendiri, jadi body boleh kosong.
            $table->text('body')->nullable();
            // Kunci unik mencegah satu pengguna merepost post yang sama dua kali.
            $table->string('repost_key')->nullable()->unique();
            // Diisi saat body diubah, dipakai untuk penanda "diedit".
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Feed dan profil selalu membaca post terbaru milik satu pengguna.
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Menghapus tabel posts.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
