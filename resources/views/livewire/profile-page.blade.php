{{-- Halaman profil pengguna persis tata letak web X: spanduk, avatar tumpuk, dan 6 tab. --}}
<div>
    {{-- Header sticky profil dengan tinggi 53px, panah kembali, nama, dan jumlah post. --}}
    <div class="sticky top-[53px] z-20 flex h-[53px] items-center gap-6 border-b border-zinc-200 bg-white/85 px-4 backdrop-blur-md sm:top-0 dark:border-[#2f3336] dark:bg-black/85">
        {{-- Tombol panah kembali ke beranda. --}}
        <a href="{{ route('home') }}" class="flex size-9 items-center justify-center rounded-full transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.1]" wire:navigate aria-label="Kembali ke beranda">
            {{-- Ikon panah kiri resmi X. --}}
            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                {{-- Jalur panah kembali. --}}
                <path d="M7.414 13l5.043 5.04-1.414 1.42L3.586 12l7.457-7.46 1.414 1.42L7.414 11H21v2H7.414z"></path>
            </svg>
        </a>
        {{-- Ringkasan nama dan jumlah post di bilah atas. --}}
        <div>
            {{-- Nama pemilik profil tebal 20px. --}}
            <h1 class="text-xl font-bold leading-tight text-[#0f1419] dark:text-[#e7e9ea]">{{ $user->name }}</h1>
            {{-- Jumlah postingan dalam ukuran 13px abu-abu. --}}
            <p class="text-[13px] text-zinc-500">{{ $jumlahPost }} Postingan</p>
        </div>
    </div>

    {{-- Spanduk profil berasio 3:1. --}}
    <div class="h-36 w-full overflow-hidden bg-zinc-200 sm:h-48 md:h-52 dark:bg-[#333639]">
        @if ($user->bannerUrl())
            {{-- Foto spanduk akun. --}}
            <img src="{{ $user->bannerUrl() }}" alt="Spanduk {{ $user->name }}" class="size-full object-cover" />
        @endif
    </div>

    {{-- Rincian profil di bawah spanduk. --}}
    <div class="border-b border-zinc-200 px-4 pb-4 dark:border-[#2f3336]">
        {{-- Baris avatar menumpuk spanduk dan tombol aksi. --}}
        <div class="flex items-start justify-between">
            {{-- Avatar profil bertumpuk ke atas spanduk dengan garis tepi 4px. --}}
            <div class="-mt-14 sm:-mt-18 relative z-10">
                {{-- Avatar bundar ukuran besar. --}}
                <x-avatar :user="$user" size="lg" class="!size-28 sm:!size-36 border-4 border-white dark:border-black" />
            </div>

            {{-- Tombol aksi profil di sudut kanan. --}}
            <div class="mt-3">
                @if (auth()->id() === $user->id)
                    {{-- Tombol edit profil berbentuk pil bulat khas X. --}}
                    <a href="{{ route('settings.profile') }}" wire:navigate class="rounded-full border border-zinc-300 px-4 py-1.5 text-[15px] font-bold text-[#0f1419] transition-colors hover:bg-black/[0.05] dark:border-zinc-700 dark:text-[#e7e9ea] dark:hover:bg-white/[0.1]">Edit profil</a>
                @else
                    {{-- Komponen tombol ikuti. --}}
                    <livewire:follow-button :user="$user" :key="'ikuti-'.$user->id" />
                @endif
            </div>
        </div>

        {{-- Nama dan handle akun. --}}
        <div class="mt-3">
            {{-- Nama tampilan ukuran 20px tebal. --}}
            <h2 class="text-xl font-extrabold text-[#0f1419] dark:text-[#e7e9ea]">{{ $user->name }}</h2>
            {{-- Handle username diawali simbol at. --}}
            <p class="text-[15px] text-zinc-500">&#64;{{ $user->username }}</p>
        </div>

        @if ($user->bio)
            {{-- Teks bio deskripsi profil. --}}
            <p class="mt-3 whitespace-pre-line text-[15px] leading-normal text-[#0f1419] dark:text-[#e7e9ea]">{{ $user->bio }}</p>
        @endif

        {{-- Baris metadata: lokasi, situs, dan waktu bergabung. --}}
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[15px] text-zinc-500">
            @if ($user->location)
                {{-- Lokasi dengan ikon pin resmi X. --}}
                <div class="flex items-center gap-1">
                    {{-- Ikon pin lokasi. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4.5 fill-current">
                        {{-- Jalur pin. --}}
                        <path d="M12 7c-1.93 0-3.5 1.57-3.5 3.5S10.07 14 12 14s3.5-1.57 3.5-3.5S13.93 7 12 7zm0 5c-.827 0-1.5-.673-1.5-1.5S11.173 9 12 9s1.5.673 1.5 1.5S12.827 12 12 12zm0-10c-4.687 0-8.5 3.813-8.5 8.5 0 5.98 7.748 11.233 8.077 11.455.127.086.275.13.423.13s.296-.044.423-.13C12.752 21.733 20.5 16.48 20.5 10.5 20.5 5.813 16.687 2 12 2zm0 18.08C10.428 18.91 5.5 14.77 5.5 10.5c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5c0 4.27-4.928 8.41-6.5 9.58z"></path>
                    </svg>
                    {{-- Teks kota. --}}
                    <span>{{ $user->location }}</span>
                </div>
            @endif

            @if ($user->website)
                {{-- Situs web dengan ikon tautan resmi X. --}}
                <div class="flex items-center gap-1">
                    {{-- Ikon rantai tautan. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4.5 fill-current">
                        {{-- Jalur rantai. --}}
                        <path d="M18.36 5.64c-1.95-1.96-5.11-1.96-7.07 0L9.88 7.05 8.46 5.64l1.42-1.42c2.73-2.73 7.16-2.73 9.9 0 2.73 2.74 2.73 7.17 0 9.9l-1.42 1.42-1.41-1.42 1.41-1.41c1.96-1.96 1.96-5.12 0-7.07zm-2.12 3.53l-7.07 7.07-1.41-1.41 7.07-7.07 1.41 1.41zm-4.95 4.25l-1.41 1.41c-1.96 1.96-5.12 1.96-7.07 0-1.96-1.95-1.96-5.11 0-7.07l1.41-1.42 1.42 1.42-1.42 1.41c-1.17 1.18-1.17 3.09 0 4.25 1.17 1.17 3.08 1.17 4.24 0l1.42-1.41 1.41 1.41z"></path>
                    </svg>
                    {{-- Tautan URL biru X. --}}
                    <a href="{{ $user->website }}" target="_blank" rel="nofollow noopener" class="text-[#1d9bf0] hover:underline">{{ $user->website }}</a>
                </div>
            @endif

            {{-- Waktu bergabung dengan ikon kalender resmi X. --}}
            <div class="flex items-center gap-1">
                {{-- Ikon kalender. --}}
                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4.5 fill-current">
                    {{-- Jalur kalender. --}}
                    <path d="M7 4V3h2v1h6V3h2v1h1.5C19.89 4 21 5.12 21 6.5v12c0 1.38-1.11 2.5-2.5 2.5h-13C4.12 21 3 19.88 3 18.5v-12C3 5.12 4.12 4 5.5 4H7zm0 2H5.5c-.27 0-.5.22-.5.5v1.5h14V6.5c0-.28-.23-.5-.5-.5H17v1h-2V6H9v1H7V6zm12 4H5v8.5c0 .28.23.5.5.5h13c.28 0 .5-.22.5-.5V10z"></path>
                </svg>
                {{-- Tahun pembuatan akun. --}}
                <span>Bergabung {{ $user->created_at->format('Y') }}</span>
            </div>
        </div>

        {{-- Baris angka metrik sosial pengikut dan mengikuti. --}}
        <div class="mt-3 flex gap-4 text-[15px] text-zinc-500">
            {{-- Jumlah akun yang diikuti. --}}
            <button type="button" wire:click="$set('tab', 'mengikuti')" class="hover:underline">
                {{-- Angka tebal mengikuti. --}}
                <span class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $jumlahMengikuti }}</span> Mengikuti
            </button>
            {{-- Jumlah pengikut akun ini. --}}
            <button type="button" wire:click="$set('tab', 'pengikut')" class="hover:underline">
                {{-- Angka tebal pengikut. --}}
                <span class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $jumlahPengikut }}</span> Pengikut
            </button>
        </div>
    </div>

    {{-- Bilah 6 tab profil dengan garis indikator biru di bawah teks tab aktif. --}}
    <div class="flex border-b border-zinc-200 select-none dark:border-[#2f3336]">
        @foreach ($this->tabs() as $kunci => $label)
            {{-- Tombol navigasi tab profil. --}}
            <button type="button" wire:click="$set('tab', '{{ $kunci }}')" aria-pressed="{{ $tab === $kunci ? 'true' : 'false' }}" class="flex flex-1 items-center justify-center transition-colors hover:bg-black/[0.05] dark:hover:bg-white/[0.05]">
                {{-- Pembungkus teks dan garis aktif. --}}
                <div class="relative flex h-full items-center py-3.5">
                    {{-- Teks nama tab. --}}
                    <span class="text-[15px] {{ $tab === $kunci ? 'font-bold text-[#0f1419] dark:text-[#e7e9ea]' : 'font-medium text-zinc-500' }}">{{ $label }}</span>
                    @if ($tab === $kunci)
                        {{-- Garis indikator biru aktif persis selebar teks tab. --}}
                        <span class="absolute bottom-0 left-0 right-0 h-1 rounded-full bg-[#1d9bf0]"></span>
                    @endif
                </div>
            </button>
        @endforeach
    </div>

    @if ($orang !== null)
        {{-- Daftar akun untuk tab pengikut atau mengikuti. --}}
        <div>
            @forelse ($orang as $akun)
                {{-- Baris akun pengguna dengan avatar dan tombol ikuti. --}}
                <div class="flex items-center gap-3 border-b border-zinc-200 px-4 py-3 dark:border-[#2f3336]">
                    {{-- Avatar akun. --}}
                    <a href="{{ route('profile.show', $akun) }}" wire:navigate class="shrink-0">
                        {{-- Avatar bulat ukuran 40px. --}}
                        <x-avatar :user="$akun" size="md" />
                    </a>

                    {{-- Informasi identitas dan cuplikan bio. --}}
                    <div class="min-w-0 flex-1">
                        {{-- Nama akun dengan tautan ke profil. --}}
                        <a href="{{ route('profile.show', $akun) }}" class="block truncate text-[15px] font-bold text-[#0f1419] hover:underline dark:text-[#e7e9ea]" wire:navigate>{{ $akun->name }}</a>
                        {{-- Username akun. --}}
                        <p class="text-sm text-zinc-500">&#64;{{ $akun->username }}</p>
                        @if ($akun->bio)
                            {{-- Cuplikan bio. --}}
                            <p class="mt-1 line-clamp-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $akun->bio }}</p>
                        @endif
                    </div>

                    @if (auth()->id() !== $akun->id)
                        {{-- Tombol ikuti untuk akun orang lain. --}}
                        <livewire:follow-button :user="$akun" :key="'ikut-'.$akun->id" />
                    @endif
                </div>
            @empty
                {{-- Keadaan kosong untuk daftar akun. --}}
                <x-empty-state judul="Belum ada akun" teks="Akun yang mengikuti atau diikuti akan muncul di sini." />
            @endforelse
        </div>
    @else
        {{-- Daftar kartu postingan sesuai tab yang dipilih. --}}
        <div>
            @forelse ($posts as $post)
                {{-- Kartu postingan di tab profil; tab balasan menampilkan induknya. --}}
                <x-post-card :post="$post" :status="$status" :tampilkan-induk="$tab === 'balasan'" />
            @empty
                {{-- Keadaan ketiadaan postingan. --}}
                <x-empty-state judul="Belum ada post" teks="Post yang tampil di tab ini akan muncul di sini." />
            @endforelse
        </div>
    @endif
</div>
