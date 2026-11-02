{{-- Keterangan halaman galat: kode besar, judul, satu kalimat penjelas, dan tautan pulang. --}}
@props(['kode', 'judul', 'pesan'])

{{-- Isi ditengahkan mengikuti tata letak halaman tamu. --}}
<div class="text-center">
    {{-- Kode status sebagai angka besar bergaya X. --}}
    <p class="text-[64px] leading-none font-extrabold text-[#0f1419] dark:text-[#e7e9ea]">{{ $kode }}</p>
    {{-- Judul singkat yang menyebut masalahnya. --}}
    <p class="mt-3 text-[31px] leading-tight font-extrabold text-[#0f1419] dark:text-[#e7e9ea]">{{ $judul }}</p>
    {{-- Satu kalimat penjelas tentang apa yang bisa dilakukan pembaca. --}}
    <p class="mt-2 text-[15px] text-zinc-500">{{ $pesan }}</p>
    {{-- Satu-satunya jalan keluar yang pasti ada di semua halaman galat. --}}
    <a href="{{ route('home') }}" class="mt-6 inline-flex h-[44px] items-center justify-center rounded-full bg-[#1d9bf0] px-6 text-[15px] font-bold text-white transition-colors hover:bg-[#1a8cd8]">Kembali ke beranda</a>
</div>
