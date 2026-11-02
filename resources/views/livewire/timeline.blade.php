{{-- Linimasa beranda persis web X: tab atas Untuk Anda/Mengikuti, kotak tulis, dan feed. --}}
<div>
    {{-- Bilah tab sticky di bagian atas dengan tinggi baku 53px dan efek kaca buram khas X. --}}
    <div class="sticky top-[53px] z-20 flex h-[53px] border-b border-zinc-200 bg-white/85 backdrop-blur-md select-none sm:top-0 dark:border-[#2f3336] dark:bg-black/85">
        {{-- Tab Untuk Anda. --}}
        <button type="button" wire:click="$set('lingkup', 'semua')" aria-pressed="{{ $lingkup === 'semua' ? 'true' : 'false' }}" class="flex flex-1 items-center justify-center transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.05]">
            {{-- Pembungkus teks dengan garis indikator biru selebar teks. --}}
            <div class="relative flex h-full items-center">
                {{-- Label teks tab Untuk Anda. --}}
                <span class="text-[15px] {{ $lingkup === 'semua' ? 'font-bold text-[#0f1419] dark:text-[#e7e9ea]' : 'font-medium text-zinc-500' }}">Untuk Anda</span>
                @if ($lingkup === 'semua')
                    {{-- Garis indikator biru khas X menempel persis di bawah teks. --}}
                    <span class="absolute bottom-0 left-0 right-0 h-1 rounded-full bg-[#1d9bf0]"></span>
                @endif
            </div>
        </button>

        {{-- Tab Mengikuti. --}}
        <button type="button" wire:click="$set('lingkup', 'ikuti')" aria-pressed="{{ $lingkup === 'ikuti' ? 'true' : 'false' }}" class="flex flex-1 items-center justify-center transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.05]">
            {{-- Pembungkus teks dengan garis indikator biru selebar teks. --}}
            <div class="relative flex h-full items-center">
                {{-- Label teks tab Mengikuti. --}}
                <span class="text-[15px] {{ $lingkup === 'ikuti' ? 'font-bold text-[#0f1419] dark:text-[#e7e9ea]' : 'font-medium text-zinc-500' }}">Mengikuti</span>
                @if ($lingkup === 'ikuti')
                    {{-- Garis indikator biru menempel di bawah teks. --}}
                    <span class="absolute bottom-0 left-0 right-0 h-1 rounded-full bg-[#1d9bf0]"></span>
                @endif
            </div>
        </button>
    </div>

    {{-- Kotak tulis postingan untuk pengguna terotentikasi. --}}
    @auth
        {{-- Komponen kotak tulis. --}}
        <livewire:composer />
    @endauth

    {{-- Daftar kartu postingan linimasa. --}}
    <div>
        @forelse ($posts as $post)
            {{-- Komponen kartu postingan. --}}
            <x-post-card :post="$post" :status="$status" />
        @empty
            @if ($lingkup === 'ikuti')
                {{-- Keadaan kosong untuk tab Mengikuti. --}}
                <x-empty-state judul="Belum ada post di sini" teks="Post dari akun yang Anda ikuti akan muncul di tab ini." />
            @else
                {{-- Keadaan kosong untuk tab Untuk Anda. --}}
                <x-empty-state judul="Belum ada post" teks="Post dari akun mana pun akan muncul di sini. Jadilah yang pertama menulis." />
            @endif
        @endforelse
    </div>

    {{-- Penanda muat lagi otomatis saat pengguna menggulir ke bawah. --}}
    @if ($posts->count() === $batas)
        {{-- Direktif Alpine untuk pemanggilan muat lanjutan. --}}
        <div x-intersect="$wire.muatLagi()" class="h-8"></div>
    @endif
</div>
