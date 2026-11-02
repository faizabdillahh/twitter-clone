{{-- Kerangka auth dua kolom: panel merek gelap di kiri, formulir di kanan. --}}
<!DOCTYPE html>
{{-- Bahasa dokumen mengikuti locale aplikasi. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    {{-- Bagian kepala dokumen: judul tab, huruf, CSS, dan skrip tema. --}}
    <head>
        @include('partials.head')
    </head>
    {{-- Latar mengikuti tema, kolom mengisi seluruh tinggi layar. --}}
    <body class="min-h-svh bg-white antialiased dark:bg-black">
        {{-- Dua kolom berdampingan mulai layar lebar. --}}
        <div class="flex min-h-svh">
            {{-- Panel merek: disembunyikan di ponsel agar formulir tetap lega. --}}
            <aside class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-[#0f1419] p-10 lg:flex xl:w-[55%]">
                {{-- Cahaya biru samar di sudut kiri atas panel. --}}
                <div class="pointer-events-none absolute -top-32 -left-32 size-[520px] rounded-full bg-[#1d9bf0]/20 blur-3xl" aria-hidden="true"></div>
                {{-- Lambang dan nama aplikasi di puncak panel. --}}
                <a href="{{ route('home') }}" class="relative flex items-center gap-2 text-[#e7e9ea]">
                    {{-- Vektor lambang X. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-8 fill-current">
                        {{-- Jalur resmi huruf X. --}}
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                    </svg>
                    {{-- Nama aplikasi di samping lambang. --}}
                    <span class="text-xl font-extrabold tracking-tight">{{ config('app.name') }}</span>
                </a>
                {{-- Janji singkat aplikasi di tengah panel. --}}
                <div class="relative max-w-md">
                    {{-- Kalimat utama. --}}
                    <p class="text-[40px] leading-[1.15] font-extrabold tracking-tight text-white">Percakapan singkat, tanpa basa-basi.</p>
                    {{-- Satu kalimat penjelas di bawahnya. --}}
                    <p class="mt-4 text-[17px] leading-relaxed text-zinc-400">Tulis, balas, kutip, dan ikuti akun yang menarik bagi Anda. Semua dalam satu kolom yang rapi.</p>
                </div>
                {{-- Keterangan hak cipta di kaki panel. --}}
                <p class="relative text-[13px] text-zinc-500">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </aside>

            {{-- Panel formulir, setengah layar di layar lebar. --}}
            <main class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:border-l lg:border-zinc-200 xl:w-[45%] dark:lg:border-zinc-800">
                {{-- Kolom sempit agar isian tetap nyaman dibaca. --}}
                <div class="mx-auto w-full max-w-[400px]">
                    {{-- Lambang versi ponsel, karena panel merek disembunyikan. --}}
                    <a href="{{ route('home') }}" class="mb-10 flex items-center gap-2 text-[#0f1419] lg:hidden dark:text-[#e7e9ea]">
                        {{-- Vektor lambang X. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current">
                            {{-- Jalur resmi huruf X. --}}
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                        </svg>
                        {{-- Nama aplikasi di samping lambang. --}}
                        <span class="text-lg font-extrabold tracking-tight">{{ config('app.name') }}</span>
                    </a>
                    {{-- Judul, formulir, dan tautan antarhalaman. --}}
                    {{ $slot }}
                </div>
            </main>
        </div>
        @fluxScripts
    </body>
</html>
