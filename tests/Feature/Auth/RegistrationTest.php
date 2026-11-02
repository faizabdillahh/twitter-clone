<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        // Halaman pendaftaran bisa dibuka tanpa login.
        $response = $this->get('/register');

        // Setiap isian terikat ke propertinya sendiri lewat komponen x-auth-field.
        $response->assertStatus(200)
            ->assertSee('Buat akun Anda')
            ->assertSee('wire:model="username"', false)
            ->assertSee('wire:model="password_confirmation"', false);
    }

    public function test_new_users_can_register(): void
    {
        // Kirim formulir dengan username berhuruf besar untuk menguji normalisasi.
        $response = Volt::test('auth.register')
            ->set('name', 'Test User')
            ->set('username', 'Test_User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        // Pengguna langsung terautentikasi setelah mendaftar.
        $this->assertAuthenticated();

        // Username disimpan dalam bentuk huruf kecil.
        $this->assertSame('test_user', User::first()->username);
    }

    public function test_username_must_be_unique(): void
    {
        // Username milik orang lain tidak boleh dipakai lagi.
        User::factory()->create(['username' => 'sudah_dipakai']);

        $response = Volt::test('auth.register')
            ->set('name', 'Test User')
            ->set('username', 'Sudah_Dipakai')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Pemeriksaan unik memakai bentuk huruf kecil yang akan disimpan.
        $response->assertHasErrors('username');
    }

    public function test_username_rejects_characters_outside_the_allowed_set(): void
    {
        // Spasi dan tanda baca tidak boleh masuk username karena dipakai di URL.
        $response = Volt::test('auth.register')
            ->set('name', 'Test User')
            ->set('username', 'test user!')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response->assertHasErrors('username');
    }
}
