<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_reservations(): void
    {
        $this->get(route('reservations.index'))
            ->assertRedirect(route('login'));

        $this->post(route('reservations.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_non_admin_can_view_weekday_availability(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 08:00:00'));
        $user = $this->createUserWithRole('Content Manager');

        $this->actingAs($user)
            ->get(route('reservations.index', ['date' => '2026-11-23']))
            ->assertOk()
            ->assertSee('Consulta general')
            ->assertSee('Asesoría personalizada')
            ->assertSee('Servicio general')
            ->assertSee('value="09:00"', false)
            ->assertSee('value="16:30"', false)
            ->assertDontSee('value="17:00"', false);
    }

    public function test_booking_is_confirmed_and_the_slot_is_hidden_from_other_users(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 08:00:00'));
        $user = $this->createUserWithRole('Content Manager');
        $otherUser = $this->createUserWithRole('Administrador');

        $this->actingAs($user)
            ->post(route('reservations.store'), [
                'service' => 'general_consultation',
                'date' => '2026-11-23',
                'time' => '09:00',
            ])
            ->assertRedirect(route('reservations.index', ['date' => '2026-11-23']))
            ->assertSessionHas('message.content', 'Reserva confirmada: Consulta general el 23/11/2026 a las 09:00.')
            ->assertSessionHas('message.type', 'success');

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'service' => 'general_consultation',
            'appointment_at' => '2026-11-23 09:00:00',
        ]);

        $this->flushSession();
        $this->actingAs($otherUser)
            ->get(route('reservations.index', ['date' => '2026-11-23']))
            ->assertOk()
            ->assertDontSee('value="09:00"', false)
            ->assertSee('Todavía no tienes reservas próximas.');
    }

    public function test_a_reserved_slot_cannot_be_booked_twice(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 08:00:00'));
        $firstUser = $this->createUserWithRole('Content Manager');
        $secondUser = $this->createUserWithRole('Content Manager');
        $slot = '2026-11-23 09:30:00';

        Reservation::query()->create([
            'user_id' => $firstUser->id,
            'service' => 'general_service',
            'appointment_at' => $slot,
        ]);

        $this->actingAs($secondUser)
            ->from(route('reservations.index', ['date' => '2026-11-23']))
            ->post(route('reservations.store'), [
                'service' => 'personal_advice',
                'date' => '2026-11-23',
                'time' => '09:30',
            ])
            ->assertRedirect(route('reservations.index', ['date' => '2026-11-23']))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $firstUser->id,
            'appointment_at' => $slot,
        ]);
    }

    public function test_booking_rejects_weekends_invalid_hours_past_times_and_services(): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 10:00:00'));
        $user = $this->createUserWithRole('Content Manager');
        $this->actingAs($user);

        $this->from(route('reservations.index'))
            ->post(route('reservations.store'), [
                'service' => 'general_service',
                'date' => '2026-11-29',
                'time' => '09:00',
            ])
            ->assertSessionHasErrors('time');

        $this->from(route('reservations.index'))
            ->post(route('reservations.store'), [
                'service' => 'general_service',
                'date' => '2026-11-24',
                'time' => '17:00',
            ])
            ->assertSessionHasErrors('time');

        $this->from(route('reservations.index'))
            ->post(route('reservations.store'), [
                'service' => 'general_service',
                'date' => '2026-11-23',
                'time' => '09:30',
            ])
            ->assertSessionHasErrors('time');

        $this->from(route('reservations.index'))
            ->post(route('reservations.store'), [
                'service' => 'unknown',
                'date' => '2026-11-24',
                'time' => '11:00',
            ])
            ->assertSessionHasErrors('service');

        $this->from(route('reservations.index'))
            ->post(route('reservations.store'), [
                'service' => 'general_service',
                'date' => '2026-11-22',
                'time' => '11:00',
            ])
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('reservations', 0);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = new Role;
        $role->name = $roleName;
        $role->save();

        $user = User::factory()->make();
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }
}
