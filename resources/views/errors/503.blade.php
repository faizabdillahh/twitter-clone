{{-- Halaman 503: aplikasi sedang dalam perawatan. --}}
{{-- Kerangka halaman tamu dipakai karena layout aplikasi butuh pengguna yang login. --}}
<x-layouts.auth>
    {{-- Keterangan kode galat beserta tautan kembali ke beranda. --}}
    <x-error-page kode="503" judul="Sedang dalam perawatan" pesan="Aplikasi sedang diperbarui. Silakan kembali beberapa saat lagi." />
</x-layouts.auth>
