{{-- Kotak tulis postingan persis antarmuka X: avatar kiri, textarea 20px, dan tombol aksi. --}}
{{-- Jangkar #tulis ada di pembungkus beranda supaya dialog tulis tidak bentrok id. --}}
{{-- Event buka-tulis memfokuskan kursor ke textarea. --}}
<div x-data x-on:buka-tulis.window="$refs.tulis.focus()" class="border-b border-zinc-200 px-4 pt-3 pb-2 dark:border-[#2f3336]">
    {{-- Formulir penulisan kiriman. --}}
    <form wire:submit="kirim" class="flex gap-3">
        {{-- Avatar bulat pengguna aktif. --}}
        <x-avatar :user="auth()->user()" size="md" class="mt-1 shrink-0" />

        {{-- Kolom utama penulisan. --}}
        <div class="min-w-0 flex-1">
            @if ($merantai)
                {{-- Keterangan merangkai utas lanjutan tweet. --}}
                <div class="mb-2 flex items-center gap-2 text-xs font-semibold text-[#1d9bf0]">
                    {{-- Ikon utas tweet. --}}
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4 fill-current">
                        {{-- Jalur ikon rantai. --}}
                        <path d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88z"></path>
                    </svg>
                    {{-- Status merantai utas. --}}
                    <span>Melanjutkan thread</span>
                    {{-- Tombol selesai merantai. --}}
                    <button type="button" wire:click="berhentiMerantai" class="text-zinc-500 underline hover:text-zinc-700 dark:hover:text-zinc-300">Selesai</button>
                </div>
            @endif

            {{-- Textarea transparan tanpa garis tepi dengan ukuran 20px khas X. --}}
            {{-- autofocus hanya dipakai dialog tulis, bukan kotak tulis beranda. --}}
            <textarea wire:model.live="body" x-ref="tulis" @if ($fokusOtomatis) autofocus @endif x-init="if (location.hash === '#tulis') $el.focus()" rows="2" aria-label="Isi post" placeholder="{{ $tipe === 'quote' ? 'Tambahkan komentar' : ($tipe === 'reply' ? 'Posting balasan Anda' : 'Apa yang sedang hangat?!') }}" class="min-h-[56px] w-full resize-none border-none bg-transparent p-0 text-xl leading-normal text-[#0f1419] placeholder:text-zinc-500 focus:ring-0 dark:text-[#e7e9ea]"></textarea>

            @if ($parent && ! $merantai)
                @if ($tipe === 'quote')
                    {{-- Pratinjau post yang dikutip di dalam kotak tulis, seperti X. --}}
                    <div class="mt-3 rounded-2xl border border-zinc-200 p-3 dark:border-[#2f3336]">
                        {{-- Identitas pemilik post yang dikutip. --}}
                        <div class="flex items-center gap-2 text-sm">
                            {{-- Avatar pemilik post yang dikutip. --}}
                            <x-avatar :user="$parent->user" size="sm" />
                            {{-- Nama pemilik post yang dikutip. --}}
                            <span class="font-bold text-[#0f1419] dark:text-[#e7e9ea]">{{ $parent->user->name }}</span>
                            {{-- Username pemilik post yang dikutip. --}}
                            <span class="text-zinc-500">&#64;{{ $parent->user->username }}</span>
                        </div>
                        {{-- Cuplikan isi post yang dikutip. --}}
                        <p class="mt-1 line-clamp-3 text-sm text-[#0f1419] dark:text-[#e7e9ea]">{{ $parent->body }}</p>
                    </div>
                @else
                    {{-- Baris tujuan balasan di bawah teks, persis tata letak X. --}}
                    <p class="mt-1 text-[15px] text-zinc-500">Membalas <span class="text-[#1d9bf0]">&#64;{{ $parent->user->username }}</span></p>
                @endif
            @endif

            @error('body')
                {{-- Pesan galat validasi bila teks melanggar aturan. --}}
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            @error('gambar')
                {{-- Pesan galat bila gambar yang diunggah tidak valid. --}}
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            @error('berkas')
                {{-- Pesan galat bila berkas dokumen yang diunggah tidak valid. --}}
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            @error('media')
                {{-- Pesan galat umum bila lampiran ditolak. --}}
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            @if ($gambar !== [])
                {{-- Pratinjau berkas gambar dalam kisi dua kolom. --}}
                <div class="mt-3 grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-zinc-200 dark:border-[#2f3336]">
                    @foreach ($gambar as $foto)
                        {{-- Bingkai pratinjau gambar terpilih. --}}
                        <div class="relative" wire:key="pratinjau-gambar-{{ $loop->index }}">
                            {{-- Pratinjau gambar sementara. --}}
                            <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau gambar" class="h-44 w-full object-cover" />
                            {{-- Tombol untuk membatalkan unggahan gambar. --}}
                            <button type="button" wire:click="hapusGambar({{ $loop->index }})" class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full bg-black/70 text-white transition-colors hover:bg-black/85" aria-label="Hapus gambar">
                                {{-- Ikon silang penutup. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4 fill-current">
                                    {{-- Jalur silang. --}}
                                    <path d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($berkas !== [])
                {{-- Daftar pratinjau berkas dokumen terpisah dari gambar. --}}
                <div class="mt-3 space-y-2">
                    @foreach ($berkas as $dokumen)
                        {{-- Kartu pratinjau dokumen dengan batas tepi melengkung. --}}
                        <div class="relative flex items-center gap-3 rounded-2xl border border-zinc-200 px-3 py-3 dark:border-[#2f3336]" wire:key="pratinjau-berkas-{{ $loop->index }}">
                            {{-- Ikon dokumen resmi. --}}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 shrink-0 fill-current text-[#1d9bf0]">
                                {{-- Jalur ikon dokumen. --}}
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"></path>
                            </svg>
                            {{-- Nama berkas asli dokumen. --}}
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-[#0f1419] dark:text-[#e7e9ea]">{{ $dokumen->getClientOriginalName() }}</span>
                            {{-- Tombol membatalkan unggahan dokumen. --}}
                            <button type="button" wire:click="hapusBerkas({{ $loop->index }})" class="flex size-7 shrink-0 items-center justify-center rounded-full bg-black/70 text-white transition-colors hover:bg-black/85" aria-label="Hapus berkas">
                                {{-- Ikon silang penutup. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4 fill-current">
                                    {{-- Jalur silang. --}}
                                    <path d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($media !== [])
                {{-- Pratinjau kompatibilitas untuk media yang disetel langsung. --}}
                <div class="mt-3 grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border border-zinc-200 dark:border-[#2f3336]">
                    @foreach ($media as $item)
                        @if ($item->isPreviewable())
                            {{-- Bingkai gambar pratinjau kompatibilitas. --}}
                            <div class="relative" wire:key="pratinjau-media-{{ $loop->index }}">
                                {{-- Gambar sementara. --}}
                                <img src="{{ $item->temporaryUrl() }}" alt="Pratinjau gambar" class="h-44 w-full object-cover" />
                                {{-- Tombol membuang gambar kompatibilitas. --}}
                                <button type="button" wire:click="hapusMedia({{ $loop->index }})" class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full bg-black/70 text-white transition-colors hover:bg-black/85" aria-label="Hapus media">
                                    {{-- Ikon silang penutup. --}}
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4 fill-current">
                                        {{-- Jalur silang. --}}
                                        <path d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"></path>
                                    </svg>
                                </button>
                            </div>
                        @else
                            {{-- Dokumen pratinjau kompatibilitas. --}}
                            <div class="relative col-span-2 flex items-center gap-3 px-3 py-4" wire:key="pratinjau-media-{{ $loop->index }}">
                                {{-- Ikon dokumen lampiran. --}}
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 shrink-0 fill-current text-[#1d9bf0]">
                                    {{-- Jalur ikon dokumen. --}}
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"></path>
                                </svg>
                                {{-- Nama berkas kompatibilitas. --}}
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-[#0f1419] dark:text-[#e7e9ea]">{{ $item->getClientOriginalName() }}</span>
                                {{-- Tombol membuang berkas kompatibilitas. --}}
                                <button type="button" wire:click="hapusMedia({{ $loop->index }})" class="flex size-7 shrink-0 items-center justify-center rounded-full bg-black/70 text-white transition-colors hover:bg-black/85" aria-label="Hapus media">
                                    {{-- Ikon silang penutup. --}}
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="size-4 fill-current">
                                        {{-- Jalur silang. --}}
                                        <path d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Bilah pembatas dan deretan ikon bawah. --}}
            <div class="mt-3 border-t border-zinc-100 pt-2.5 dark:border-[#2f3336]/60">
                {{-- Penampung flex tombol media dan tombol posting. --}}
                <div class="flex items-center justify-between">
                    {{-- Deretan tombol aksi lampiran terpisah: gambar dan berkas. --}}
                    <div class="flex items-center gap-1">
                        {{-- Tombol unggah gambar khusus foto dan ilustrasi. --}}
                        <label class="flex size-9 cursor-pointer items-center justify-center rounded-full text-[#1d9bf0] transition-colors hover:bg-[#1d9bf0]/10" title="Foto / Gambar">
                            {{-- Input khusus gambar yang tersembunyi. --}}
                            <input wire:model="gambar" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only" />
                            {{-- Ikon gambar resmi X. --}}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                                {{-- Jalur ikon galeri foto. --}}
                                <path d="M3 5.5C3 4.119 4.119 3 5.5 3h13C19.881 3 21 4.119 21 5.5v13c0 1.381-1.119 2.5-2.5 2.5h-13C4.119 21 3 19.881 3 18.5v-13zM5.5 5c-.276 0-.5.224-.5.5v9.086l3-3 3 3 5-5 3 3V5.5c0-.276-.224-.5-.5-.5h-13zM19 15.414l-3-3-5 5-3-3-3 3V18.5c0 .276.224.5.5.5h13c.276 0 .5-.224.5-.5v-3.086zM9.75 8.75c0 .69-.56 1.25-1.25 1.25s-1.25-.56-1.25-1.25.56-1.25 1.25-1.25 1.25.56 1.25 1.25z"></path>
                            </svg>
                        </label>

                        {{-- Tombol unggah berkas dokumen dan arsip terpisah. --}}
                        <label class="flex size-9 cursor-pointer items-center justify-center rounded-full text-[#1d9bf0] transition-colors hover:bg-[#1d9bf0]/10" title="Berkas / Dokumen">
                            {{-- Input khusus dokumen yang tersembunyi. --}}
                            <input wire:model="berkas" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip" class="sr-only" />
                            {{-- Ikon dokumen berkas. --}}
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-current">
                                {{-- Jalur ikon berkas dokumen. --}}
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"></path>
                            </svg>
                        </label>
                    </div>

                    {{-- Bagian kanan: penghitung grapheme dan tombol posting. --}}
                    <div class="flex items-center gap-3">
                        @if ($body !== '')
                            {{-- Penghitung karakter: X menampilkannya hanya saat ada teks. --}}
                            <span class="text-[13px] {{ grapheme_strlen($body) > 250 ? 'font-bold text-red-500' : (grapheme_strlen($body) > 230 ? 'text-[#ffd400]' : 'text-zinc-500') }}">{{ grapheme_strlen($body) }}/250</span>
                        @endif

                        {{-- Tombol Posting biru khas X. --}}
                        <flux:button type="submit" variant="primary" size="sm" :disabled="trim($body) === '' && $media === [] && $gambar === [] && $berkas === []" class="rounded-full !bg-[#1d9bf0] px-4 py-1.5 text-[15px] font-bold text-white shadow-none transition-colors hover:!bg-[#1a8cd8] disabled:opacity-50">{{ $tipe === 'quote' ? 'Kutip' : 'Posting' }}</flux:button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
