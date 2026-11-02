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
<div class="flex flex-col gap-6">
    {{-- Judul dan penjelasan singkat di atas formulir. --}}
    <x-auth-header title="Buat akun" description="Isi data di bawah untuk membuat akun tweetUSK" />

    {{-- Menampilkan pesan status dari sesi, misalnya tautan verifikasi terkirim. --}}
    <x-auth-session-status class="text-center" :status="session('status')" />

    {{-- Satu formulir, satu metode simpan di komponen. --}}
    <form wire:submit="register" class="flex flex-col gap-6">
        {{-- Kelompok isian nama. --}}
        <div class="grid gap-2">
            {{-- Nama tampilan, boleh mengandung spasi. --}}
            <flux:input wire:model="name" id="name" label="Nama" type="text" name="name" required autofocus autocomplete="name" placeholder="Nama lengkap" />
        </div>

        {{-- Kelompok isian username. --}}
        <div class="grid gap-2">
            {{-- Username dipakai di URL profil, jadi dibatasi dan hanya huruf kecil. --}}
            <flux:input wire:model="username" id="username" label="Username" type="text" name="username" required autocomplete="username" placeholder="nama_pengguna" description="3-20 karakter: huruf kecil, angka, dan garis bawah." />
        </div>

        {{-- Kelompok isian surel. --}}
        <div class="grid gap-2">
            {{-- Surel dipakai untuk login dan pemulihan akun. --}}
            <flux:input wire:model="email" id="email" label="Email" type="email" name="email" required autocomplete="email" placeholder="email@contoh.com" />
        </div>

        {{-- Kelompok isian sandi. --}}
        <div class="grid gap-2">
            {{-- Sandi dikirim ke server lalu di-hash sebelum disimpan. --}}
            <flux:input wire:model="password" id="password" label="Sandi" type="password" name="password" required autocomplete="new-password" placeholder="Sandi" />
        </div>

        {{-- Kelompok konfirmasi sandi. --}}
        <div class="grid gap-2">
            {{-- Konfirmasi dicocokkan oleh aturan confirmed, bukan oleh skrip klien. --}}
            <flux:input wire:model="password_confirmation" id="password_confirmation" label="Ulangi sandi" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi sandi" />
        </div>

        {{-- Tombol kirim selebar formulir. --}}
        <div class="flex items-center justify-end">
            {{-- Satu-satunya tombol kirim di formulir. --}}
            <flux:button type="submit" variant="primary" class="w-full">Buat akun</flux:button>
        </div>
    </form>

    {{-- Jalan keluar bagi pengguna yang ternyata sudah punya akun. --}}
    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{-- Teks pengantar menuju halaman masuk. --}}
        Sudah punya akun?
        <x-text-link href="{{ route('login') }}">Masuk</x-text-link>
    </div>
</div>
