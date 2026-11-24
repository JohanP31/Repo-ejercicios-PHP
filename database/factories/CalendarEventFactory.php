<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+1 month');

        return [
            'user_id' => User::factory()->state(function (array $attributes): array {
                $role = new Role;
                $role->name = 'Calendar User';
                $role->save();

                return ['role_id' => $role->id];
            }),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 hour'),
            'reminder_minutes' => fake()->randomElement([null, ...array_keys(CalendarEvent::REMINDER_OPTIONS)]),
        ];
    }
}
