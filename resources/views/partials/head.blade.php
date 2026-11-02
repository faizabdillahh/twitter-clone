{{-- Kepala dokumen yang dipakai semua layout. --}}
{{-- Encoding dan lebar pandang standar. --}}
<meta charset="utf-8" />
{{-- Lebar pandang agar tata letak ponsel tidak diperkecil. --}}
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

{{-- Judul tab mengikuti nama aplikasi bila halaman tidak menentukan sendiri. --}}
<title>{{ $title ?? config('app.name') }}</title>

{{-- Huruf Instrument Sans dimuat dari CDN bunny.net. --}}
<link rel="preconnect" href="https://fonts.bunny.net">
{{-- Berkas huruf yang sama untuk seluruh halaman. --}}
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
@vite('resources/css/app.css')
@fluxAppearance
