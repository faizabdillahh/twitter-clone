{{-- Halaman pengaturan profil: identitas publik, avatar, dan banner. --}}
<section class="w-full">
    {{-- Bingkai sidebar pengaturan yang sama dengan tab lain. --}}
    <x-settings.layout heading="Profil" subheading="Identitas yang tampil di halaman profilmu">
        {{-- Satu formulir untuk semua kolom profil. --}}
        <form wire:submit="updateProfile" class="my-6 w-full space-y-6">
            {{-- Banner: gambar lebar di kepala profil publik. --}}
            <div class="h-32 w-full overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                @if ($banner)
                    {{-- Pratinjau dari berkas yang baru dipilih. --}}
                    <img src="{{ $banner->temporaryUrl() }}" alt="Pratinjau banner" class="size-full object-cover" />
                @elseif (auth()->user()->bannerUrl())
                    {{-- Banner yang sudah tersimpan di disk. --}}
                    <img src="{{ auth()->user()->bannerUrl() }}" alt="Banner profil" class="size-full object-cover" />
                @endif
            </div>

            {{-- Berkas baru baru diunggah saat formulir disimpan. --}}
            <label class="block text-sm font-medium" for="banner">Banner (JPEG, PNG, atau WebP, maksimal 4 MB)</label>
            {{-- Kolom unggah banner; namanya terhubung ke label di atas. --}}
            <input wire:model="banner" id="banner" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-zinc-600 file:me-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 dark:text-zinc-300 dark:file:bg-zinc-700" />

            {{-- Avatar berupa lingkaran kecil di samping nama tampilan. --}}
            <div class="flex items-center gap-4">
                @if ($avatar)
                    {{-- Pratinjau avatar yang baru dipilih. --}}
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Pratinjau avatar" class="size-16 rounded-full object-cover" />
                @else
                    {{-- Avatar tersimpan, atau inisial nama bila belum ada. --}}
                    <x-avatar :user="auth()->user()" size="lg" />
                @endif

                {{-- Sisi kanan: label dan tombol unggah avatar. --}}
                <div class="flex-1">
                    {{-- Batas 2 MB cukup untuk foto persegi. --}}
                    <label class="block text-sm font-medium" for="avatar">Avatar (maksimal 2 MB)</label>
                    {{-- Kolom unggah avatar; namanya terhubung ke label di atas. --}}
                    <input wire:model="avatar" id="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-zinc-600 file:me-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 dark:text-zinc-300 dark:file:bg-zinc-700" />
                </div>
            </div>

            {{-- Nama tampilan, boleh mengandung spasi. --}}
            <flux:input wire:model="name" label="Nama" type="text" name="name" required autofocus autocomplete="name" />

            {{-- Username muncul sebagai @nama di profil publik. --}}
            <flux:input wire:model="username" label="Username" type="text" name="username" required autocomplete="username" description="3-20 karakter: huruf kecil, angka, dan garis bawah." />

            {{-- Surel dipakai untuk login dan pemulihan akun. --}}
            <flux:input wire:model="email" label="Email" type="email" name="email" required autocomplete="email" />

            {{-- Bio singkat yang tampil di bawah nama. --}}
            <flux:textarea wire:model="bio" label="Bio" rows="3" maxlength="160" description="Maksimal 160 karakter." />

            {{-- Lokasi bebas, misalnya kota atau negara. --}}
            <flux:input wire:model="location" label="Lokasi" type="text" name="location" maxlength="60" />

            {{-- Situs pribadi; https:// ditambahkan otomatis bila lupa. --}}
            <flux:input wire:model="website" label="Situs web" type="url" name="website" placeholder="https://contoh.com" maxlength="120" />

            {{-- Tombol simpan dan pesan hasil penyimpanan. --}}
            <div class="flex items-center gap-4">
                {{-- Simpan hanya mengirim satu permintaan untuk semua kolom. --}}
                <flux:button variant="primary" type="submit">Simpan</flux:button>

                {{-- Pesan muncul setelah event profile-updated dikirim komponen. --}}
                <x-action-message class="me-3" on="profile-updated">Tersimpan.</x-action-message>
            </div>
        </form>

        {{-- Hapus akun tetap memakai komponen bawaan agar konfirmasi sandi tidak ditulis ulang. --}}
        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
