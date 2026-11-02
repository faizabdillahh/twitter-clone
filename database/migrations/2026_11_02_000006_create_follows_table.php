<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel hubungan mengikuti antar pengguna.
     */
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            // follower_id adalah yang mengikuti, following_id adalah yang diikuti.
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Satu pengguna hanya boleh mengikuti orang yang sama sekali.
            $table->primary(['follower_id', 'following_id']);
        });
    }

    /**
     * Menghapus tabel hubungan mengikuti.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
