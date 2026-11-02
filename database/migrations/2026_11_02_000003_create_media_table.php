<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel media untuk gambar yang menempel pada post.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            // Media selalu milik satu post dan ikut terhapus bersamanya.
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            // Path disimpan relatif terhadap disk public, bukan URL penuh.
            $table->string('path');
            // Urutan menentukan susunan gambar di kartu post.
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel media.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
