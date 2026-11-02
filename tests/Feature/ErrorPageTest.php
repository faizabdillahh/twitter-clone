<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_custom_not_found_page(): void
    {
        // Alamat dua segmen tidak ditangani rute mana pun, jadi tamu menerima 404.
        $this->get('/halaman/tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_missing_post_renders_custom_not_found_page(): void
    {
        $this->actingAs(User::factory()->create());

        // Binding model yang tidak menemukan barisnya diteruskan ke halaman 404 kita.
        $this->get(route('posts.show', 999999))
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Kembali ke beranda');
    }
}
