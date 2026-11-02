{{-- Halaman 404: alamat yang dibuka tidak ada. --}}
{{-- Kerangka halaman tamu dipakai karena layout aplikasi butuh pengguna yang login. --}}
<x-layouts.auth>
    {{-- Keterangan kode galat beserta tautan kembali ke beranda. --}}
    <x-error-page kode="404" judul="Halaman tidak ditemukan" pesan="Alamat yang Anda buka tidak ada atau sudah dipindahkan." />
</x-layouts.auth>
