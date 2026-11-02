<?php

namespace App\Enums;

/**
 * Jenis post yang disimpan di kolom `posts.type`.
 *
 * Satu tabel dipakai untuk keempat jenis karena aturan hapus, like, dan
 * tampilan feed-nya sama. Perbedaan tiap jenis dijaga validasi dan PostWriter.
 */
enum PostType: string
{
    // Post biasa yang muncul di feed.
    case Tweet = 'tweet';

    // Balasan untuk post lain.
    case Reply = 'reply';

    // Repost tanpa teks, hanya menunjuk post asli.
    case Repost = 'repost';

    // Kutipan: teks baru dengan post lain sebagai lampiran.
    case Quote = 'quote';
}
