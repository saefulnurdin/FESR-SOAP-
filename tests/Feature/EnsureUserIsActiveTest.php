<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_keeps_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_inactive_user_is_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $response = $this->actingAs($user->refresh())->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_inactive_admin_loses_access_to_user_management(): void
    {
        $user = User::factory()->admin()->inactive()->create();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertRedirect(route('login'));
    }
}
