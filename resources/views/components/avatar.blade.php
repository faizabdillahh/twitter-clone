{{-- Avatar: foto unggahan bila ada, inisial nama bila belum. --}}
@props(['user', 'size' => 'md'])

@php
    // Ukuran disimpan di satu tempat agar seluruh halaman memakai skala yang sama.
    $ukuran = match ($size) {
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-16 text-xl',
    };
@endphp

{{-- Lingkaran pembungkus: ukuran dan warna latar ditentukan di sini. --}}
<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-200 font-medium text-zinc-600 {$ukuran} dark:bg-zinc-700 dark:text-zinc-200"]) }}>
    @if ($user->avatarUrl())
        {{-- Foto hasil unggahan pengguna. --}}
        <img src="{{ $user->avatarUrl() }}" alt="Foto profil {{ $user->name }}" class="size-full object-cover" />
    @else
        {{-- Tanpa foto, inisial nama jadi penanda sementara. --}}
        {{ $user->initials() }}
    @endif
</span>
