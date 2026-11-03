<?php

namespace App\Livewire;

use App\Models\Hashtag;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Widget tren di bilah kanan: empat hashtag dengan jumlah post terbanyak.
 */
class Trends extends Component
{
    /**
     * Mengambil tag paling sering dipakai beserta jumlah postnya.
     */
    public function render(): View
    {
        // withCount menambahkan kolom posts_count, dan whereHas membuang tag tanpa post.
        $tren = Hashtag::query()
            ->withCount('posts')
            ->whereHas('posts')
            ->orderByDesc('posts_count')
            ->orderBy('tag')
            ->limit(4)
            ->get();

        // Daftar tag siap dipakai view tren.
        return view('livewire.trends', ['tren' => $tren]);
    }
}
