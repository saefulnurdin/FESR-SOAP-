<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_is_displayed_to_administrator(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create(['name' => 'Dokter Budi']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee($admin->name);
        $response->assertSee($staff->name);
    }

    public function test_user_list_is_only_available_to_administrator(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_user_list_can_be_searched(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Dokter Budi']);
        User::factory()->create(['name' => 'Dokter Sari']);

        $response = $this->actingAs($admin)->get('/admin/users?search=Sari');

        $response->assertOk();
        $response->assertSee('Dokter Sari');
        $response->assertDontSee('Dokter Budi');
    }

    public function test_create_user_screen_is_displayed_to_administrator(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/users/create')
            ->assertOk();
    }

    public function test_administrator_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Dokter Sari',
            'email' => 'sari@rs-contoh.local',
            'password' => 'rahasia-kuat',
            'password_confirmation' => 'rahasia-kuat',
            'is_active' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/users');

        $user = User::query()->where('email', 'sari@rs-contoh.local')->sole();

        $this->assertTrue(Hash::check('rahasia-kuat', $user->password));
        $this->assertFalse($user->is_admin);
        $this->assertTrue($user->is_active);
    }

    public function test_creating_a_user_requires_a_unique_email(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create();

        $response = $this->actingAs($admin)->from('/admin/users/create')->post('/admin/users', [
            'name' => 'Duplikat',
            'email' => $existing->email,
            'password' => 'rahasia-kuat',
            'password_confirmation' => 'rahasia-kuat',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(2, User::query()->count());
    }

    public function test_non_administrator_can_not_create_a_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/users', [
                'name' => 'Dokter Sari',
                'email' => 'sari@rs-contoh.local',
                'password' => 'rahasia-kuat',
                'password_confirmation' => 'rahasia-kuat',
            ])
            ->assertForbidden();

        $this->assertSame(1, User::query()->count());
    }

    public function test_administrator_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Dokter Budi',
            'email' => $user->email,
            'is_active' => '1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/users');

        $user->refresh();

        $this->assertSame('Dokter Budi', $user->name);
        $this->assertTrue($user->is_active);
    }

    public function test_updating_a_user_can_reset_the_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'kata-sandi-baru',
            'password_confirmation' => 'kata-sandi-baru',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('kata-sandi-baru', $user->refresh()->password));
    }

    public function test_administrator_can_deactivate_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->refresh()->is_active);
    }

    public function test_administrator_can_not_demote_or_deactivate_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
        ])->assertSessionHasNoErrors();

        $admin->refresh();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->is_active);
    }

    public function test_the_only_active_administrator_keeps_their_access(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create();

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->put("/admin/users/{$staff->id}", [
            'name' => $staff->name,
            'email' => $staff->email,
        ])->assertSessionHasNoErrors();

        $admin->refresh();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->is_active);
        $this->assertSame(1, User::query()->where('is_admin', true)->count());
    }

    public function test_administrator_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/users');

        $this->assertNull($user->fresh());
    }

    public function test_administrator_can_not_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from('/admin/users')
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertNotNull($admin->fresh());
    }

    public function test_an_administrator_can_delete_another_administrator(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from('/admin/users')
            ->delete("/admin/users/{$otherAdmin->id}")
            ->assertSessionHasNoErrors();

        $this->assertNull($otherAdmin->fresh());
    }

    public function test_edit_screen_is_displayed_to_administrator(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/users/{$user->id}")
            ->assertOk()
            ->assertSee($user->email);
    }
}
