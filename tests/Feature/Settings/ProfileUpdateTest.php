<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        // Halaman pengaturan profil hanya untuk pengguna yang login.
        $this->actingAs(User::factory()->create());

        $this->get('/settings/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        // Mulai dari pengguna dengan data bawaan factory.
        $user = User::factory()->create();

        $this->actingAs($user);

        // Kirim perubahan lewat komponen kelas, bukan halaman Volt.
        Livewire::test(Profile::class)
            ->set('name', 'Test User')
            ->set('username', 'Test_User')
            ->set('bio', 'Menulis tentang Laravel.')
            ->set('email', 'test@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();

        // Nama dan surel baru tersimpan.
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);

        // Username disimpan huruf kecil agar URL profil konsisten.
        $this->assertEquals('test_user', $user->username);

        // Bio ikut tersimpan pada kolom yang sama.
        $this->assertEquals('Menulis tentang Laravel.', $user->bio);

        // Surel berubah, jadi verifikasi lama dibatalkan.
        $this->assertNull($user->email_verified_at);
    }

    public function test_username_must_be_unique(): void
    {
        // Username yang sudah dipakai orang lain tidak boleh diambil.
        User::factory()->create(['username' => 'sudah_dipakai']);

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('username', 'Sudah_Dipakai')
            ->call('updateProfile')
            ->assertHasErrors('username');
    }

    public function test_website_without_scheme_is_completed(): void
    {
        // Alamat tanpa skema dilengkapi https:// agar tetap tautan yang sah.
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('website', 'contoh.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertEquals('https://contoh.com', $user->refresh()->website);
    }

    public function test_avatar_is_stored_on_public_disk(): void
    {
        // Disk palsu mencegah berkas uji menumpuk di storage asli.
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('avatar', UploadedFile::fake()->image('avatar.jpg', 200, 200))
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();

        // Path tersimpan di database dan berkasnya benar-benar ada di disk.
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        // Surel yang tidak diubah tidak boleh membatalkan verifikasi.
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        // Penghapusan akun tetap ditangani komponen Volt bawaan.
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $response
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertFalse(auth()->check());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        // Sandi salah menahan penghapusan akun.
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $response->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }
}
