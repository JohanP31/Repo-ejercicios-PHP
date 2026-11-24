<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_out_button_ends_the_authenticated_session(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(route('home.index'))
            ->assertOk()
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('Sign Out');

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_cannot_use_sign_out(): void
    {
        $this->get(route('home.index'))
            ->assertOk()
            ->assertDontSee('Sign Out');

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function createUser(): User
    {
        $role = new Role;
        $role->name = 'Test User';
        $role->save();

        $user = User::factory()->make();
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }
}
