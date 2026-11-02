{{-- Halaman rincian postingan dan daftar balasan bergaya utas resmi X. --}}
<div>
    {{-- Header sticky di bagian atas dengan tinggi 53px, tombol kembali panah resmi, dan judul. --}}
    <div class="sticky top-[53px] z-20 flex h-[53px] items-center gap-6 border-b border-zinc-200 bg-white/85 px-4 backdrop-blur-md sm:top-0 dark:border-[#2f3336] dark:bg-black/85">
        {{-- Tombol panah kembali ke halaman sebelumnya. --}}
        <a href="{{ route('home') }}" class="flex size-9 items-center justify-center rounded-full transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.1]" wire:navigate aria-label="Kembali ke beranda">
            {{-- Ikon panah kiri resmi X. --}}
            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                {{-- Jalur panah kembali. --}}
                <path d="M7.414 13l5.043 5.04-1.414 1.42L3.586 12l7.457-7.46 1.414 1.42L7.414 11H21v2H7.414z"></path>
            </svg>
        </a>
        {{-- Judul halaman tampilan utas. --}}
        <h1 class="text-xl font-bold text-[#0f1419] dark:text-[#e7e9ea]">Postingan</h1>
    </div>

    @if ($post->parent?->trashed())
        {{-- Penanda bahwa postingan induk dari balasan ini telah dihapus. --}}
        <div class="border-b border-zinc-200 p-4 text-sm text-zinc-500 dark:border-[#2f3336]">
            {{-- Teks pemberitahuan penghapusan post induk. --}}
            <p>Post induk sudah dihapus.</p>
        </div>
    @endif

    {{-- Rantai induk di atas post, dari yang paling tua ke induk langsung. --}}
    @foreach ($induk as $leluhur)
        {{-- Post induk dalam rantai yang sama. --}}
        <x-post-card :post="$leluhur" :status="$status" />
    @endforeach

    {{-- Postingan utama yang sedang dibuka secara detail. --}}
    <x-post-card :post="$post" :status="$status" />

    {{-- Baris hitungan interaksi khas halaman rincian X. --}}
    <div class="flex flex-wrap gap-5 border-b border-zinc-200 px-4 py-3 text-[15px] dark:border-[#2f3336]">
        {{-- Jumlah balasan pada post ini. --}}
        <span><b class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $status['jumlahBalas'][$post->id] ?? 0 }}</b> <span class="text-zinc-500">Balasan</span></span>
        {{-- Jumlah repost pada post ini. --}}
        <span><b class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $status['jumlahRepost'][$post->id] ?? 0 }}</b> <span class="text-zinc-500">Repost</span></span>
        {{-- Jumlah suka pada post ini. --}}
        <span><b class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $status['jumlahLike'][$post->id] ?? 0 }}</b> <span class="text-zinc-500">Suka</span></span>
    </div>

    {{-- Kotak balas atau kutip di bawah postingan yang sedang dibahas. --}}
    <livewire:composer :parent="$post" :tipe="$mode" />

    {{-- Daftar balasan yang menempel pada postingan ini. --}}
    <div>
        @forelse ($balasan as $jawaban)
            {{-- Kartu postingan balasan. --}}
            <x-post-card :post="$jawaban" :status="$status" />
        @empty
            {{-- Keadaan kosong saat belum ada balasan. --}}
            <x-empty-state judul="Belum ada balasan" teks="Balasan pertama bisa jadi milik Anda." />
        @endforelse
    </div>
</div>
