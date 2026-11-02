<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Titik masuk penyemaian data contoh.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seeder yang dibutuhkan aplikasi.
     */
    public function run(): void
    {
        // Data contoh dipisah supaya bisa dijalankan sendiri lewat --class.
        $this->call(DemoSeeder::class);
    }
}
