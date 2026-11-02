<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks untuk query feed global dan halaman aktivitas.
 *
 * Tanpa indeks ini, feed global mengurutkan seluruh baris posts dan
 * halaman aktivitas memindai penuh post_likes serta follows.
 */
return new class extends Migration
{
    /**
     * Menambah indeks pada kolom yang dipakai penyaringan dan pengurutan.
     */
    public function up(): void
    {
        // Feed global selalu diurutkan created_at menurun, tanpa saringan user.
        Schema::table('posts', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Aktivitas menyaring suka berdasarkan post_id, bukan user_id.
        Schema::table('post_likes', function (Blueprint $table) {
            $table->index('post_id');
        });

        // Daftar pengikut menyaring following_id, kolom belakang primary key.
        Schema::table('follows', function (Blueprint $table) {
            $table->index('following_id');
        });
    }

    /**
     * Menghapus kembali indeks yang ditambahkan di up().
     */
    public function down(): void
    {
        // Indeks created_at tidak dipakai kunci asing, jadi bisa langsung dihapus.
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        // Kunci asing post_id bersandar pada indeks ini, jadi dilepas lebih dulu.
        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropIndex(['post_id']);
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
        });

        // Pola yang sama untuk following_id di tabel follows.
        Schema::table('follows', function (Blueprint $table) {
            $table->dropForeign(['following_id']);
            $table->dropIndex(['following_id']);
            $table->foreign('following_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
