<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login_from_the_timeline(): void
    {
        // Beranda hanya untuk pengguna yang login.
        $this->get('/')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_see_the_timeline(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertOk();
    }
}
