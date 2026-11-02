{{-- Tombol ikuti persis tombol akun web X dengan dua mode visual kontras. --}}
<div>
    @if ($mengikuti)
        {{-- Sedang mengikuti: label berganti merah saat kursor menyorot, sama seperti X. --}}
        <button wire:click="ikuti" type="button" class="group flex min-w-[7.5rem] items-center justify-center rounded-full border border-zinc-300 px-4 py-1.5 text-[15px] font-bold text-[#0f1419] transition-colors hover:border-red-600 hover:bg-red-500/10 hover:text-red-600 dark:border-zinc-600 dark:text-[#e7e9ea]">
            {{-- Label saat kursor tidak menyorot. --}}
            <span class="group-hover:hidden">Mengikuti</span>
            {{-- Label saat kursor menyorot. --}}
            <span class="hidden group-hover:inline">Berhenti mengikuti</span>
        </button>
    @else
        {{-- Keadaan belum mengikuti: tombol kontras hitam pekat di mode terang dan putih di mode gelap. --}}
        <button wire:click="ikuti" type="button" class="rounded-full bg-[#0f1419] px-4 py-1.5 text-[15px] font-bold text-white transition-opacity hover:opacity-90 dark:bg-[#eff3f4] dark:text-black">Ikuti</button>
    @endif
</div>
