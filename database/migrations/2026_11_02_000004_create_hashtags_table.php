<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel hashtag beserta tabel penghubungnya ke posts.
     */
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            // Tag disimpan tanpa tanda # dan selalu huruf kecil.
            $table->string('tag', 50)->unique();
            $table->timestamps();
        });

        Schema::create('hashtag_post', function (Blueprint $table) {
            // Tabel penghubung tanpa id karena pasangan kolomnya sudah unik.
            $table->foreignId('hashtag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->primary(['hashtag_id', 'post_id']);
        });
    }

    /**
     * Menghapus tabel penghubung lebih dulu, lalu tabel hashtag.
     */
    public function down(): void
    {
        Schema::dropIfExists('hashtag_post');
        Schema::dropIfExists('hashtags');
    }
};
