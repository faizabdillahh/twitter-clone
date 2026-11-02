{{-- Pembungkus utama komponen rekomendasi akun. --}}
<div>
    @if ($users->isNotEmpty())
        {{-- Wadah utama widget dengan latar abu-abu gelap khas tema X. --}}
        <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50/50 py-3 dark:border-[#2f3336] dark:bg-[#16181c]">
            {{-- Judul widget rekomendasi akun. --}}
            <h2 class="px-4 text-[20px] font-extrabold text-[#0f1419] dark:text-[#e7e9ea]">Siapa untuk diikuti</h2>

            {{-- Daftar akun rekomendasi yang bisa diikuti. --}}
            <div class="mt-2 divide-y divide-zinc-200/50 dark:divide-[#2f3336]/50">
                @foreach ($users as $user)
                    {{-- Baris akun dengan efek sorot abu-abu halus. --}}
                    <div class="flex items-center justify-between px-4 py-3 transition hover:bg-black/5 dark:hover:bg-white/5" wire:key="who-user-{{ $user->id }}">
                        {{-- Tautan profil akun mencakup avatar dan teks identitas. --}}
                        <a href="{{ route('profile.show', $user) }}" wire:navigate class="flex min-w-0 items-center gap-3">
                            {{-- Foto profil ukuran 40px (md). --}}
                            <x-avatar :user="$user" size="md" />

                            {{-- Nama dan username yang otomatis terpotong bila terlalu panjang. --}}
                            <div class="min-w-0 flex-1">
                                {{-- Nama lengkap pengguna dengan efek garis bawah saat disorot. --}}
                                <div class="truncate text-[15px] font-bold text-[#0f1419] hover:underline dark:text-[#e7e9ea]">{{ $user->name }}</div>

                                {{-- Username diawali simbol @ abu-abu. --}}
                                <div class="truncate text-[14px] text-zinc-500">&#64;{{ $user->username }}</div>
                            </div>
                        </a>

                        {{-- Tombol ikuti atau berhenti ikuti reaktif Livewire. --}}
                        <div class="shrink-0 pl-2">
                            {{-- Komponen tombol ikuti akun target. --}}
                            <livewire:follow-button :user="$user" :key="'who-btn-'.$user->id" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
