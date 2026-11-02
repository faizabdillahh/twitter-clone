<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Pengaturan profil: identitas publik, avatar, dan banner.
 *
 * Berkas unggahan disimpan di disk `public` sehingga bisa disajikan lewat
 * symlink storage tanpa route tambahan.
 */
#[Layout('components.layouts.app')]
class Profile extends Component
{
    // Menyediakan properti unggahan beserta pembersihan berkas sementara.
    use WithFileUploads;

    public string $name = ''; // Nama tampilan.

    public string $username = ''; // Alamat profil, 3-20 karakter.

    public string $email = ''; // Surel untuk login.

    public string $bio = ''; // Deskripsi singkat, maksimal 160 karakter.

    public string $location = ''; // Kota atau wilayah.

    public string $website = ''; // Tautan situs pribadi.

    public ?TemporaryUploadedFile $avatar = null; // Avatar baru yang belum disimpan.

    public ?TemporaryUploadedFile $banner = null; // Banner baru yang belum disimpan.

    /**
     * Menyalin data pengguna ke properti formulir saat halaman dibuka.
     */
    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Nilai awal formulir mengikuti data yang tersimpan di database.
        $this->fill([
            'name' => $user->name,
            'username' => $user->username ?? '',
            'email' => $user->email,
            'bio' => $user->bio ?? '',
            'location' => $user->location ?? '',
            'website' => $user->website ?? '',
        ]);
    }

    /**
     * Memvalidasi lalu menyimpan perubahan profil beserta berkas unggahan.
     */
    public function updateProfile(): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Username dinormalkan sebelum validasi supaya aturan unik memeriksa
        // bentuk yang benar-benar akan disimpan.
        $this->username = Str::lower($this->username);

        // Situs tanpa skema dilengkapi https:// agar lolos aturan url.
        $this->website = $this->situsLengkap($this->website);

        // Kolom yang boleh berubah dibatasi di sini, bukan lewat mass assignment.
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-z0-9_]+$/', Rule::unique(User::class)->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:60'],
            'website' => ['nullable', 'url', 'max:120'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        // Berkas unggahan bukan kolom tabel, jadi dikeluarkan dari data.
        unset($data['avatar'], $data['banner']);

        // Avatar baru menggantikan yang lama, bukan menumpuk di disk.
        if ($this->avatar) {
            // Path hasil store() relatif terhadap disk public.
            $data['avatar_path'] = $this->avatar->store('avatars', 'public');

            // Berkas lama dihapus setelah berkas baru berhasil ditulis.
            // array_filter membuang null karena delete() menolak path kosong.
            Storage::disk('public')->delete(array_filter([$user->avatar_path]));

            // Kosongkan properti supaya pratinjau kembali memakai data tersimpan.
            $this->avatar = null;
        }

        // Banner memakai alur yang sama dengan avatar.
        if ($this->banner) {
            // Path relatif terhadap disk public, sama seperti avatar.
            $data['banner_path'] = $this->banner->store('banners', 'public');

            // Banner lama tidak lagi dipakai setelah yang baru tersimpan.
            Storage::disk('public')->delete(array_filter([$user->banner_path]));

            // Pratinjau kembali membaca dari database.
            $this->banner = null;
        }

        // Isian kosong disimpan sebagai null agar kolom nullable tetap bersih.
        foreach (['bio', 'location', 'website'] as $kolom) {
            $data[$kolom] = $data[$kolom] === '' ? null : $data[$kolom];
        }

        // Terapkan data yang sudah divalidasi ke model.
        $user->fill($data);

        // Surel berubah berarti verifikasi lama tidak lagi berlaku.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Simpan ke database; kegagalan query dilempar sebagai exception.
        $user->save();

        // Beri tahu tampilan bahwa penyimpanan selesai.
        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Menambahkan https:// bila alamat situs diketik tanpa skema.
     */
    private function situsLengkap(string $website): string
    {
        // Kosong tetap kosong supaya kolom nullable tidak terisi teks palsu.
        if ($website === '') {
            return '';
        }

        // Alamat yang sudah punya skema dibiarkan apa adanya.
        return Str::startsWith($website, ['http://', 'https://']) ? $website : 'https://'.$website;
    }
}
