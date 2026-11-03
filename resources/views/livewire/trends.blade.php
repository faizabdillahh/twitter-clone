{{-- Widget tren: tag yang paling banyak dipakai di bilah kanan. --}}
<div>
    @if ($tren->isNotEmpty())
        {{-- Wadah widget dengan latar yang sama seperti rekomendasi akun. --}}
        <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50/50 py-3 dark:border-[#2f3336] dark:bg-[#16181c]">
            {{-- Judul widget tren. --}}
            <h2 class="px-4 text-[20px] font-extrabold text-[#0f1419] dark:text-[#e7e9ea]">Tren untuk Anda</h2>

            {{-- Daftar tag beserta jumlah postnya. --}}
            <div class="mt-1">
                @foreach ($tren as $tag)
                    {{-- Baris tag yang membuka halaman tag terkait. --}}
                    <a href="{{ route('tag.show', $tag->tag) }}" wire:navigate class="block px-4 py-2 transition hover:bg-black/5 dark:hover:bg-white/5" wire:key="tren-{{ $tag->id }}">
                        {{-- Keterangan kecil di atas nama tag. --}}
                        <div class="text-[13px] text-zinc-500">Tren</div>
                        {{-- Nama tag dengan tanda pagar. --}}
                        <div class="truncate text-[15px] font-bold text-[#0f1419] dark:text-[#e7e9ea]">#{{ $tag->tag }}</div>
                        {{-- Jumlah post yang memakai tag ini. --}}
                        <div class="text-[13px] text-zinc-500">{{ $tag->posts_count }} post</div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
