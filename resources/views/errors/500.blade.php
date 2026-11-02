{{-- Halaman 500: kesalahan tak terduga di sisi server. --}}
{{-- Kerangka halaman tamu dipakai karena layout aplikasi butuh pengguna yang login. --}}
<x-layouts.auth>
    {{-- Keterangan kode galat beserta tautan kembali ke beranda. --}}
    <x-error-page kode="500" judul="Terjadi kesalahan di server" pesan="Ada masalah di sisi kami. Coba muat ulang beberapa saat lagi." />
</x-layouts.auth>
