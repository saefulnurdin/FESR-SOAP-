<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee($user->email);
    }

    public function test_profile_page_is_only_available_to_authenticated_users(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Dokter Ratna',
            'email' => 'ratna@rs-contoh.local',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Dokter Ratna', $user->name);
        $this->assertSame('ratna@rs-contoh.local', $user->email);
    }

    public function test_profile_update_requires_a_unique_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => $user->name,
            'email' => $other->email,
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertSame($user->email, $user->refresh()->email);
    }

    public function test_profile_update_cannot_change_account_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => '1',
            'is_active' => '0',
        ])->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertFalse($user->is_admin);
        $this->assertTrue($user->is_active);
    }
}
