<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Komponen rekomendasi akun untuk diikuti di bilah samping kanan.
 *
 * Menampilkan hingga tiga akun selain pengguna yang sedang aktif,
 * memprioritaskan akun yang belum diikuti.
 */
class WhoToFollow extends Component
{
    /**
     * Memperbarui daftar rekomendasi saat hubungan pertemanan berubah.
     */
    #[On('follow-changed')]
    public function perbarui(): void
    {
        // Re-render otomatis dipicu oleh Livewire saat method ini dipanggil.
    }

    /**
     * Mengambil rekomendasi akun dan merender tampilan bilah samping.
     */
    public function render(): View
    {
        // Pengguna aktif yang sedang masuk aplikasi.
        $saya = Auth::user();

        // Daftar id pengguna yang sudah diikuti oleh pengguna aktif.
        $idMengikuti = $saya ? $saya->following()->pluck('following_id')->all() : [];

        // Prioritaskan akun yang belum diikuti dan bukan akun sendiri.
        $rekomendasi = User::query()
            ->when($saya, fn ($query) => $query->where('id', '!=', $saya->id))
            ->whereNotIn('id', $idMengikuti)
            ->inRandomOrder()
            ->take(3)
            ->get();

        // Bila akun yang belum diikuti kurang dari tiga, lengkapi dengan pengguna lain selain diri sendiri.
        if ($rekomendasi->count() < 3) {
            // Ambil akun tambahan selain yang sudah masuk rekomendasi dan bukan akun sendiri.
            $tambahan = User::query()
                ->when($saya, fn ($query) => $query->where('id', '!=', $saya->id))
                ->whereNotIn('id', $rekomendasi->pluck('id'))
                ->take(3 - $rekomendasi->count())
                ->get();

            // Gabungkan kedua kelompok akun.
            $rekomendasi = $rekomendasi->merge($tambahan);
        }

        // Tampilkan daftar ke view blade komponen.
        return view('livewire.who-to-follow', [
            'users' => $rekomendasi,
        ]);
    }
}
