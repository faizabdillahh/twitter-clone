<?php

namespace Database\Seeders;

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use App\Support\PostWriter;
use Illuminate\Database\Seeder;

/**
 * Mengisi database dengan akun demo dan konten percakapan lengkap.
 *
 * Tiga akun demo membuat masing-masing 5 postingan, 3 quote, 3 repost, dan 3 reply.
 */
class DemoSeeder extends Seeder
{
    /**
     * Menjalankan seeder data demo.
     */
    public function run(): void
    {
        // Penulis post dipakai agar relasi media dan hashtag diproses otomatis.
        $penulis = app(PostWriter::class);

        // Akun Andi Pratama dengan username tetap untuk testing.
        $andi = User::factory()->create([
            'name' => 'Andi Pratama',
            'username' => 'andi',
            'email' => 'andi@example.com',
            'bio' => 'Menulis Laravel dan merawat server kecil-kecilan.',
            'location' => 'Surabaya',
        ]);

        // Akun Bunga Lestari sebagai desainer antarmuka.
        $bunga = User::factory()->create([
            'name' => 'Bunga Lestari',
            'username' => 'bunga',
            'email' => 'bunga@example.com',
            'bio' => 'Desainer antarmuka, penggemar tipografi.',
            'location' => 'Yogyakarta',
        ]);

        // Akun Cakra Wijaya sebagai pengembang web Livewire.
        $cakra = User::factory()->create([
            'name' => 'Cakra Wijaya',
            'username' => 'cakra',
            'email' => 'cakra@example.com',
            'bio' => 'Belajar Livewire sambil membangun produk sendiri.',
            'location' => 'Bandung',
        ]);

        // Postingan 1 milik Andi: tweet arsitektur database.
        $andiT1 = $penulis->tulis($andi, [
            'body' => 'Baru selesai merapikan feed dengan satu tabel posts. Satu tabel, empat tipe. #laravel #arsitektur',
        ]);

        // Postingan 2 milik Andi: tweet optimasi query indeks.
        $andiT2 = $penulis->tulis($andi, [
            'body' => 'Optimasi indeks database di MySQL 8: urutan kolom menentukan segalanya. #database #mysql',
        ]);

        // Postingan 3 milik Andi: tweet pengalaman Livewire 3.
        $andiT3 = $penulis->tulis($andi, [
            'body' => 'Livewire 3 terasa sangat cepat saat dipadukan dengan Alpine.js untuk interaksi mikro. #livewire',
        ]);

        // Postingan 4 milik Andi: tweet deploy Docker di server kampus.
        $andiT4 = $penulis->tulis($andi, [
            'body' => 'Server deployment dengan Docker swarm ternyata cukup stabil untuk traffic kampus. #devops',
        ]);

        // Postingan 5 milik Andi: tweet suasana pagi di Aceh.
        $andiT5 = $penulis->tulis($andi, [
            'body' => 'Kopi pagi di Darussalam sebelum mulai ngoding modul baru. Semangat semuanya! #usk #aceh',
        ]);

        // Postingan 1 milik Bunga: tweet tipografi antarmuka.
        $bungaT1 = $penulis->tulis($bunga, [
            'body' => 'Mencoba skala tipografi baru untuk halaman profil. Huruf besar tidak selalu lebih baik. #desain #ui',
        ]);

        // Postingan 2 milik Bunga: tweet antarmuka minimalis gaya X.
        $bungaT2 = $penulis->tulis($bunga, [
            'body' => 'Desain antarmuka minimalis ala X ini membuat konten teks terasa lebih hidup dan fokus. #uiux',
        ]);

        // Postingan 3 milik Bunga: tweet tema gelap kontras OLED.
        $bungaT3 = $penulis->tulis($bunga, [
            'body' => 'Warna gelap #16181c dan #000000 memberikan kontras sempurna untuk layar OLED. #design',
        ]);

        // Postingan 4 milik Bunga: tweet pembuatan ikon vektor navigasi.
        $bungaT4 = $penulis->tulis($bunga, [
            'body' => 'Menggambar ikon vektor baru untuk navigasi aplikasi kampus. Detail kecil itu penting. #svg #vektor',
        ]);

        // Postingan 5 milik Bunga: tweet suasana senja di perpustakaan kampus.
        $bungaT5 = $penulis->tulis($bunga, [
            'body' => 'Senja di perpustakaan USK selalu menenangkan untuk mencari inspirasi visual baru. #usk #desain',
        ]);

        // Postingan 1 milik Cakra: tweet pengalaman wire model live.
        $cakraT1 = $penulis->tulis($cakra, [
            'body' => 'Malam ini belajar wire:model.live. Ternyata penghitung karakter jadi jauh lebih enak dipakai. #livewire',
        ]);

        // Postingan 2 milik Cakra: tweet scroll tak terbatas dengan Alpine.
        $cakraT2 = $penulis->tulis($cakra, [
            'body' => 'Implementasi infinite scroll di Livewire dengan x-intersect berjalan mulus tanpa lag. #alpinejs',
        ]);

        // Postingan 3 milik Cakra: tweet tes otomatis Pest dan PHPUnit.
        $cakraT3 = $penulis->tulis($cakra, [
            'body' => 'Testing dengan Pest dan PHPUnit di Laravel selalu memuaskan saat semua hijau. #php #tdd',
        ]);

        // Postingan 4 milik Cakra: tweet soft delete pada utas diskusi.
        $cakraT4 = $penulis->tulis($cakra, [
            'body' => 'Mempelajari cara kerja Eloquent soft deletes dan relasi withTrashed untuk thread diskusi. #laravel',
        ]);

        // Postingan 5 milik Cakra: tweet diskusi teknologi di gedung AAC.
        $cakraT5 = $penulis->tulis($cakra, [
            'body' => 'Diskusi seru di gedung AAC Dayan Dawood tentang masa depan web engineering di Aceh. #usk #teknologi',
        ]);

        // Balasan 1 milik Andi: membalas tweet tipografi Bunga.
        $penulis->tulis($andi, [
            'type' => PostType::Reply,
            'parent_id' => $bungaT1->id,
            'body' => 'Setuju, hierarki visual dan keterbacaan jauh lebih penting dibanding ukuran font.',
        ]);

        // Balasan 2 milik Andi: membalas tweet karakter Cakra.
        $penulis->tulis($andi, [
            'type' => PostType::Reply,
            'parent_id' => $cakraT1->id,
            'body' => 'Penghitung karakter grapheme juga mencegah bug emoji yang terpotong!',
        ]);

        // Balasan 3 milik Andi: membalas tweet tema OLED Bunga.
        $penulis->tulis($andi, [
            'type' => PostType::Reply,
            'parent_id' => $bungaT3->id,
            'body' => 'Kontras gelapnya pas di mata, tidak bikin cepat lelah saat sesi coding panjang.',
        ]);

        // Balasan 1 milik Bunga: membalas tweet arsitektur Andi.
        $penulis->tulis($bunga, [
            'type' => PostType::Reply,
            'parent_id' => $andiT1->id,
            'body' => 'Arsitektur satu tabel ini membuat relasi balasan dan kutipan jauh lebih ringkas! #laravel',
        ]);

        // Balasan 2 milik Bunga: membalas tweet scroll Cakra.
        $penulis->tulis($bunga, [
            'type' => PostType::Reply,
            'parent_id' => $cakraT2->id,
            'body' => 'Transisi loading-nya terasa halus, mirip feed aplikasi aslinya.',
        ]);

        // Balasan 3 milik Bunga: membalas tweet kopi Darussalam Andi.
        $penulis->tulis($bunga, [
            'type' => PostType::Reply,
            'parent_id' => $andiT5->id,
            'body' => 'Jangan lupa mampir ke warung kopi sebelah rektorat, kopinya mantap.',
        ]);

        // Balasan 1 milik Cakra: membalas tweet optimasi MySQL Andi.
        $penulis->tulis($cakra, [
            'type' => PostType::Reply,
            'parent_id' => $andiT2->id,
            'body' => 'Kemarin sempat coba composite index, ternyata single column created_at lebih efisien untuk query ini.',
        ]);

        // Balasan 2 milik Cakra: membalas tweet desain X Bunga.
        $penulis->tulis($cakra, [
            'type' => PostType::Reply,
            'parent_id' => $bungaT2->id,
            'body' => 'Transisi tab Untuk Anda dan Mengikuti juga presisi dengan indikator birunya.',
        ]);

        // Balasan 3 milik Cakra: membalas tweet ikon vektor Bunga.
        $penulis->tulis($cakra, [
            'type' => PostType::Reply,
            'parent_id' => $bungaT4->id,
            'body' => 'Boleh minta export format SVG-nya untuk dipakai di modul frontend?',
        ]);

        // Kutipan 1 milik Andi: mengutip tweet antarmuka Bunga.
        $penulis->tulis($andi, [
            'type' => PostType::Quote,
            'parent_id' => $bungaT2->id,
            'body' => 'Desain yang bersih membuat pengguna betah berlama-lama membaca tweet. #uiux',
        ]);

        // Kutipan 2 milik Andi: mengutip tweet testing Cakra.
        $penulis->tulis($andi, [
            'type' => PostType::Quote,
            'parent_id' => $cakraT3->id,
            'body' => 'Green tests bring peace of mind sebelum push ke production. #tdd',
        ]);

        // Kutipan 3 milik Andi: mengutip tweet ikon Bunga.
        $penulis->tulis($andi, [
            'type' => PostType::Quote,
            'parent_id' => $bungaT4->id,
            'body' => 'Apresiasi tinggi untuk detail ikon yang dibuat manual! #desain',
        ]);

        // Kutipan 1 milik Bunga: mengutip tweet arsitektur Andi.
        $penulis->tulis($bunga, [
            'type' => PostType::Quote,
            'parent_id' => $andiT1->id,
            'body' => 'Catatan penting untuk desain arsitektur database aplikasi sosial. #arsitektur',
        ]);

        // Kutipan 2 milik Bunga: mengutip tweet Livewire Cakra.
        $penulis->tulis($bunga, [
            'type' => PostType::Quote,
            'parent_id' => $cakraT1->id,
            'body' => 'Reaktivitas Livewire dipadu micro-copy yang pas bikin UX naik kelas. #livewire',
        ]);

        // Kutipan 3 milik Bunga: mengutip tweet diskusi kampus Cakra.
        $penulis->tulis($bunga, [
            'type' => PostType::Quote,
            'parent_id' => $cakraT5->id,
            'body' => 'Senang melihat antusiasme teman-teman mahasiswa USK kemarin! #usk',
        ]);

        // Kutipan 1 milik Cakra: mengutip tweet query Andi.
        $penulis->tulis($cakra, [
            'type' => PostType::Quote,
            'parent_id' => $andiT2->id,
            'body' => 'Wajib baca untuk siapa saja yang sedang tuning query MySQL di project-nya. #database',
        ]);

        // Kutipan 2 milik Cakra: mengutip tweet tipografi Bunga.
        $penulis->tulis($cakra, [
            'type' => PostType::Quote,
            'parent_id' => $bungaT1->id,
            'body' => 'Tipografi yang baik adalah yang tidak disadari pembaca saat menyerap informasi. #desain',
        ]);

        // Kutipan 3 milik Cakra: mengutip tweet server kampus Andi.
        $penulis->tulis($cakra, [
            'type' => PostType::Quote,
            'parent_id' => $andiT4->id,
            'body' => 'Bisa dicoba untuk infrastruktur lab komputasi kampus semester depan. #devops',
        ]);

        // Repost 1 milik Andi: merepost tweet tipografi Bunga.
        $penulis->tulis($andi, [
            'type' => PostType::Repost,
            'parent_id' => $bungaT1->id,
        ]);

        // Repost 2 milik Andi: merepost tweet karakter Cakra.
        $penulis->tulis($andi, [
            'type' => PostType::Repost,
            'parent_id' => $cakraT1->id,
        ]);

        // Repost 3 milik Andi: merepost tweet OLED Bunga.
        $penulis->tulis($andi, [
            'type' => PostType::Repost,
            'parent_id' => $bungaT3->id,
        ]);

        // Repost 1 milik Bunga: merepost tweet arsitektur Andi.
        $penulis->tulis($bunga, [
            'type' => PostType::Repost,
            'parent_id' => $andiT1->id,
        ]);

        // Repost 2 milik Bunga: merepost tweet pengujian Cakra.
        $penulis->tulis($bunga, [
            'type' => PostType::Repost,
            'parent_id' => $cakraT3->id,
        ]);

        // Repost 3 milik Bunga: merepost tweet kampus Cakra.
        $penulis->tulis($bunga, [
            'type' => PostType::Repost,
            'parent_id' => $cakraT5->id,
        ]);

        // Repost 1 milik Cakra: merepost tweet interaksi Andi.
        $penulis->tulis($cakra, [
            'type' => PostType::Repost,
            'parent_id' => $andiT3->id,
        ]);

        // Repost 2 milik Cakra: merepost tweet desain Bunga.
        $penulis->tulis($cakra, [
            'type' => PostType::Repost,
            'parent_id' => $bungaT2->id,
        ]);

        // Repost 3 milik Cakra: merepost tweet kopi Andi.
        $penulis->tulis($cakra, [
            'type' => PostType::Repost,
            'parent_id' => $andiT5->id,
        ]);

        // Relasi saling mengikuti antar pengguna demo.
        $andi->following()->attach([$bunga->id, $cakra->id]);
        $bunga->following()->attach([$andi->id]);
        $cakra->following()->attach([$andi->id]);

        // Suka dibagikan ke sejumlah postingan linimasa.
        $postingan = Post::feed()->pluck('id');
        $andi->likes()->attach($postingan->take(5)->all());
        $bunga->likes()->attach($postingan->skip(2)->take(5)->all());
        $cakra->likes()->attach($postingan->skip(4)->take(5)->all());
    }
}
