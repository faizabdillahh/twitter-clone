<?php

namespace App\Livewire\Concerns;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Validation\ValidationException;

/**
 * Aturan isi post yang dipakai bersama oleh Composer dan EditPost.
 *
 * Batas panjang sengaja tidak memakai aturan `max` bawaan karena aturan itu
 * menghitung karakter, bukan grapheme. Satu emoji keluarga terdiri dari
 * beberapa karakter tetapi tetap satu yang dilihat pengguna.
 */
trait ValidatesPostInput
{
    public string $body = ''; // Isi post yang sedang ditulis.

    public string $tipe = 'tweet'; // Jenis post: tweet, reply, quote, atau repost.

    public ?Post $parent = null; // Post yang dibalas, dikutip, atau direpost.

    /**
     * Aturan validasi dasar untuk body.
     *
     * @return array<string, array<int, string>>
     */
    protected function aturanBody(): array
    {
        return [
            // Kosong diperbolehkan karena repost tidak punya teks sendiri.
            'body' => ['nullable', 'string'],
        ];
    }

    /**
     * Jenis post dalam bentuk enum, dengan tweet sebagai cadangan.
     */
    protected function jenis(): PostType
    {
        // Nilai tak dikenal dari klien tidak boleh lolos apa adanya.
        return PostType::tryFrom($this->tipe) ?? PostType::Tweet;
    }

    /**
     * Menolak kombinasi jenis dan induk yang tidak masuk akal.
     *
     * Skema sengaja longgar supaya satu tabel bisa menampung empat jenis post,
     * karena itu kombinasi yang salah harus ditolak di sini.
     *
     * @throws ValidationException Bila kombinasi tidak sah.
     */
    protected function pastikanKombinasi(): void
    {
        // Cari aturan pertama yang dilanggar.
        $salah = match (true) {
            // Tweet berdiri sendiri, tidak menempel pada post lain.
            $this->jenis() === PostType::Tweet && $this->parent !== null => 'Tweet tidak boleh menempel pada post lain.',
            // Balasan, kutipan, dan repost wajib punya post induk.
            $this->jenis() !== PostType::Tweet && $this->parent === null => 'Post ini butuh post induk.',
            // Kutipan tanpa teks sama saja dengan repost.
            $this->jenis() === PostType::Quote && trim($this->body) === '' => 'Kutipan butuh teks.',
            default => null,
        };

        // Hentikan proses bila ada pelanggaran.
        if ($salah !== null) {
            throw ValidationException::withMessages(['body' => $salah]);
        }
    }

    /**
     * Menolak body yang melewati 250 grapheme.
     *
     * @throws ValidationException Bila isi terlalu panjang.
     */
    protected function pastikanPanjangBody(): void
    {
        // Hitung per grapheme agar emoji tidak dihitung berlipat.
        if (grapheme_strlen($this->body) > 250) {
            throw ValidationException::withMessages(['body' => 'Maksimal 250 karakter.']);
        }
    }
}
