{{-- Tata letak utama aplikasi hasil reverse engineering dari web X (Twitter). --}}
<!DOCTYPE html>
{{-- Atribut bahasa dokumen HTML mengikuti pengaturan aplikasi. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    {{-- Kepala dokumen HTML untuk memuat favicon, font, dan berkas Vite. --}}
    <head>
        {{-- Partial head bersama. --}}
        @include('partials.head')
    </head>
    {{-- Tema warna X: hitam murni #000000 di mode gelap, putih murni di mode terang. --}}
    <body class="min-h-screen bg-white text-[#0f1419] antialiased dark:bg-black dark:text-[#e7e9ea]">
        {{-- Kontainer terpusat dengan lebar maksimum standar web X. --}}
        <div class="mx-auto flex min-h-screen max-w-7xl justify-center">
            {{-- Bilah navigasi kiri desktop (lebar 68px di tablet, 275px di layar lebar). --}}
            <aside class="sticky top-0 hidden h-screen w-[68px] shrink-0 flex-col justify-between border-r border-zinc-200 px-2 py-1 select-none sm:flex xl:w-[275px] xl:px-3 dark:border-[#2f3336]">
                {{-- Bagian atas navigasi: logo X dan daftar menu. --}}
                <div class="flex flex-col items-center xl:items-start">
                    {{-- Logo resmi X dengan wadah hover melingkar ukuran 52px. --}}
                    <a href="{{ route('home') }}" class="mb-1 flex size-[52px] items-center justify-center rounded-full transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.1]" wire:navigate aria-label="X Beranda">
                        {{-- Vektor lambang X. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current text-[#0f1419] dark:text-[#e7e9ea]">
                            {{-- Jalur resmi huruf X. --}}
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                        </svg>
                    </a>

                    {{-- Daftar navigasi menu utama. --}}
                    <nav class="flex w-full flex-col space-y-1">
                        {{-- Menu Beranda. --}}
                        <a href="{{ route('home') }}" wire:navigate class="group flex items-center gap-5 rounded-full p-3 transition-colors hover:bg-black/[0.05] xl:px-4 xl:py-3 dark:hover:bg-white/[0.1]">
                            {{-- Ikon rumah: terisi saat aktif, bergaris saat nonaktif. --}}
                            <div class="size-7 shrink-0">
                                @if (request()->routeIs('home'))
                                    {{-- Ikon rumah versi aktif. --}}
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current">
                                        {{-- Jalur rumah aktif terisi. --}}
                                        <path d="M12 1.696L.622 8.807l1.06 1.696L3 9.679V19.5C3 20.881 4.119 22 5.5 22h13c1.381 0 2.5-1.119 2.5-2.5V9.679l1.318.824 1.06-1.696L12 1.696zM12 16.5c-1.381 0-2.5-1.119-2.5-2.5s1.119-2.5 2.5-2.5 2.5 1.119 2.5 2.5-1.119 2.5-2.5 2.5z"></path>
                                    </svg>
                                @else
                                    {{-- Ikon rumah versi nonaktif. --}}
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current">
                                        {{-- Jalur rumah nonaktif. --}}
                                        <path d="M12 1.696L.622 8.807l1.06 1.696L3 9.679V19.5C3 20.881 4.119 22 5.5 22h13c1.381 0 2.5-1.119 2.5-2.5V9.679l1.318.824 1.06-1.696L12 1.696zM12 3.97l8 5V19.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V8.97l6-3.75 6 3.75z"></path>
                                    </svg>
                                @endif
                            </div>
                            {{-- Label teks Beranda ukuran 20px khas X. --}}
                            <span class="hidden text-xl xl:inline {{ request()->routeIs('home') ? 'font-bold' : 'font-normal' }}">Beranda</span>
                        </a>

                        {{-- Menu Cari / Jelajahi. --}}
                        <a href="{{ route('search') }}" wire:navigate class="group flex items-center gap-5 rounded-full p-3 transition-colors hover:bg-black/[0.05] xl:px-4 xl:py-3 dark:hover:bg-white/[0.1]">
                            {{-- Ikon kaca pembesar X. --}}
                            <div class="size-7 shrink-0">
                                {{-- Vektor kaca pembesar. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current">
                                    {{-- Jalur kaca pembesar. --}}
                                    <path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path>
                                </svg>
                            </div>
                            {{-- Label teks Cari. --}}
                            <span class="hidden text-xl xl:inline {{ request()->routeIs('search', 'tag.*') ? 'font-bold' : 'font-normal' }}">Cari</span>
                        </a>

                        {{-- Menu Notifikasi / Aktivitas. --}}
                        <a href="{{ route('activities') }}" wire:navigate class="group flex items-center gap-5 rounded-full p-3 transition-colors hover:bg-black/[0.05] xl:px-4 xl:py-3 dark:hover:bg-white/[0.1]">
                            {{-- Ikon lonceng: terisi saat aktif, bergaris saat nonaktif. --}}
                            <div class="size-7 shrink-0">
                                @if (request()->routeIs('activities'))
                                    {{-- Ikon lonceng versi aktif. --}}
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current">
                                        {{-- Jalur lonceng terisi. --}}
                                        <path d="M21.996 16.59c0 .5-.22 1-.6 1.3l-.9.71H3.5l-.9-.71c-.38-.3-.6-.8-.6-1.3 0-1.8 1-3.6 2.6-4.6l.4-.3V9c0-4.4 3.6-8 8-8s8 3.6 8 8v2.69l.4.3c1.6 1 2.6 2.8 2.6 4.6zm-8 4.41c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2z"></path>
                                    </svg>
                                @else
                                    {{-- Ikon lonceng versi nonaktif: badan bergaris, pemukul tetap terisi. --}}
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 stroke-current" fill="currentColor" stroke-width="2" stroke-linejoin="round">
                                        {{-- Badan lonceng yang hanya digariskan. --}}
                                        <path d="M21.996 16.59c0 .5-.22 1-.6 1.3l-.9.71H3.5l-.9-.71c-.38-.3-.6-.8-.6-1.3 0-1.8 1-3.6 2.6-4.6l.4-.3V9c0-4.4 3.6-8 8-8s8 3.6 8 8v2.69l.4.3c1.6 1 2.6 2.8 2.6 4.6z" fill="none"></path>
                                        {{-- Pemukul lonceng di bawah, tetap terisi penuh. --}}
                                        <path d="M13.996 21c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2z"></path>
                                    </svg>
                                @endif
                            </div>
                            {{-- Label teks Aktivitas. --}}
                            <span class="hidden text-xl xl:inline {{ request()->routeIs('activities') ? 'font-bold' : 'font-normal' }}">Aktivitas</span>
                        </a>

                        {{-- Menu Profil pribadi. --}}
                        <a href="{{ route('profile.show', auth()->user()) }}" wire:navigate class="group flex items-center gap-5 rounded-full p-3 transition-colors hover:bg-black/[0.05] xl:px-4 xl:py-3 dark:hover:bg-white/[0.1]">
                            {{-- Ikon siluet orang X. --}}
                            <div class="size-7 shrink-0">
                                {{-- Vektor orang profil. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-7 fill-current">
                                    {{-- Jalur profil orang. --}}
                                    <path d="M12 11.816c3.297 0 5.971-2.673 5.971-5.97s-2.674-5.971-5.971-5.971c-3.297 0-5.971 2.674-5.971 5.971s2.674 5.97 5.971 5.97zm0-9.942c2.19 0 3.971 1.781 3.971 3.971 0 2.191-1.781 3.972-3.971 3.972s-3.971-1.781-3.971-3.972c0-2.19 1.781-3.971 3.971-3.971zm7.716 19.493c0-3.68-2.984-6.664-6.664-6.664H10.95c-3.68 0-6.664 2.984-6.664 6.664 0 .414.336.75.75.75h14.93c.414 0 .75-.336.75-.75zm-14.73-.75c.34-2.604 2.574-4.664 5.264-4.664h2.1c2.69 0 4.924 2.06 5.264 4.664H4.986z"></path>
                                </svg>
                            </div>
                            {{-- Label teks Profil. --}}
                            <span class="hidden text-xl xl:inline {{ request()->url() === route('profile.show', auth()->user()) ? 'font-bold' : 'font-normal' }}">Profil</span>
                        </a>
                    </nav>

                    {{-- Tombol Post selebar rel, sama seperti tombol Post x.com. --}}
                    <flux:button variant="primary" :href="route('home').'#tulis'" x-on:click.prevent="$flux.modal('tulis').show()" class="mt-4 hidden h-[52px] w-full items-center justify-center rounded-full !bg-[#1d9bf0] text-[17px] font-bold text-white shadow-none transition-colors hover:!bg-[#1a8cd8] xl:flex">Post</flux:button>
                    {{-- Tombol Post bulat kompak untuk layar tablet. --}}
                    <flux:button variant="primary" :href="route('home').'#tulis'" x-on:click.prevent="$flux.modal('tulis').show()" class="mt-4 flex size-[50px] items-center justify-center rounded-full !bg-[#1d9bf0] text-white shadow-none transition-colors hover:!bg-[#1a8cd8] xl:hidden">
                        {{-- Ikon bulu tulis tweet resmi X. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current">
                            {{-- Jalur bulu pena. --}}
                            <path d="M23 3c-6.62-.1-10.38 2.41-13.05 4.73C7.29 10.04 6 12 2 12c1.72 1.3 3.67 2.1 6 2.5.34 2.1 1.2 3.8 2.5 5.5 0-4 1.96-5.29 4.27-7.95C17.09 9.38 19.6 5.62 23 3z"></path>
                        </svg>
                    </flux:button>
                </div>

                {{-- Pil profil akun pengguna di bagian bawah sidebar kiri. --}}
                <div class="my-3 w-full">
                    {{-- Dropdown menu opsi akun. --}}
                    <flux:dropdown position="top" align="start" class="w-full">
                        {{-- Tombol pemicu profil. --}}
                        <button type="button" class="flex w-full items-center justify-between rounded-full p-2 transition-colors hover:bg-black/[0.05] xl:p-3 dark:hover:bg-white/[0.1]">
                            {{-- Avatar dan nama akun. --}}
                            <div class="flex items-center gap-3">
                                {{-- Avatar bulat ukuran 40px. --}}
                                <x-avatar :user="auth()->user()" size="md" />
                                {{-- Nama dan username ditampilkan di layar lebar. --}}
                                <div class="hidden text-left text-[15px] leading-tight xl:block">
                                    {{-- Nama lengkap akun pengguna. --}}
                                    <div class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ auth()->user()->name }}</div>
                                    {{-- Username diawali simbol @. --}}
                                    <div class="text-zinc-500">&#64;{{ auth()->user()->username }}</div>
                                </div>
                            </div>
                            {{-- Ikon tiga titik horizontal. --}}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="hidden size-5 fill-current text-zinc-500 xl:block">
                                {{-- Tiga titik opsi. --}}
                                <path d="M3 12c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm9 2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm7 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"></path>
                            </svg>
                        </button>

                        {{-- Isi popover menu akun. --}}
                        <flux:menu class="w-[260px] rounded-2xl border border-zinc-200 bg-white p-2 shadow-2xl dark:border-[#2f3336] dark:bg-black">
                            {{-- Header informasi akun pengguna. --}}
                            <div class="p-3 text-sm">
                                {{-- Nama akun tebal. --}}
                                <div class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ auth()->user()->name }}</div>
                                {{-- Username akun abu-abu. --}}
                                <div class="text-zinc-500">&#64;{{ auth()->user()->username }}</div>
                            </div>
                            {{-- Pemisah menu. --}}
                            <flux:menu.separator />
                            {{-- Tautan profil pribadi. --}}
                            <flux:menu.item :href="route('profile.show', auth()->user())" icon="user" wire:navigate>Buka Profil</flux:menu.item>
                            {{-- Tautan pengaturan akun, posisinya sama seperti menu X. --}}
                            <flux:menu.item :href="route('settings.profile')" icon="cog-6-tooth" wire:navigate>Pengaturan</flux:menu.item>
                            {{-- Pemisah sebelum logout. --}}
                            <flux:menu.separator />
                            {{-- Formulir keluar akun. --}}
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                {{-- Tombol konfirmasi keluar akun. --}}
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-red-500">Keluar &#64;{{ auth()->user()->username }}</flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </aside>

            {{-- Kolom utama linimasa dengan lebar pas 600px khas X. --}}
            <div class="flex min-h-screen w-full flex-col sm:w-[600px]">
                {{-- Header mobile di bagian paling atas ponsel (layar < 640px). --}}
                <header class="sticky top-0 z-30 flex h-[53px] items-center justify-between border-b border-zinc-200 bg-white/85 px-4 backdrop-blur-md sm:hidden dark:border-[#2f3336] dark:bg-black/85">
                    {{-- Avatar akun di pojok kiri atas ponsel. --}}
                    <a href="{{ route('profile.show', auth()->user()) }}" wire:navigate aria-label="Profil saya">
                        {{-- Avatar foto ukuran 32px. --}}
                        <x-avatar :user="auth()->user()" size="sm" />
                    </a>

                    {{-- Logo resmi X di tengah header ponsel. --}}
                    <a href="{{ route('home') }}" wire:navigate aria-label="Beranda X">
                        {{-- Vektor logo X kecil. --}}
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current text-[#0f1419] dark:text-[#e7e9ea]">
                            {{-- Bentuk huruf X. --}}
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                        </svg>
                    </a>

                    {{-- Penyeimbang tata letak header kanan agar logo X tetap berada di tengah. --}}
                    <div class="size-8"></div>
                </header>

                {{-- Area konten utama 600px dengan pembatas garis vertikal kanan. --}}
                <main class="min-h-screen w-full border-r border-zinc-200 pb-20 sm:w-[600px] sm:pb-0 dark:border-[#2f3336]">
                    {{-- Slot tampilan komponen Livewire. --}}
                    {{ $slot }}
                </main>
            </div>

            {{-- Bilah samping kanan desktop (lebar 350px khas web X). --}}
            <aside class="sticky top-0 hidden h-screen w-[350px] shrink-0 overflow-y-auto px-6 py-3 lg:block">
                {{-- Formulir pencarian berbentuk kapsul dengan latar abu-abu gelap #16181c. --}}
                <form action="{{ route('search') }}" method="GET" class="group relative">
                    {{-- Ikon kaca pembesar abu-abu. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="absolute top-3 left-4 size-4.5 fill-current text-zinc-500 transition-colors group-focus-within:text-[#1d9bf0]">
                        {{-- Bentuk kaca pembesar. --}}
                        <path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path>
                    </svg>
                    {{-- Kotak input teks pencarian X. --}}
                    <input type="text" name="q" placeholder="Cari" class="h-[44px] w-full rounded-full border border-transparent bg-zinc-100 pr-4 pl-12 text-[15px] text-[#0f1419] outline-none transition placeholder:text-zinc-500 focus:border-[#1d9bf0] focus:bg-white focus:ring-1 focus:ring-[#1d9bf0] dark:bg-[#16181c] dark:text-[#e7e9ea] dark:focus:bg-black" />
                </form>

                {{-- Tren hashtag dengan jumlah post terbanyak. --}}
                <livewire:trends />

                {{-- Rekomendasi pengguna untuk diikuti di bilah samping kanan. --}}
                <livewire:who-to-follow />
            </aside>
        </div>

        {{-- Bilah navigasi bawah tetap di ponsel (tinggi 53px). --}}
        <nav class="fixed bottom-0 left-0 right-0 z-40 flex h-[53px] items-center justify-around border-t border-zinc-200 bg-white/85 backdrop-blur-md sm:hidden dark:border-[#2f3336] dark:bg-black/85">
            {{-- Navigasi Beranda ponsel. --}}
            <a href="{{ route('home') }}" wire:navigate class="flex size-10 items-center justify-center text-[#0f1419] dark:text-[#e7e9ea]" aria-label="Beranda">
                {{-- Ikon rumah ponsel. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6.5 fill-current">
                    {{-- Jalur ikon rumah. --}}
                    <path d="M12 1.696L.622 8.807l1.06 1.696L3 9.679V19.5C3 20.881 4.119 22 5.5 22h13c1.381 0 2.5-1.119 2.5-2.5V9.679l1.318.824 1.06-1.696L12 1.696zM12 16.5c-1.381 0-2.5-1.119-2.5-2.5s1.119-2.5 2.5-2.5 2.5 1.119 2.5 2.5-1.119 2.5-2.5 2.5z"></path>
                </svg>
            </a>

            {{-- Navigasi Cari ponsel. --}}
            <a href="{{ route('search') }}" wire:navigate class="flex size-10 items-center justify-center text-[#0f1419] dark:text-[#e7e9ea]" aria-label="Cari">
                {{-- Ikon kaca pembesar ponsel. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6.5 fill-current">
                    {{-- Jalur pencarian. --}}
                    <path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path>
                </svg>
            </a>

            {{-- Navigasi Aktivitas ponsel. --}}
            <a href="{{ route('activities') }}" wire:navigate class="flex size-10 items-center justify-center text-[#0f1419] dark:text-[#e7e9ea]" aria-label="Aktivitas">
                {{-- Ikon lonceng notifikasi ponsel. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6.5 fill-current">
                    {{-- Jalur lonceng. --}}
                    <path d="M21.996 16.59c0 .5-.22 1-.6 1.3l-.9.71H3.5l-.9-.71c-.38-.3-.6-.8-.6-1.3 0-1.8 1-3.6 2.6-4.6l.4-.3V9c0-4.4 3.6-8 8-8s8 3.6 8 8v2.69l.4.3c1.6 1 2.6 2.8 2.6 4.6zm-8 4.41c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2z"></path>
                </svg>
            </a>

            {{-- Navigasi Profil ponsel. --}}
            <a href="{{ route('profile.show', auth()->user()) }}" wire:navigate class="flex size-10 items-center justify-center text-[#0f1419] dark:text-[#e7e9ea]" aria-label="Profil">
                {{-- Ikon orang profil ponsel. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6.5 fill-current">
                    {{-- Jalur profil. --}}
                    <path d="M12 11.816c3.297 0 5.971-2.673 5.971-5.97s-2.674-5.971-5.971-5.971c-3.297 0-5.971 2.674-5.971 5.971s2.674 5.97 5.971 5.97zm0-9.942c2.19 0 3.971 1.781 3.971 3.971 0 2.191-1.781 3.972-3.971 3.972s-3.971-1.781-3.971-3.972c0-2.19 1.781-3.971 3.971-3.971zm7.716 19.493c0-3.68-2.984-6.664-6.664-6.664H10.95c-3.68 0-6.664 2.984-6.664 6.664 0 .414.336.75.75.75h14.93c.414 0 .75-.336.75-.75zm-14.73-.75c.34-2.604 2.574-4.664 5.264-4.664h2.1c2.69 0 4.924 2.06 5.264 4.664H4.986z"></path>
                </svg>
            </a>
        </nav>

        {{-- Tombol aksi melayang (FAB) di kanan bawah ponsel. --}}
        <flux:button variant="primary" :href="route('home').'#tulis'" x-on:click.prevent="$flux.modal('tulis').show()" class="fixed right-4 bottom-18 z-40 flex size-14 items-center justify-center rounded-full !bg-[#1d9bf0] text-white shadow-lg transition-transform active:scale-95 sm:hidden hover:!bg-[#1a8cd8]" aria-label="Tulis post baru">
            {{-- Ikon bulu tulis tweet resmi. --}}
            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-6 fill-current">
                {{-- Jalur bulu pena. --}}
                <path d="M23 3c-6.62-.1-10.38 2.41-13.05 4.73C7.29 10.04 6 12 2 12c1.72 1.3 3.67 2.1 6 2.5.34 2.1 1.2 3.8 2.5 5.5 0-4 1.96-5.29 4.27-7.95C17.09 9.38 19.6 5.62 23 3z"></path>
            </svg>
        </flux:button>

        {{-- Pemberitahuan singkat di bawah layar, dipakai aksi salin tautan. --}}
        <flux:toast />

        {{-- Dialog tulis ala x.com, dibuka dari tombol Post rail kiri dan tombol melayang ponsel. --}}
        {{-- Dialog menutup sendiri lewat event post-created. --}}
        {{-- closable dimatikan karena tombol tutup sudah ada di header dialog. --}}
        {{-- Buka dan tutup memakai API Flux: $flux.modal('tulis'), bukan event open-modal. --}}
        <flux:modal name="tulis" :closable="false" class="w-full max-w-[600px] overflow-hidden !rounded-2xl !p-0 [&>div:last-child]:border-b-0 dark:!bg-black" x-on:post-created.window="$flux.modal('tulis').close()">
            {{-- Header dialog 53px berisi tombol tutup, sama seperti dialog tulis x.com. --}}
            <div class="flex h-[53px] items-center border-b border-zinc-200 px-4 dark:border-[#2f3336]">
                {{-- Tombol penutup dialog; elemen flux:modal.close tidak hidup di Flux 2.21. --}}
                {{-- Tombol bulat berikon silang. --}}
                <button type="button" x-on:click="$flux.modal('tulis').close()" class="-ml-2 flex size-9 items-center justify-center rounded-full transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.1]" aria-label="Tutup">
                    {{-- Ikon silang penutup. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                        {{-- Jalur silang. --}}
                        <path d="M13.414 12l5.793-5.793c.39-.39.39-1.023 0-1.414s-1.023-.39-1.414 0L12 10.586 6.207 4.793c-.39-.39-1.023-.39-1.414 0s-.39 1.023 0 1.414L10.586 12l-5.793 5.793c-.39.39-.39 1.023 0 1.414.195.195.45.293.707.293s.512-.098.707-.293L12 13.414l5.793 5.793c.195.195.45.293.707.293s.512-.098.707-.293c.39-.39.39-1.023 0-1.414L13.414 12z"></path>
                    </svg>
                </button>
            </div>
            {{-- Kotak tulis yang sama dengan beranda, tetapi tanpa rantai otomatis dan langsung fokus. --}}
            <livewire:composer :merantai-otomatis="false" :fokus-otomatis="true" />
        </flux:modal>

        {{-- Skrip JavaScript Flux bawaan. --}}
        @fluxScripts
    </body>
</html>
