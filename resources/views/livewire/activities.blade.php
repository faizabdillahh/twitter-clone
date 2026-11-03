{{-- Halaman aktivitas bergaya notifikasi X. --}}
<div>
    {{-- Kepala halaman: judul dan tab, menempel saat digulir. --}}
    <div class="sticky top-[53px] z-20 border-b border-zinc-200 bg-white/85 backdrop-blur-md sm:top-0 dark:border-[#2f3336] dark:bg-black/85">
        {{-- Baris judul setinggi 53px. --}}
        <div class="flex h-[53px] items-center px-4">
            {{-- Judul halaman aktivitas. --}}
            <h1 class="text-xl font-bold text-[#0f1419] dark:text-[#e7e9ea]">Aktivitas</h1>
        </div>

        {{-- Dua tab: semua aktivitas dan sebutan untuk akun ini. --}}
        <div class="flex">
            @foreach (['semua' => 'Semua', 'sebutan' => 'Sebutan'] as $nilai => $label)
                {{-- Tab dengan garis biru pada pilihan aktif. --}}
                <button type="button" wire:click="pilihTab('{{ $nilai }}')" class="relative flex-1 cursor-pointer py-3 text-[15px] font-bold transition-colors {{ $tab === $nilai ? 'text-[#0f1419] dark:text-[#e7e9ea]' : 'text-zinc-500 hover:bg-black/[0.03] dark:hover:bg-white/[0.03]' }}">
                    {{-- Label tab. --}}
                    {{ $label }}
                    @if ($tab === $nilai)
                        {{-- Garis bawah biru penanda tab aktif. --}}
                        <span class="absolute bottom-0 left-1/2 h-1 w-14 -translate-x-1/2 rounded-full bg-[#1d9bf0]"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Daftar kejadian terurut dari yang terbaru. --}}
    <div>
        @forelse ($aktivitas as $item)
            {{-- Baris kejadian yang bisa diklik menuju post atau profil terkait. --}}
            <a href="{{ $item['url'] }}" wire:navigate class="flex gap-3 border-b border-zinc-200 px-4 py-3 transition-colors hover:bg-black/[0.02] dark:border-[#2f3336] dark:hover:bg-white/[0.03]">
                {{-- Kolom ikon jenis aktivitas, seperti notifikasi X. --}}
                <div class="flex size-8 shrink-0 items-center justify-center pt-0.5">
                    @if (str_contains($item['teks'], 'menyukai'))
                        {{-- Ikon hati merah muda resmi X untuk interaksi suka. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current text-[#f91880]">
                            {{-- Bentuk hati penuh. --}}
                            <path d="M20.884 13.19c-1.351 2.48-4.001 5.12-8.379 8.67l-.505.41-.505-.41C7.117 18.31 4.467 15.67 3.116 13.19 1.901 10.96 1.75 8.79 2.676 6.74c.917-2.03 2.713-3.47 4.935-3.95 2.192-.48 4.542.17 6.389 1.74 1.847-1.57 4.197-2.22 6.389-1.74 2.222.48 4.018 1.92 4.935 3.95.926 2.05.775 4.22-.44 6.45z"></path>
                        </svg>
                    @elseif (str_contains($item['teks'], 'merepost') || str_contains($item['teks'], 'mengutip'))
                        {{-- Ikon panah hijau resmi X untuk repost dan kutipan. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current text-[#00ba7c]">
                            {{-- Bentuk panah ganda repost. --}}
                            <path d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 20.12l-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14z"></path>
                        </svg>
                    @elseif (str_contains($item['teks'], 'membalas') || str_contains($item['teks'], 'menyebut'))
                        {{-- Ikon balon biru resmi X untuk balasan dan sebutan. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current text-[#1d9bf0]">
                            {{-- Bentuk balon pesan. --}}
                            <path d="M1.751 10c0-4.42 3.584-8 8.005-8h4.366c4.49 0 8.129 3.64 8.129 8.13 0 2.96-1.607 5.68-4.196 7.11l-8.054 4.46v-3.69h-.067c-4.49.1-8.183-3.51-8.183-8.01zm8.005-6c-3.317 0-6.005 2.69-6.005 6 0 3.37 2.77 6.08 6.138 6.01l.351-.01h1.761v2.3l5.087-2.81c1.951-1.08 3.163-3.13 3.163-5.36 0-3.39-2.744-6.13-6.129-6.13H9.756z"></path>
                        </svg>
                    @else
                        {{-- Ikon pengguna biru resmi X untuk pengikut baru. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current text-[#1d9bf0]">
                            {{-- Bentuk orang profil. --}}
                            <path d="M12 11.816c3.297 0 5.971-2.673 5.971-5.97s-2.674-5.971-5.971-5.971c-3.297 0-5.971 2.674-5.971 5.971s2.674 5.97 5.971 5.97zm0-9.942c2.19 0 3.971 1.781 3.971 3.971 0 2.191-1.781 3.972-3.971 3.972s-3.971-1.781-3.971-3.972c0-2.19 1.781-3.971 3.971-3.971zm7.716 19.493c0-3.68-2.984-6.664-6.664-6.664H10.95c-3.68 0-6.664 2.984-6.664 6.664 0 .414.336.75.75.75h14.93c.414 0 .75-.336.75-.75zm-14.73-.75c.34-2.604 2.574-4.664 5.264-4.664h2.1c2.69 0 4.924 2.06 5.264 4.664H4.986z"></path>
                        </svg>
                    @endif
                </div>

                {{-- Isi kejadian: pelaku, perbuatan, waktu, dan cuplikan post. --}}
                <div class="min-w-0 flex-1">
                    {{-- Nama, username, kejadian, dan waktu dalam satu paragraf. --}}
                    <p class="text-[15px] leading-snug text-[#0f1419] dark:text-[#e7e9ea]">
                        {{-- Nama pelaku dicetak tebal. --}}
                        <span class="font-bold">{{ $item['nama'] }}</span>
                        @if ($item['handle'] !== null)
                            {{-- Username pelaku di samping namanya. --}}
                            <span class="text-zinc-500">&#64;{{ $item['handle'] }}</span>
                        @endif
                        {{-- Perbuatan yang terjadi. --}}
                        {{ $item['teks'] }}
                        {{-- Waktu kejadian di ujung kalimat. --}}
                        <span class="whitespace-nowrap text-zinc-500">&middot; {{ $item['waktu']->diffForHumans(short: true) }}</span>
                    </p>
                    @if ($item['cuplikan'] !== null)
                        {{-- Potongan isi post terkait, paling banyak dua baris. --}}
                        <p class="mt-1 line-clamp-2 text-[15px] leading-snug text-zinc-500">{{ $item['cuplikan'] }}</p>
                    @endif
                </div>
            </a>
        @empty
            @if ($tab === 'sebutan')
                {{-- Keadaan kosong untuk tab sebutan. --}}
                <x-empty-state judul="Belum ada sebutan" teks="Post yang menyebut akun Anda akan muncul di sini." />
            @else
                {{-- Keadaan kosong untuk tab semua. --}}
                <x-empty-state judul="Belum ada aktivitas" teks="Suka, balasan, repost, dan pengikut baru akan muncul di sini." />
            @endif
        @endforelse
    </div>
</div>
