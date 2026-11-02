{{-- Isi teks post dengan penyorotan tagar dan sebutan otomatis khas X. --}}
@props(['body'])

@php
    // Teks di-escape lebih dulu supaya karakter HTML dari pengguna tidak pernah memicu celah XSS.
    $aman = e($body);

    // Setiap hashtag diganti menjadi tautan biru khas X ke rute halaman tag terkait.
    $bertautan = preg_replace_callback(
        '/(?<![\w#])#([\p{L}\p{N}_]{1,50})/u',
        fn (array $cocok) => '<a href="'.route('tag.show', ['tag' => mb_strtolower($cocok[1])]).'" class="text-sky-500 hover:underline">#'.$cocok[1].'</a>',
        $aman,
    );

    // Setiap sebutan @username menjadi tautan biru ke profil pemiliknya, sama seperti X.
    $bertautan = preg_replace_callback(
        // Alamat surel tidak ikut tertaut karena karakter sebelum @ adalah huruf atau angka.
        '/(?<![\w@\/])@([A-Za-z0-9_]{1,30})/u',
        fn (array $cocok) => '<a href="'.route('profile.show', ['user' => mb_strtolower($cocok[1])]).'" class="text-sky-500 hover:underline">@'.$cocok[1].'</a>',
        $bertautan,
    );
@endphp

{{-- Paragraf penampung teks dengan dukungan jeda baris alami. --}}
<p {{ $attributes->merge(['class' => 'mt-1 whitespace-pre-line break-words']) }}>{!! $bertautan !!}</p>
