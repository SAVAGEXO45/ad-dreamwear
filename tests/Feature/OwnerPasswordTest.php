<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_settings_require_an_administrator(): void
    {
        $this->get(route('admin.password.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.password.update'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.password.edit'))->assertForbidden();
        $this->put(route('admin.password.update'))->assertForbidden();
    }

    public function test_password_update_rejects_invalid_input_and_an_incorrect_current_password(): void
    {
        $original = 'Existing owner password!';
        $user = User::factory()->create(['is_admin' => true, 'password' => $original]);
        $this->actingAs($user)->from(route('admin.password.edit'));

        $this->get(route('admin.password.edit'))->assertOk()->assertSee('Current password');
        $this->put(route('admin.password.update'), [
            'current_password' => $original,
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors('password');

        $this->put(route('admin.password.update'), [
            'current_password' => $original,
            'password' => str_repeat('é', 37),
            'password_confirmation' => str_repeat('é', 37),
        ])->assertSessionHasErrors(['password' => 'The new password must be at most 72 bytes.']);

        $this->put(route('admin.password.update'), [
            'current_password' => 'Wrong owner password!',
            'password' => 'A valid replacement password!',
            'password_confirmation' => 'A valid replacement password!',
        ])->assertSessionHasErrors('current_password')
            ->assertSessionMissing('_old_input.current_password')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');

        $this->assertTrue(Hash::check($original, $user->fresh()->password));
    }

    public function test_owner_can_change_password_and_keep_only_the_current_session(): void
    {
        config(['session.driver' => 'database']);
        $original = 'Existing owner password!';
        $replacement = 'A valid replacement password!';
        $owner = User::factory()->create(['is_admin' => true, 'password' => $original]);
        $other = User::factory()->create();

        foreach (['other-owner-session' => $owner, 'unrelated-session' => $other] as $id => $user) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp]);
        }

        $this->post(route('login.store'), ['email' => $owner->email, 'password' => $original])
            ->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Change password');
        $sessionId = session()->getId();

        $this->put(route('admin.password.update'), [
            'current_password' => $original,
            'password' => $replacement,
            'password_confirmation' => $replacement,
        ])->assertRedirect(route('admin.password.edit'))->assertSessionHas('status');

        $this->assertNotSame($sessionId, session()->getId());
        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);
        $this->assertDatabaseMissing('sessions', ['id' => 'other-owner-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'unrelated-session']);
        $this->assertTrue(Hash::check($replacement, $owner->fresh()->password));
        $this->assertFalse(Hash::check($original, $owner->fresh()->password));
        $this->get(route('admin.dashboard'))->assertOk();
        $this->post(route('admin.logout'))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => $owner->email, 'password' => $original])
            ->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => $owner->email, 'password' => $replacement])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($owner);
    }
}
