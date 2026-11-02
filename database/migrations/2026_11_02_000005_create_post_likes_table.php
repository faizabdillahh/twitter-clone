<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel suka post.
     */
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table) {
            // Tabel penghubung tanpa id karena pasangan kolomnya sudah unik.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Satu pengguna hanya boleh menyukai satu post sekali.
            $table->primary(['user_id', 'post_id']);
        });
    }

    /**
     * Menghapus tabel suka post.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
