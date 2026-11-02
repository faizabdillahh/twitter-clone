{{-- Judul dan penjelasan di atas formulir halaman auth. --}}
@props([
    'title',
    'description',
])

{{-- Rata kiri agar sejalan dengan formulir di panel kanan. --}}
<div class="flex w-full flex-col gap-1.5">
    {{-- Judul halaman. --}}
    <h1 class="text-[26px] font-extrabold tracking-tight text-[#0f1419] dark:text-[#e7e9ea]">{{ $title }}</h1>
    {{-- Satu kalimat penjelas di bawah judul. --}}
    <p class="text-[15px] text-zinc-500">{{ $description }}</p>
</div>
