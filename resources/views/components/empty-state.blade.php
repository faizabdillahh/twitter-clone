{{-- Keadaan kosong bergaya X: judul besar tebal dengan satu kalimat penjelas. --}}
@props(['judul', 'teks'])

{{-- Pembungkus terpusat dengan lebar baca yang nyaman. --}}
<div {{ $attributes->merge(['class' => 'mx-auto max-w-md px-8 py-12 text-center']) }}>
    {{-- Judul besar khas halaman kosong X. --}}
    <p class="text-[31px] leading-tight font-extrabold text-[#0f1419] dark:text-[#e7e9ea]">{{ $judul }}</p>
    {{-- Satu kalimat penjelas di bawah judul. --}}
    <p class="mt-2 text-[15px] text-zinc-500">{{ $teks }}</p>
</div>
