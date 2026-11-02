<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom identitas publik di tabel users.
     *
     * Kolom-kolom ini dipisah dari `name` karena masing-masing punya batas
     * panjang sendiri dan tampil di bagian berbeda pada halaman profil.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Username menjadi alamat profil, karena itu unik dan dibatasi 20 karakter.
            $table->string('username', 20)->nullable()->unique()->after('name');
            // Bio mengikuti batas 160 karakter agar profil tetap ringkas.
            $table->string('bio', 160)->nullable()->after('username');
            // Lokasi berupa teks bebas, misalnya kota atau negara.
            $table->string('location', 60)->nullable()->after('bio');
            // Situs disimpan sudah lengkap dengan skema https:// oleh komponen profil.
            $table->string('website', 120)->nullable()->after('location');
            // Path disimpan relatif terhadap disk `public`, bukan URL penuh,
            // supaya domain bisa berubah tanpa menyentuh database.
            $table->string('avatar_path')->nullable()->after('website');
            // Banner memakai pola simpan yang sama dengan avatar.
            $table->string('banner_path')->nullable()->after('avatar_path');
        });
    }

    /**
     * Menghapus kembali kolom identitas publik.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Indeks unik ikut terhapus bersama kolomnya.
            $table->dropColumn(['username', 'bio', 'location', 'website', 'avatar_path', 'banner_path']);
        });
    }
};
