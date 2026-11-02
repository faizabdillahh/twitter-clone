<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        // Route lama tetap butuh login seperti beranda.
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_are_sent_to_the_timeline(): void
    {
        $this->actingAs(User::factory()->create());

        // Nama route dashboard dipertahankan sebagai pengalihan, bukan halaman.
        $this->get('/dashboard')->assertRedirect('/');
    }
}
