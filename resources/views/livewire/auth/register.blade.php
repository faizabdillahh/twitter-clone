<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Formulir pendaftaran: satu langkah, langsung masuk setelah akun dibuat.
new #[Layout('components.layouts.auth')] class extends Component {
    // Nama tampilan pengguna.
    public string $name = '';
    // Alamat profil, 3-20 karakter huruf kecil.
    public string $username = '';
    // Surel untuk login.
    public string $email = '';
    // Sandi asli sebelum di-hash.
    public string $password = '';
    // Konfirmasi sandi, dicocokkan oleh aturan confirmed.
    public string $password_confirmation = '';

    /**
     * Memvalidasi pendaftaran, membuat akun, lalu masuk otomatis.
     */
    public function register(): void
    {
        // Username dinormalkan sebelum validasi supaya aturan unik memeriksa
        // bentuk yang benar-benar akan disimpan.
        $this->username = Str::lower($this->username);

        // Data yang lolos validasi sekaligus menjadi isi baris users baru.
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-z0-9_]+$/', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Sandi disimpan sebagai hash, tidak pernah sebagai teks asli.
        $validated['password'] = Hash::make($validated['password']);

        // Event Registered memicu verifikasi surel bila fitur itu dinyalakan.
        event(new Registered(($user = User::create($validated))));

        // Pengguna langsung masuk tanpa mengisi formulir login lagi.
        Auth::login($user);

        // Arahkan ke beranda yang butuh login.
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

{{-- Seluruh isi formulir berada dalam satu kolom. --}}
<div class="flex flex-col gap-8">
    {{-- Judul dan pengantar singkat. --}}
    <div class="flex flex-col gap-1.5">
        {{-- Judul halaman. --}}
        <h1 class="text-[26px] font-extrabold tracking-tight text-[#0f1419] dark:text-[#e7e9ea]">Buat akun Anda</h1>
        {{-- Satu kalimat pengantar. --}}
        <p class="text-[15px] text-zinc-500">Isi data di bawah ini, akun langsung aktif setelah dikirim.</p>
    </div>

    {{-- Menampilkan pesan status dari sesi, misalnya tautan verifikasi terkirim. --}}
    <x-auth-session-status class="text-sm text-emerald-600 dark:text-emerald-400" :status="session('status')" />

    {{-- Satu formulir, satu metode simpan di komponen. --}}
    <form wire:submit="register" class="flex flex-col gap-5">
        {{-- Nama tampilan, boleh mengandung spasi. --}}
        <flux:input wire:model="name" id="name" label="Nama" type="text" name="name" required autofocus autocomplete="name" placeholder="Nama lengkap" />

        {{-- Username dipakai di URL profil, jadi hanya huruf kecil, angka, dan garis bawah. --}}
        <flux:input wire:model="username" id="username" label="Username" type="text" name="username" required autocomplete="username" placeholder="nama_pengguna" description="3-20 karakter: huruf kecil, angka, dan garis bawah." />

        {{-- Surel dipakai untuk login dan pemulihan akun. --}}
        <flux:input wire:model="email" id="email" label="Email" type="email" name="email" required autocomplete="email" placeholder="nama@email.com" />

        {{-- Sandi dikirim ke server lalu di-hash sebelum disimpan. --}}
        <flux:input wire:model="password" id="password" label="Sandi" type="password" name="password" required autocomplete="new-password" placeholder="Sandi" />

        {{-- Konfirmasi dicocokkan oleh aturan confirmed di server. --}}
        <flux:input wire:model="password_confirmation" id="password_confirmation" label="Ulangi sandi" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi sandi" />

        {{-- Tombol kirim selebar kolom. --}}
        <flux:button variant="primary" type="submit" class="w-full">Buat akun</flux:button>
    </form>

    {{-- Jalan keluar bagi pengguna yang ternyata sudah punya akun. --}}
    <p class="text-center text-sm text-zinc-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-[#1d9bf0] hover:underline">Masuk</a></p>
</div>
