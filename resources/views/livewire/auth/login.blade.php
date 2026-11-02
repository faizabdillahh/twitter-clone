<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Halaman masuk: satu isian surel, satu isian sandi, lalu tombol Masuk.
new #[Layout('components.layouts.auth')] class extends Component {
    // Surel yang dipakai untuk masuk.
    public string $email = '';

    // Sandi asli yang dicocokkan dengan hash di database.
    public string $password = '';

    // Menyimpan sesi setelah peramban ditutup.
    public bool $remember = false;

    /**
     * Memeriksa kredensial lalu memindahkan pengguna ke halaman tujuan.
     */
    public function login(): void
    {
        // Aturan dijalankan sebelum apa pun dikirim ke database.
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Percobaan berulang dari alamat yang sama ditahan lebih dulu.
        $this->ensureIsNotRateLimited();

        // Kredensial yang salah tidak menyebut bagian mana yang keliru.
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Hitungan gagal dipakai pembatas laju di atas.
            RateLimiter::hit($this->throttleKey());

            // Pesan sama untuk surel yang tidak ada maupun sandi yang salah.
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Hitungan dihapus setelah berhasil supaya percobaan berikutnya penuh lagi.
        RateLimiter::clear($this->throttleKey());
        // Id sesi diganti untuk mencegah pembajakan sesi.
        Session::regenerate();

        // Halaman tujuan disimpan middleware auth sebelum pengguna dialihkan ke sini.
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Menahan percobaan masuk yang datang terlalu sering.
     */
    protected function ensureIsNotRateLimited(): void
    {
        // Lima percobaan per menit dihitung per surel dan alamat IP.
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            // Percobaan yang masih wajar diteruskan.
            return;
        }

        // Event ini dipakai pencatat keamanan dan pendengar lain.
        event(new Lockout(request()));

        // Sisa waktu tunggu dipakai pesan galat.
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Pengguna diberi tahu berapa lama harus menunggu.
        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Kunci pembatas laju: surel dan alamat IP pengirim.
     */
    protected function throttleKey(): string
    {
        // Transliterasi menjaga kunci tetap stabil untuk surel berhuruf non-latin.
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

{{-- Kolom formulir dengan jarak antarbagian. --}}
<div class="flex flex-col gap-8">
    {{-- Judul dan pengantar singkat. --}}
    <div class="flex flex-col gap-1.5">
        {{-- Judul halaman. --}}
        <h1 class="text-[26px] font-extrabold tracking-tight text-[#0f1419] dark:text-[#e7e9ea]">Masuk ke tweetUSK</h1>
        {{-- Satu kalimat pengantar. --}}
        <p class="text-[15px] text-zinc-500">Masukkan email dan sandi akun Anda untuk melanjutkan.</p>
    </div>

    {{-- Pesan status dari sesi, misalnya tautan atur ulang sandi terkirim. --}}
    <x-auth-session-status class="text-sm text-emerald-600 dark:text-emerald-400" :status="session('status')" />

    {{-- Formulir masuk. --}}
    <form wire:submit="login" class="flex flex-col gap-5">
        {{-- Isian surel. --}}
        <flux:input wire:model="email" label="Email" type="email" name="email" required autofocus autocomplete="email" placeholder="nama@email.com" />

        {{-- Isian sandi. --}}
        <flux:input wire:model="password" label="Sandi" type="password" name="password" required autocomplete="current-password" placeholder="Sandi" />

        {{-- Ingat saya dan tautan lupa sandi berbagi satu baris. --}}
        <div class="-mt-1 flex items-center justify-between gap-3">
            {{-- Pilihan menyimpan sesi setelah peramban ditutup. --}}
            <flux:checkbox wire:model="remember" label="Ingat saya" />
            @if (Route::has('password.request'))
                {{-- Jalan keluar bila sandi terlupa. --}}
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#1d9bf0] hover:underline">Lupa sandi?</a>
            @endif
        </div>

        {{-- Tombol kirim selebar kolom. --}}
        <flux:button variant="primary" type="submit" class="w-full">Masuk</flux:button>
    </form>

    {{-- Jalan keluar bagi pengguna yang belum punya akun. --}}
    <p class="text-center text-sm text-zinc-500">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-[#1d9bf0] hover:underline">Daftar</a></p>
</div>
