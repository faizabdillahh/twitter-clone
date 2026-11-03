<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah nama berkas asli pada media, dipakai sebagai label lampiran.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // Path tersimpan berupa nama acak, jadi nama asli disimpan terpisah.
            $table->string('name')->nullable()->after('path');
        });
    }

    /**
     * Membuang kolom nama berkas asli.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // Kolom tambahan dihapus kembali saat migrasi dibatalkan.
            $table->dropColumn('name');
        });
    }
};
