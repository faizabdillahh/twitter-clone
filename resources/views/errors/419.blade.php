{{-- Halaman 419: sesi atau token formulir sudah kedaluwarsa. --}}
{{-- Kerangka halaman tamu dipakai karena layout aplikasi butuh pengguna yang login. --}}
<x-layouts.auth>
    {{-- Keterangan kode galat beserta tautan kembali ke beranda. --}}
    <x-error-page kode="419" judul="Sesi berakhir" pesan="Halaman ini kedaluwarsa karena sesi Anda sudah berakhir. Muat ulang lalu coba lagi." />
</x-layouts.auth>
