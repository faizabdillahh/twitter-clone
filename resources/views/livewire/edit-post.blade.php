{{-- Halaman penyuntingan postingan persis tata letak formulir X. --}}
<div>
    {{-- Header sticky penyuntingan postingan dengan tombol kembali resmi X. --}}
    <div class="sticky top-[53px] z-20 flex h-[53px] items-center gap-6 border-b border-zinc-200 bg-white/85 px-4 backdrop-blur-md sm:top-0 dark:border-[#2f3336] dark:bg-black/85">
        {{-- Tombol kembali ke halaman rincian postingan. --}}
        <a href="{{ route('posts.show', $post) }}" class="flex size-9 items-center justify-center rounded-full transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.1]" wire:navigate aria-label="Batal dan kembali">
            {{-- Ikon panah kiri resmi X. --}}
            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                {{-- Jalur panah kembali. --}}
                <path d="M7.414 13l5.043 5.04-1.414 1.42L3.586 12l7.457-7.46 1.414 1.42L7.414 11H21v2H7.414z"></path>
            </svg>
        </a>
        {{-- Judul halaman edit postingan. --}}
        <h1 class="text-xl font-bold text-[#0f1419] dark:text-[#e7e9ea]">Edit post</h1>
    </div>

    {{-- Formulir penyuntingan teks kiriman. --}}
    <div class="p-4">
        {{-- Formulir penyimpanan pembaruan. --}}
        <form wire:submit="simpan">
            {{-- Textarea penyuntingan teks kiriman. --}}
            <textarea wire:model.live="body" rows="4" aria-label="Isi post" class="w-full resize-none rounded-2xl border border-zinc-200 bg-transparent p-4 text-[17px] leading-relaxed text-[#0f1419] outline-none transition placeholder:text-zinc-500 focus:border-[#1d9bf0] focus:ring-1 focus:ring-[#1d9bf0] dark:border-[#2f3336] dark:text-[#e7e9ea]"></textarea>

            @error('body')
                {{-- Pesan galat validasi bila teks tidak valid. --}}
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            {{-- Bilah tombol aksi penyuntingan. --}}
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                {{-- Penghitung karakter grapheme. --}}
                <span class="text-[13px] font-medium text-zinc-500">{{ grapheme_strlen($body) }}/250</span>

                {{-- Kumpulan tombol simpan, hapus, dan batal. --}}
                <div class="flex items-center gap-4">
                    {{-- Tautan membatalkan penyuntingan. --}}
                    <a href="{{ route('posts.show', $post) }}" class="text-[15px] font-bold text-zinc-500 hover:underline">Batal</a>

                    {{-- Tombol menghapus post dengan dialog konfirmasi. --}}
                    <button type="button" wire:click="hapus" wire:confirm="Hapus post ini? Tindakan ini tidak bisa dibatalkan." class="text-[15px] font-bold text-red-600 hover:underline">Hapus</button>

                    {{-- Tombol menyimpan perubahan teks berbentuk kapsul biru X. --}}
                    <flux:button type="submit" variant="primary" size="sm" class="rounded-full !bg-[#1d9bf0] px-4 py-1.5 text-[15px] font-bold text-white shadow-none transition-colors hover:!bg-[#1a8cd8]">Simpan</flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
