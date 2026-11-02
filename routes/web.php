<?php

use App\Livewire\Activities;
use App\Livewire\EditPost;
use App\Livewire\ProfilePage;
use App\Livewire\SearchPosts;
use App\Livewire\Settings\Profile;
use App\Livewire\Thread;
use App\Livewire\Timeline;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Beranda dan detail post hanya bisa dibuka setelah login.
Route::middleware(['auth'])->group(function () {
    Route::get('/', Timeline::class)->name('home');
    Route::get('posts/{post}', Thread::class)->name('posts.show');
    Route::get('posts/{post}/edit', EditPost::class)->name('posts.edit');

    // Pencarian bebas dan halaman satu tag memakai komponen yang sama.
    Route::get('cari', SearchPosts::class)->name('search');
    Route::get('tag/{tag}', SearchPosts::class)->name('tag.show');

    // Aktivitas orang lain terhadap post dan akun pengguna ini.
    Route::get('aktivitas', Activities::class)->name('activities');

    // Nama route lama dipertahankan agar tautan bawaan starter kit tetap hidup.
    Route::redirect('dashboard', '/')->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

// Profil publik didaftarkan paling akhir supaya tidak menelan /login atau /register.
// Nama parameter harus `user` agar cocok dengan mount(User $user) di komponen.
Route::middleware(['auth'])->get('/{user}', ProfilePage::class)->name('profile.show');
