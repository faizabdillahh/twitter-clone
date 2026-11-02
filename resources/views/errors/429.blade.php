{{-- Halaman 429: permintaan datang terlalu sering. --}}
{{-- Kerangka halaman tamu dipakai karena layout aplikasi butuh pengguna yang login. --}}
<x-layouts.auth>
    {{-- Keterangan kode galat beserta tautan kembali ke beranda. --}}
    <x-error-page kode="429" judul="Terlalu banyak permintaan" pesan="Anda mengirim permintaan terlalu sering. Tunggu sebentar lalu coba lagi." />
</x-layouts.auth>
