{{-- Halaman 403: akun tidak punya izin membuka halaman ini. --}}
{{-- Kerangka halaman tamu dipakai karena layout aplikasi butuh pengguna yang login. --}}
<x-layouts.auth>
    {{-- Keterangan kode galat beserta tautan kembali ke beranda. --}}
    <x-error-page kode="403" judul="Akses ditolak" pesan="Akun Anda tidak punya izin untuk membuka halaman ini." />
</x-layouts.auth>
