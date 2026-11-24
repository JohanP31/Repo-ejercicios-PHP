<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\CalendarEventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_calendar(): void
    {
        $this->get(route('calendar.index'))
            ->assertRedirect(route('login'));

        $this->post(route('calendar.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_user_can_create_edit_and_delete_events(): void
    {
        $user = $this->createUser();
        $eventData = [
            'title' => 'Reunión de equipo',
            'description' => 'Revisar el calendario del proyecto',
            'starts_at' => '2026-11-25T10:00',
            'ends_at' => '2026-11-25T11:00',
            'reminder_minutes' => '15',
            'user_id' => 999999,
        ];

        $this->actingAs($user)
            ->post(route('calendar.store'), $eventData)
            ->assertRedirect(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']));

        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $user->id,
            'title' => 'Reunión de equipo',
            'reminder_minutes' => 15,
        ]);

        $event = CalendarEvent::query()->where('title', 'Reunión de equipo')->firstOrFail();

        $this->get(route('calendar.create'))
            ->assertOk()
            ->assertSee('Agregar evento');

        $this->get(route('calendar.edit', $event))
            ->assertOk()
            ->assertSee('Actualizar evento');

        $this->get(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']))
            ->assertOk()
            ->assertSee('Reunión de equipo')
            ->assertSee('Revisar el calendario del proyecto');

        $this->put(route('calendar.update', $event), [
            ...$eventData,
            'title' => 'Planificación mensual',
            'reminder_minutes' => '30',
        ])->assertRedirect(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']));

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'user_id' => $user->id,
            'title' => 'Planificación mensual',
            'reminder_minutes' => 30,
            'reminded_at' => null,
        ]);

        $this->delete(route('calendar.destroy', $event))
            ->assertRedirect(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']));

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_user_cannot_access_another_users_events(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $event = $owner->calendarEvents()->create([
            'title' => 'Evento privado',
            'starts_at' => '2026-11-25 10:00:00',
            'ends_at' => '2026-11-25 11:00:00',
        ]);

        $this->actingAs($otherUser)
            ->get(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']))
            ->assertOk()
            ->assertDontSee('Evento privado');

        $this->get(route('calendar.edit', $event))
            ->assertNotFound();

        $this->put(route('calendar.update', $event), [
            'title' => 'Intento de edición',
            'starts_at' => '2026-11-25T10:00',
            'ends_at' => '2026-11-25T11:00',
        ])->assertNotFound();

        $this->delete(route('calendar.destroy', $event))
            ->assertNotFound();

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'user_id' => $owner->id,
            'title' => 'Evento privado',
        ]);
    }

    public function test_invalid_event_times_and_reminders_are_rejected(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->from(route('calendar.create'))
            ->post(route('calendar.store'), [
                'title' => 'Evento inválido',
                'starts_at' => '2026-11-25T11:00',
                'ends_at' => '2026-11-25T10:00',
                'reminder_minutes' => '10',
            ])
            ->assertRedirect(route('calendar.create'))
            ->assertSessionHasErrors(['ends_at', 'reminder_minutes']);

        $this->assertDatabaseCount('calendar_events', 0);
    }

    public function test_due_reminder_is_shown_once_when_calendar_is_opened(): void
    {
        $this->travelTo(Carbon::parse('2026-11-24 09:00:00'));
        $user = $this->createUser();
        $user->calendarEvents()->create([
            'title' => 'Llamar al proveedor',
            'starts_at' => '2026-11-24 09:10:00',
            'ends_at' => '2026-11-24 09:30:00',
            'reminder_minutes' => 15,
        ]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-24']))
            ->assertOk()
            ->assertSee('Tienes próximos recordatorios')
            ->assertSee('Llamar al proveedor');

        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $user->id,
            'title' => 'Llamar al proveedor',
            'reminded_at' => '2026-11-24 09:00:00',
        ]);

        $this->get(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-24']))
            ->assertOk()
            ->assertDontSee('Tienes próximos recordatorios');
    }

    public function test_event_content_is_escaped_in_the_calendar(): void
    {
        $user = $this->createUser();
        $script = '<script>alert(1)</script>';

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'title' => $script,
                'description' => $script,
                'starts_at' => '2026-11-25T10:00',
                'ends_at' => '2026-11-25T11:00',
            ])
            ->assertRedirect(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']));

        $this->get(route('calendar.index', ['month' => '2026-11', 'date' => '2026-11-25']))
            ->assertOk()
            ->assertSee($script)
            ->assertDontSee($script, false);
    }

    public function test_event_factory_creates_an_owner(): void
    {
        $event = CalendarEvent::factory()->create();

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'user_id' => $event->user_id,
        ]);
        $this->assertNotNull($event->user->role_id);
    }

    public function test_calendar_seeder_adds_one_demo_event_to_the_demo_user(): void
    {
        $user = $this->createUser();
        $user->email = 'jhond@privateblog.com';
        $user->save();

        $this->seed(CalendarEventSeeder::class);
        $this->seed(CalendarEventSeeder::class);

        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $user->id,
            'title' => 'Revisión semanal',
        ]);
        $this->assertDatabaseCount('calendar_events', 1);
    }

    private function createUser(): User
    {
        $role = new Role;
        $role->name = 'Calendar User';
        $role->save();

        $user = User::factory()->make();
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }
}
