{{-- Kartu postingan persis web X: identitas penulis, tipografi 15px, dan empat tombol aksi berikon resmi. --}}
@props(['post', 'status' => ['sudahRepost' => [], 'sudahLike' => [], 'jumlahLike' => [], 'jumlahBalas' => [], 'jumlahRepost' => []], 'tampilkanInduk' => false])

@php
    // Post bertipe repost menampilkan konten asli yang dirujuk.
    $asli = $post->type === \App\Enums\PostType::Repost ? $post->parent : $post;
@endphp

@if ($asli === null || $asli->trashed())
    {{-- Tampilan saat induk repost sudah dihapus, sementara maupun permanen. --}}
    <article class="border-b border-zinc-200 p-4 text-sm text-zinc-500 dark:border-[#2f3336]">Post ini sudah tidak tersedia.</article>
@else
    @php
        // Gambar tampil sebagai galeri, berkas lain tampil sebagai kartu unduhan.
        $gambar = $asli->media->filter(fn ($satu) => $satu->isImage());
        // Lampiran selain gambar ditampilkan terpisah di bawah galeri.
        $lampiran = $asli->media->reject(fn ($satu) => $satu->isImage());
        // Jumlah gambar menentukan bentuk galeri.
        $jumlahGambar = $gambar->count();
        // Hitungan balasan diambil dari status halaman.
        $jumlahBalas = $status['jumlahBalas'][$asli->id] ?? 0;
        // Hitungan repost diambil dari status halaman.
        $jumlahRepost = $status['jumlahRepost'][$asli->id] ?? 0;
        // Hitungan suka diambil dari status halaman.
        $jumlahSuka = $status['jumlahLike'][$asli->id] ?? 0;
        // Penanda repost milik pengguna yang sedang melihat.
        $sudahRepost = in_array($asli->id, $status['sudahRepost'], true);
        // Penanda suka milik pengguna yang sedang melihat.
        $sudahSuka = in_array($asli->id, $status['sudahLike'], true);
        // Hanya pemilik post yang melihat menu sunting dan hapus.
        $pemilik = auth()->id() === $asli->user_id;
    @endphp

    {{-- Kontainer kartu post dengan hover efek halus khas X. --}}
    <article {{ $attributes->merge(['class' => 'border-b border-zinc-200 px-4 py-3 transition-colors hover:bg-black/[0.02] dark:border-[#2f3336] dark:hover:bg-white/[0.03]']) }}>
        @if ($post->type === \App\Enums\PostType::Repost)
            {{-- Header penanda repost dengan ikon hijau mini. --}}
            <div class="mb-1 flex items-center gap-3 pl-9 text-[13px] font-bold text-zinc-500">
                {{-- Ikon panah repost mini. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4 fill-current text-[#00ba7c]">
                    {{-- Jalur ikon repost. --}}
                    <path d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 20.12l-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14z"></path>
                </svg>
                {{-- Nama perepost. --}}
                <span class="hover:underline">Direpost oleh {{ $post->user->name }}</span>
            </div>
        @endif

        {{-- Tata letak kolom: avatar di kiri, konten utama di kanan. --}}
        <div class="flex gap-3">
            {{-- Avatar profil penulis postingan. --}}
            <a href="{{ route('profile.show', $asli->user) }}" class="shrink-0" wire:navigate>
                {{-- Komponen avatar bundar ukuran 40px. --}}
                <x-avatar :user="$asli->user" size="md" />
            </a>

            {{-- Kolom isi konten dan deretan aksi. --}}
            <div class="min-w-0 flex-1">
                {{-- Baris tajuk: identitas penulis di kiri, menu opsi di kanan. --}}
                <div class="flex items-start justify-between gap-2">
                    {{-- Informasi penulis dan waktu. --}}
                    <div class="flex min-w-0 flex-wrap items-baseline gap-x-1 text-[15px]">
                        {{-- Nama tampilan penulis tebal. --}}
                        <a href="{{ route('profile.show', $asli->user) }}" wire:navigate class="font-bold text-[#0f1419] hover:underline dark:text-[#e7e9ea]">{{ $asli->user->name }}</a>
                        {{-- Handle username dengan simbol at. --}}
                        <span class="text-zinc-500">&#64;{{ $asli->user->username }}</span>
                        {{-- Titik pemisah. --}}
                        <span class="text-zinc-500">·</span>
                        {{-- Waktu tayang sebagai tautan ke halaman rincian post. --}}
                        <a href="{{ route('posts.show', $asli) }}" wire:navigate class="text-zinc-500 hover:underline">{{ $asli->created_at->diffForHumans() }}</a>
                        @if ($asli->sudahDiedit())
                            {{-- Penanda bahwa teks pernah diubah. --}}
                            <span class="text-[13px] text-zinc-500">· Diedit</span>
                        @endif
                    </div>

                    @if ($pemilik)
                        {{-- Menu tiga titik milik pemilik post. --}}
                        <flux:dropdown align="end" class="-mt-1 -mr-2 shrink-0">
                            {{-- Pemicu menu opsi post. --}}
                            <button type="button" class="flex size-8 items-center justify-center rounded-full text-zinc-500 transition-colors hover:bg-[#1d9bf0]/10 hover:text-[#1d9bf0]" aria-label="Opsi post">
                                {{-- Ikon tiga titik horizontal. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18px] fill-current">
                                    {{-- Tiga titik opsi. --}}
                                    <path d="M3 12c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm9 2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm7 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"></path>
                                </svg>
                            </button>
                            {{-- Daftar opsi untuk post sendiri. --}}
                            <flux:menu>
                                @if ($asli->type !== \App\Enums\PostType::Repost)
                                    {{-- Repost tidak punya teks sendiri, jadi tidak bisa disunting. --}}
                                    <flux:menu.item :href="route('posts.edit', $asli)" wire:navigate icon="pencil">Sunting post</flux:menu.item>
                                @endif
                                {{-- Hapus post setelah konfirmasi. --}}
                                <flux:menu.item wire:click="hapus({{ $asli->id }})" wire:confirm="Hapus post ini?" icon="trash" variant="danger">Hapus post</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                </div>

                @if ($tampilkanInduk && $asli->parent)
                    {{-- Konteks balasan saat induknya tidak tampil, sama seperti tab balasan X. --}}
                    <p class="text-[15px] text-zinc-500">Membalas <a href="{{ route('profile.show', $asli->parent->user) }}" wire:navigate class="text-[#1d9bf0] hover:underline">&#64;{{ $asli->parent->user->username }}</a></p>
                @endif

                @if ($asli->body)
                    {{-- Teks isi postingan dengan ukuran 15px standar X. --}}
                    <div class="mt-0.5 text-[15px] leading-normal text-[#0f1419] dark:text-[#e7e9ea]">
                        {{-- Badan post dengan tagar dan sebutan aktif. --}}
                        <x-post-body :body="$asli->body" />
                    </div>
                @endif

                @if ($jumlahGambar > 0)
                    {{-- Galeri gambar mengikuti pola X: satu penuh, dua sejajar, tiga dengan gambar utama memanjang. --}}
                    <div class="mt-3 grid gap-0.5 overflow-hidden rounded-2xl border border-zinc-200 dark:border-[#2f3336] {{ $jumlahGambar > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                        @foreach ($gambar as $satu)
                            {{-- Gambar lampiran; gambar pertama melebar saat jumlahnya tiga. --}}
                            <img src="{{ $satu->url() }}" alt="Media dari {{ $asli->user->name }}" class="{{ $jumlahGambar === 1 ? 'max-h-[510px] w-full object-cover' : ($jumlahGambar === 3 && $loop->first ? 'row-span-2 h-full w-full object-cover' : 'aspect-square w-full object-cover') }}" />
                        @endforeach
                    </div>
                @endif

                @foreach ($lampiran as $satu)
                    {{-- Kartu berkas lampiran: ikon, nama asli, dan tautan unduh. --}}
                    <a href="{{ $satu->url() }}" download class="mt-3 flex items-center gap-3 rounded-2xl border border-zinc-200 p-3 transition-colors hover:bg-black/[0.02] dark:border-[#2f3336] dark:hover:bg-white/[0.02]">
                        {{-- Ikon dokumen untuk lampiran berkas. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 shrink-0 fill-current text-[#1d9bf0]">
                            {{-- Jalur ikon dokumen. --}}
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"></path>
                        </svg>
                        {{-- Nama berkas asli yang diunggah. --}}
                        <span class="min-w-0 flex-1 truncate text-[15px] font-semibold text-[#1d9bf0]">{{ $satu->label() }}</span>
                        {{-- Ikon panah unduh. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 shrink-0 fill-current text-zinc-500">
                            {{-- Jalur ikon unduh. --}}
                            <path d="M12 3v10.59l3.3-3.3 1.4 1.42-5.7 5.7-5.7-5.7 1.4-1.42 3.3 3.3V3h2zM4 19h16v2H4v-2z"></path>
                        </svg>
                    </a>
                @endforeach

                @if ($asli->type === \App\Enums\PostType::Quote && $asli->parent && ! $asli->parent->trashed())
                    {{-- Kotak kutipan tweet berbingkai sudut melengkung. --}}
                    <div class="mt-3 rounded-2xl border border-zinc-200 p-3 transition-colors hover:bg-black/[0.02] dark:border-[#2f3336] dark:hover:bg-white/[0.02]">
                        {{-- Tajuk pemilik tweet yang dikutip. --}}
                        <div class="flex items-center gap-2 text-sm">
                            {{-- Avatar mini yang dikutip. --}}
                            <x-avatar :user="$asli->parent->user" size="sm" />
                            {{-- Nama tebal penulis asli kutipan. --}}
                            <span class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $asli->parent->user->name }}</span>
                            {{-- Handle username yang dikutip. --}}
                            <span class="text-zinc-500">&#64;{{ $asli->parent->user->username }}</span>
                        </div>
                        {{-- Isi teks yang dikutip. --}}
                        <div class="mt-1 text-sm text-[#0f1419] dark:text-[#e7e9ea]">
                            {{-- Komponen teks kutipan. --}}
                            <x-post-body :body="$asli->parent->body" class="line-clamp-4" />
                        </div>
                    </div>
                @endif

                {{-- Deretan empat tombol aksi berikon tanpa label teks, persis bilah aksi X. --}}
                <div class="mt-3 flex max-w-[425px] items-center justify-between text-[13px] text-zinc-500">
                    {{-- Balas: membuka halaman post untuk menulis balasan. --}}
                    <a href="{{ route('posts.show', $asli) }}" wire:navigate class="group flex items-center transition-colors hover:text-[#1d9bf0]" title="Balas" aria-label="Balas">
                        {{-- Lingkaran latar belakang biru saat disorot. --}}
                        <div class="flex size-[34px] items-center justify-center rounded-full transition-colors group-hover:bg-[#1d9bf0]/10">
                            {{-- Ikon balon pesan resmi X. --}}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18.75px] fill-current">
                                {{-- Jalur balon percakapan. --}}
                                <path d="M1.751 10c0-4.42 3.584-8 8.005-8h4.366c4.49 0 8.129 3.64 8.129 8.13 0 2.96-1.607 5.68-4.196 7.11l-8.054 4.46v-3.69h-.067c-4.49.1-8.183-3.51-8.183-8.01zm8.005-6c-3.317 0-6.005 2.69-6.005 6 0 3.37 2.77 6.08 6.138 6.01l.351-.01h1.761v2.3l5.087-2.81c1.951-1.08 3.163-3.13 3.163-5.36 0-3.39-2.744-6.13-6.129-6.13H9.756z"></path>
                            </svg>
                        </div>
                        @if ($jumlahBalas > 0)
                            {{-- Angka balasan, disembunyikan bila nol seperti X. --}}
                            <span class="min-w-[1.75rem] ps-1 text-left group-hover:underline">{{ $jumlahBalas }}</span>
                        @endif
                    </a>

                    {{-- Repost: menu pilihan repost atau kutip, sama seperti X. --}}
                    <flux:dropdown>
                        {{-- Pemicu menu repost; ikon berwarna hijau bila sudah direpost. --}}
                        <button type="button" class="group flex items-center transition-colors {{ $sudahRepost ? 'text-[#00ba7c]' : 'hover:text-[#00ba7c]' }}" title="{{ $sudahRepost ? 'Batalkan repost' : 'Repost' }}" aria-label="Repost">
                            {{-- Lingkaran latar belakang hijau saat disorot. --}}
                            <div class="flex size-[34px] items-center justify-center rounded-full transition-colors group-hover:bg-[#00ba7c]/10">
                                {{-- Ikon panah ganda repost resmi X. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18.75px] fill-current">
                                    {{-- Jalur panah ganda. --}}
                                    <path d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 20.12l-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14z"></path>
                                </svg>
                            </div>
                            @if ($jumlahRepost > 0)
                                {{-- Angka repost. --}}
                                <span class="min-w-[1.75rem] ps-1 text-left group-hover:underline">{{ $jumlahRepost }}</span>
                            @endif
                        </button>
                        {{-- Dua pilihan repost yang dikenal X. --}}
                        <flux:menu>
                            {{-- Repost atau batalkan repost milik sendiri. --}}
                            <flux:menu.item wire:click="repost({{ $asli->id }})" icon="arrow-path" class="font-bold">{{ $sudahRepost ? 'Batalkan repost' : 'Repost' }}</flux:menu.item>
                            {{-- Kutip membuka kotak tulis dengan post ini sebagai lampiran. --}}
                            <flux:menu.item :href="route('posts.show', ['post' => $asli, 'kutip' => 1])" wire:navigate icon="pencil-square" class="font-bold">Kutip post</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Suka, ikon terisi penuh dan berwarna merah muda bila aktif. --}}
                    <button type="button" wire:click="like({{ $asli->id }})" class="group flex items-center transition-colors {{ $sudahSuka ? 'text-[#f91880]' : 'hover:text-[#f91880]' }}" title="{{ $sudahSuka ? 'Batalkan suka' : 'Suka' }}" aria-label="{{ $sudahSuka ? 'Batalkan suka' : 'Suka' }}">
                        {{-- Lingkaran latar belakang merah muda saat disorot. --}}
                        <div class="flex size-[34px] items-center justify-center rounded-full transition-colors group-hover:bg-[#f91880]/10">
                            {{-- Ikon hati: terisi warna jika disukai, bergaris jika belum. --}}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18.75px] fill-current">
                                @if ($sudahSuka)
                                    {{-- Jalur bentuk hati terisi penuh. --}}
                                    <path d="M20.884 13.19c-1.351 2.48-4.001 5.12-8.379 8.67l-.505.41-.505-.41C7.117 18.31 4.467 15.67 3.116 13.19 1.901 10.96 1.75 8.79 2.676 6.74c.917-2.03 2.713-3.47 4.935-3.95 2.192-.48 4.542.17 6.389 1.74 1.847-1.57 4.197-2.22 6.389-1.74 2.222.48 4.018 1.92 4.935 3.95.926 2.05.775 4.22-.44 6.45z"></path>
                                @else
                                    {{-- Jalur bentuk hati bergaris tepi luar. --}}
                                    <path d="M16.697 5.5c-1.222-.06-2.679.51-3.89 2.16l-.805 1.09-.806-1.09C9.984 6.01 8.526 5.44 7.304 5.5c-2.415.11-4.3 2.1-4.3 4.6 0 2.64 1.96 5.14 5.378 8.4 2.043 1.95 3.69 3.2 4.618 3.89.928-.69 2.575-1.94 4.618-3.89 3.418-3.26 5.378-5.76 5.378-8.4 0-2.5-1.885-4.49-4.3-4.6zm-4.697 15.65c-.754-.57-2.316-1.76-4.223-3.58C4.516 14.45 2.754 12.19 2.754 10.1c0-3.6 2.716-6.49 6.206-6.6 1.777-.06 3.655.8 4.793 2.59 1.138-1.79 3.016-2.65 4.793-2.59 3.49.11 6.206 3 6.206 6.6 0 2.09-1.762 4.35-5.02 7.47-1.907 1.82-3.469 3.01-4.223 3.58z"></path>
                                @endif
                            </svg>
                        </div>
                        @if ($jumlahSuka > 0)
                            {{-- Angka suka. --}}
                            <span class="min-w-[1.75rem] ps-1 text-left group-hover:underline">{{ $jumlahSuka }}</span>
                        @endif
                    </button>

                    {{-- Bagikan: menu salin tautan dan kutip, seperti tombol bagikan X. --}}
                    <flux:dropdown align="end">
                        {{-- Pemicu menu bagikan. --}}
                        <button type="button" class="group flex items-center transition-colors hover:text-[#1d9bf0]" title="Bagikan" aria-label="Bagikan">
                            {{-- Lingkaran latar belakang biru saat disorot. --}}
                            <div class="flex size-[34px] items-center justify-center rounded-full transition-colors group-hover:bg-[#1d9bf0]/10">
                                {{-- Ikon bagikan resmi X. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18.75px] fill-current">
                                    {{-- Jalur panah keluar baki. --}}
                                    <path d="M12 2.59l5.7 5.7-1.41 1.42L13 6.41V16h-2V6.41L7.71 9.71 6.3 8.29 12 2.59zM21 15l-.02 3.51c0 1.38-1.12 2.49-2.5 2.49H5.5C4.11 21.01 3 19.9 3 18.51V15h2v3.5c0 .28.22.5.5.5h12.98c.28 0 .5-.22.5-.5L19 15h2z"></path>
                                </svg>
                            </div>
                        </button>
                        {{-- Dua pilihan berbagi yang dikenal X. --}}
                        <flux:menu>
                            {{-- Menyalin tautan post ke papan klip dengan pemberitahuan singkat. --}}
                            <flux:menu.item x-on:click="navigator.clipboard?.writeText('{{ route('posts.show', $asli) }}'); $flux.toast('Tautan post disalin')" icon="link">Salin tautan</flux:menu.item>
                            {{-- Kutip post lewat kotak tulis. --}}
                            <flux:menu.item :href="route('posts.show', ['post' => $asli, 'kutip' => 1])" wire:navigate icon="pencil-square">Kutip post</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </div>
    </article>
@endif
