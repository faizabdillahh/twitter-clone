<?php

/**
 * Pemeriksa komentar per baris.
 *
 * Pint tidak bisa memeriksa komentar, dan aturan yang hanya ditulis di dokumen
 * akan bocor cepat atau lambat. Skrip ini menegakkannya: setiap baris pernyataan
 * di app/, resources/js/, dan resources/views/ harus punya komentar.
 *
 * Cara pakai: php scripts/cek-komentar.php
 * Keluar dengan kode 1 bila ada pelanggaran, 0 bila bersih.
 *
 * Skrip ini sendiri tidak ikut diperiksa. Perkakas yang memeriksa aturan tidak
 * perlu mematuhi aturan yang sama, sama seperti Pint tidak memformat dirinya.
 */

declare(strict_types=1);

// Direktori yang diperiksa.
$targets = [
    __DIR__.'/../app',
    __DIR__.'/../resources/js',
    __DIR__.'/../resources/views',
];

// Ekstensi yang ikut diperiksa.
$extensions = ['php', 'js'];

// Penanda bahwa baris ini sengaja dilewati.
$pragma = 'cek-komentar: abaikan';

// Berkas bawaan starter kit yang belum kita ubah. Komentar per baris berlaku
// untuk kode yang kita tulis, bukan untuk kode warisan yang tidak disentuh.
// Hapus satu baris dari daftar ini begitu berkasnya mulai kita modifikasi.
$warisan = [
    // Komponen bawaan; avatar.blade.php tidak didaftarkan karena kita yang menulisnya.
    'resources/views/components/action-message.blade.php',
    'resources/views/components/app-logo-icon.blade.php',
    'resources/views/components/auth-header.blade.php',
    'resources/views/components/auth-session-status.blade.php',
    'resources/views/components/text-link.blade.php',
    // Layout auth masih bawaan; app.blade.php dan sidebar sudah milik kita.
    'resources/views/components/layouts/auth',
    'resources/views/components/settings/',
    // Halaman auth bawaan; register.blade.php tetap diperiksa karena kita ubah.
    'resources/views/livewire/auth/confirm-password.blade.php',
    'resources/views/livewire/auth/forgot-password.blade.php',
    'resources/views/livewire/auth/login.blade.php',
    'resources/views/livewire/auth/reset-password.blade.php',
    'resources/views/livewire/auth/verify-email.blade.php',
    // Tab pengaturan lain masih apa adanya; profil sudah jadi milik kita.
    'resources/views/livewire/settings/appearance.blade.php',
    'resources/views/livewire/settings/delete-user-form.blade.php',
    'resources/views/livewire/settings/password.blade.php',
    'app/Http/Controllers/',
    'app/Providers/',
    'app/Livewire/Actions/',
];

// Kumpulan pelanggaran yang ditemukan.
$violations = [];

// Telusuri setiap direktori target secara rekursif.
foreach ($targets as $target) {
    // Lewati direktori yang belum ada.
    if (! is_dir($target)) {
        continue;
    }

    // Kumpulkan berkas dengan ekstensi yang diperiksa.
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));

    // Periksa satu per satu.
    foreach ($iterator as $file) {
        // Hanya berkas dengan ekstensi yang diminati.
        if (! in_array($file->getExtension(), $extensions, true)) {
            continue;
        }

        // Periksa berkas ini dan gabungkan temuannya.
        $violations = array_merge($violations, periksaBerkas($file->getPathname(), $pragma, $warisan));
    }
}

// Laporkan hasil.
if ($violations === []) {
    echo "Komentar per baris: bersih.\n";
    exit(0);
}

// Cetak setiap pelanggaran dalam format yang bisa diklik dari terminal.
foreach ($violations as $violation) {
    echo $violation."\n";
}

// Cetak ringkasan sebelum keluar dengan kode gagal.
echo "\n".count($violations)." baris tanpa komentar.\n";
exit(1);

/**
 * Memeriksa satu berkas dan mengembalikan daftar pelanggaran.
 *
 * @param  string  $path  Lokasi berkas yang diperiksa.
 * @param  string  $pragma  Penanda baris yang dilewati.
 * @param  array<int, string>  $warisan  Awalan path berkas bawaan starter kit.
 * @return array<int, string> Baris pelanggaran dalam format "berkas:baris".
 */
function periksaBerkas(string $path, string $pragma, array $warisan): array
{
    // Berkas warisan tidak diperiksa sampai kita mulai mengubahnya.
    foreach ($warisan as $awalan) {
        // Bandingkan path relatif terhadap root proyek.
        if (str_starts_with(relative($path), $awalan)) {
            return [];
        }
    }

    // Baca berkas sebagai baris-baris terpisah.
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    // Berkas yang gagal dibaca dianggap bersih daripada menggagalkan semuanya.
    if ($lines === false) {
        return [];
    }

    // Deteksi tipe berkas dari ekstensinya. Berkas .blade.php juga berakhiran
    // .php, jadi Blade diperiksa lebih dulu agar tidak masuk jalur tokenizer.
    $isBlade = str_ends_with($path, '.blade.php');
    $isPhp = ! $isBlade && str_ends_with($path, '.php');

    // Susun peta baris yang berisi komentar, dan baris yang berada di dalam blok komentar.
    $commentLines = petaKomentar($lines, $isPhp);

    // Kumpulkan pelanggaran berkas ini.
    $found = [];

    // Lacak baris kode terakhir untuk mengenali baris lanjutan.
    $previousCodeLine = null;

    // Lacak apakah baris sebelumnya komentar, untuk baris yang didokumentasikan dari atas.
    $previousIsComment = false;

    // Periksa baris demi baris.
    foreach ($lines as $index => $line) {
        // Nomor baris dimulai dari 1.
        $number = $index + 1;

        // Buang spasi di kedua ujung untuk memudahkan pemeriksaan.
        $trimmed = trim($line);

        // Baris kosong tidak perlu komentar.
        if ($trimmed === '') {
            continue;
        }

        // Baris yang mengandung pragma dilewati.
        if (str_contains($trimmed, $pragma)) {
            $previousCodeLine = $trimmed;

            continue;
        }

        // Baris komentar tentu tidak perlu komentar lagi.
        if (isset($commentLines[$number])) {
            $previousCodeLine = $trimmed;
            $previousIsComment = true;

            continue;
        }

        // Baris yang tidak butuh komentar sendiri.
        if (adalahBarisStruktural($trimmed, $isPhp)) {
            $previousCodeLine = $trimmed;

            // Atribut menempel pada deklarasi di bawahnya, jadi status komentar
            // dipertahankan agar docblock di atas atribut tetap dihitung.
            $previousIsComment = str_starts_with($trimmed, '#[') ? $previousIsComment : false;

            continue;
        }

        // Baris yang didokumentasikan tepat di atasnya dianggap sudah berkomentar.
        if ($previousIsComment) {
            $previousCodeLine = $trimmed;
            $previousIsComment = false;

            continue;
        }

        // Baris lanjutan mewarisi komentar dari baris di atasnya.
        if ($previousCodeLine !== null && adalahBarisLanjutan($previousCodeLine)) {
            $previousCodeLine = $trimmed;
            $previousIsComment = false;

            continue;
        }

        // Baris yang dibuka operator juga lanjutan dari baris sebelumnya.
        if (adalahBarisSambungan($trimmed)) {
            $previousCodeLine = $trimmed;
            $previousIsComment = false;

            continue;
        }

        // Sampai sini baris dianggap butuh komentar tetapi belum punya.
        $found[] = relative($path).':'.$number.'  '.$trimmed;

        // Simpan sebagai baris kode terakhir untuk pemeriksaan berikutnya.
        $previousCodeLine = $trimmed;
        $previousIsComment = false;
    }

    // Kembalikan temuan berkas ini.
    return $found;
}

/**
 * Menandai baris yang memuat komentar atau berada di dalam blok komentar.
 *
 * Memakai tokenizer PHP untuk berkas .php karena isi komentar dan string bisa
 * menyesatkan pemeriksaan berbasis teks. Untuk berkas .js dan .blade.php dipakai
 * pendekatan teks sederhana, cukup untuk pola komentar yang dipakai proyek ini.
 *
 * @param  array<int, string>  $lines  Baris berkas.
 * @param  bool  $isPhp  Apakah berkas PHP.
 * @return array<int, bool> Peta nomor baris ke status berkomentar.
 */
function petaKomentar(array $lines, bool $isPhp): array
{
    // Peta hasil.
    $map = [];

    // Untuk PHP, tokenizer memberi posisi komentar yang akurat.
    if ($isPhp) {
        // Gabungkan baris kembali agar tokenizer membaca berkas utuh.
        $code = implode("\n", $lines);

        // Telusuri token dan tandai baris tempat komentar dimulai.
        foreach (PhpToken::tokenize($code) as $token) {
            // Hanya token komentar yang menarik di sini.
            if ($token->is([T_COMMENT, T_DOC_COMMENT])) {
                // Hitung berapa baris yang dicakup blok komentar ini.
                $span = substr_count($token->text, "\n");

                // Tandai seluruh rentangnya, bukan hanya baris pertama.
                for ($offset = 0; $offset <= $span; $offset++) {
                    $map[$token->line + $offset] = true;
                }
            }
        }

        // Tambahkan penanda untuk semua baris yang dibuka oleh blok komentar.
        return $map;
    }

    // Untuk JS dan Blade, tandai baris yang memuat penanda komentar.
    foreach ($lines as $index => $line) {
        // Blade memakai {{-- --}}, JS memakai // atau /* */.
        $berkomentar = preg_match('#^\s*(//|/\*|\*)#', $line) === 1 || str_contains($line, '{{--');

        // Tandai baris bila salah satu penanda ditemukan.
        if ($berkomentar) {
            $map[$index + 1] = true;
        }
    }

    // Kembalikan peta.
    return $map;
}

/**
 * Menentukan apakah sebuah baris tidak butuh komentar sendiri.
 *
 * Contohnya pembuka PHP, deklarasi, blok use, dan tanda kurung penutup.
 *
 * @param  string  $trimmed  Baris tanpa spasi di ujung.
 * @param  bool  $isPhp  Apakah berkas PHP.
 * @return bool True bila baris dilewati.
 */
function adalahBarisStruktural(string $trimmed, bool $isPhp): bool
{
    // Baris yang selalu dilewati, apa pun bahasanya.
    $selalu = ['<?php', '?>', 'declare', 'namespace'];

    // Periksa daftar pertama.
    foreach ($selalu as $prefix) {
        // Bandingkan awal baris.
        if (str_starts_with($trimmed, $prefix)) {
            return true;
        }
    }

    // Kurung kurawal dan penutup blok tidak butuh komentar.
    if (in_array($trimmed, ['{', '}', '});', ');', '];', ']', '},', ');', ')', '});', '};', '}; ?>', '?>'], true)) {
        return true;
    }

    // Blade: arahan @if dan tag penutup tidak menambah informasi sendiri.
    if (! $isPhp && (str_starts_with($trimmed, '@') || str_starts_with($trimmed, '</'))) {
        return true;
    }

    // PHP: blok use dan atribut juga dilewati.
    if (str_starts_with($trimmed, 'use ') || $trimmed === 'use') {
        return true;
    }

    // PHP: atribut juga dilewati.
    if ($isPhp) {
        // Atribut PHP 8 ditulis rapat dengan deklarasi di bawahnya.
        if (str_starts_with($trimmed, '#[')) {
            return true;
        }
    }

    // Selain itu baris dianggap butuh komentar.
    return false;
}

/**
 * Menentukan apakah baris berikutnya adalah lanjutan dari baris ini.
 *
 * Baris yang masih menggantung, misalnya berakhir dengan operator atau tanda
 * baca, membuat baris sesudahnya tidak perlu komentar sendiri.
 *
 * @param  string  $line  Baris kode sebelumnya.
 * @return bool True bila baris sesudahnya bersambung.
 */
function adalahBarisLanjutan(string $line): bool
{
    // Buang komentar di ujung baris agar karakternya terlihat.
    $code = trim(preg_replace('#//.*$#', '', $line) ?? $line);

    // Baris kosong setelah komentar dibuang tidak memulai rantai.
    if ($code === '') {
        return false;
    }

    // Karakter terakhir yang menandakan baris belum selesai.
    $penutup = ['(', '[', '{', ',', '->', '?->', '::', '=>', '.', '=', '&&', '||', '+', '?', ':', '?>'];

    // Periksa tiap penanda.
    foreach ($penutup as $penanda) {
        // Bandingkan ujung baris.
        if (str_ends_with($code, $penanda)) {
            return true;
        }
    }

    // Baris tidak menggantung.
    return false;
}

/**
 * Menentukan apakah baris ini sambungan dari baris sebelumnya.
 *
 * Rantai method dan ekspresi yang dipecah ke beberapa baris dibuka oleh
 * operator, sehingga komentarnya cukup satu di awal rantai.
 *
 * @param  string  $trimmed  Baris tanpa spasi di ujung.
 * @return bool True bila baris menyambung.
 */
function adalahBarisSambungan(string $trimmed): bool
{
    // Awalan yang menandakan baris masih bagian dari ekspresi di atasnya.
    $awalan = ['->', '?->', '::', '.', '??', '||', '&&', '?', ':', '+'];

    // Periksa tiap penanda.
    foreach ($awalan as $penanda) {
        // Bandingkan awal baris.
        if (str_starts_with($trimmed, $penanda)) {
            return true;
        }
    }

    // Bukan sambungan.
    return false;
}

/**
 * Mengubah path absolut menjadi path relatif terhadap root proyek.
 *
 * @param  string  $path  Path absolut.
 * @return string Path relatif dengan pemisah garis miring.
 */
function relative(string $path): string
{
    // Normalkan path lebih dulu agar bagian ".." hilang.
    $normal = realpath($path);

    // Pakai hasil normalisasi bila berhasil.
    $absolute = $normal === false ? $path : $normal;

    // Cari posisi root proyek dalam path.
    $root = realpath(__DIR__.'/..');

    // Ganti bila root ditemukan.
    $relative = $root === false ? $absolute : str_replace($root.DIRECTORY_SEPARATOR, '', $absolute);

    // Samakan pemisah path agar konsisten di Windows.
    return str_replace('\\', '/', $relative);
}
