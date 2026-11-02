{{-- Halaman pencarian postingan dan penelusuran tagar persis web X. --}}
<div>
    {{-- Header sticky pencarian di bagian atas dengan tinggi baku 53px. --}}
    <div class="sticky top-[53px] z-20 flex h-[53px] items-center border-b border-zinc-200 bg-white/85 px-4 backdrop-blur-md sm:top-0 dark:border-[#2f3336] dark:bg-black/85">
        @if ($tag)
            {{-- Header penelusuran tagar dengan tombol kembali. --}}
            <div class="flex items-center gap-6">
                {{-- Tombol kembali ke halaman sebelumnya. --}}
                <a href="{{ route('home') }}" class="flex size-9 items-center justify-center rounded-full transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.1]" wire:navigate aria-label="Kembali ke beranda">
                    {{-- Ikon panah kiri resmi X. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                        {{-- Jalur panah kembali. --}}
                        <path d="M7.414 13l5.043 5.04-1.414 1.42L3.586 12l7.457-7.46 1.414 1.42L7.414 11H21v2H7.414z"></path>
                    </svg>
                </a>
                {{-- Judul tagar yang sedang dibuka. --}}
                <h1 class="text-xl font-bold text-[#0f1419] dark:text-[#e7e9ea]">Tag #{{ $tag }}</h1>
            </div>
        @else
            {{-- Kotak input pencarian berbentuk kapsul khas X. --}}
            <div class="relative w-full">
                {{-- Ikon kaca pembesar resmi X di dalam kotak teks. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="absolute top-2.5 left-3.5 size-4.5 fill-current text-zinc-500">
                    {{-- Jalur lensa pencarian. --}}
                    <path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path>
                </svg>
                {{-- Kolom input teks pencarian. --}}
                <input wire:model.live.debounce.400ms="q" type="search" aria-label="Cari post" placeholder="Cari di X" class="h-[40px] w-full rounded-full border border-transparent bg-zinc-100 pr-4 pl-11 text-[15px] text-[#0f1419] outline-none transition placeholder:text-zinc-500 focus:border-[#1d9bf0] focus:bg-white focus:ring-1 focus:ring-[#1d9bf0] dark:bg-[#16181c] dark:text-[#e7e9ea] dark:focus:bg-black" />
            </div>
        @endif
    </div>

    {{-- Daftar kartu postingan hasil pencarian. --}}
    <div>
        @forelse ($posts as $post)
            {{-- Kartu postingan. --}}
            <x-post-card :post="$post" :status="$status" />
        @empty
            {{-- Keadaan kosong saat kata kunci belum diisi atau tidak ada yang cocok. --}}
            <x-empty-state :judul="$kata === '' && $tag === null ? 'Mulai mencari' : 'Tidak ada hasil'" :teks="$kata === '' && $tag === null ? 'Ketik kata kunci, tagar, atau nama akun di kotak pencarian.' : 'Coba kata kunci lain atau periksa ejaan Anda.'" />
        @endforelse
    </div>

    {{-- Navigasi paginasi hasil pencarian. --}}
    <div class="p-4">
        {{-- Tautan halaman hasil pencarian. --}}
        {{ $posts->links() }}
    </div>
</div>
