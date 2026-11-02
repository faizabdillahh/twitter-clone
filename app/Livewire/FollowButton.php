<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Tombol ikuti di halaman profil.
 *
 * Satu tombol, dua keadaan: mengikuti atau belum. Klik berikutnya berhenti
 * mengikuti, jadi tombol ini juga sekaligus tombol batal ikuti.
 */
class FollowButton extends Component
{
    public User $user; // Pemilik profil yang sedang dibuka.

    /**
     * Menyimpan pemilik profil dari halaman yang memanggil.
     */
    public function mount(User $user): void
    {
        // Model dipakai apa adanya; halaman sudah memuatnya lewat route binding.
        $this->user = $user;
    }

    /**
     * Mengikuti atau berhenti mengikuti pemilik profil.
     */
    public function ikuti(): void
    {
        /** @var User $saya */
        $saya = Auth::user();

        // Akun sendiri tidak bisa diikuti.
        if ($saya->id === $this->user->id) {
            return;
        }

        // toggle() memasang relasi bila belum ada dan melepasnya bila sudah ada.
        $saya->following()->toggle($this->user->id);

        // Halaman profil memperbarui jumlah pengikut lewat event ini.
        $this->dispatch('follow-changed');
    }

    /**
     * Menampilkan tombol sesuai keadaan hubungan saat ini.
     */
    public function render(): View
    {
        // Keadaan dibaca ulang setiap render agar label selalu benar setelah klik.
        $mengikuti = Auth::user()->following()->where('users.id', $this->user->id)->exists();

        // Kirim keadaan hubungan ke view tombol.
        return view('livewire.follow-button', ['mengikuti' => $mengikuti]);
    }
}
