{{-- Kerangka halaman: sidebar untuk layar lebar, isi halaman di kolom tengah. --}}
<x-layouts.app.sidebar>
    {{-- Layout sidebar sudah punya tag main sendiri, jadi slot dikirim apa adanya. --}}
    {{ $slot }}
</x-layouts.app.sidebar>
