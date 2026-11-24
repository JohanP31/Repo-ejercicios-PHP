<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'jhond@privateblog.com')->firstOrFail();
        $startsAt = now()->addDay()->setTime(10, 0);

        $event = $user->calendarEvents()->updateOrCreate(
            ['title' => 'Revisión semanal'],
            [
                'description' => 'Organiza las prioridades y tareas de la semana.',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHour(),
                'reminder_minutes' => 15,
            ],
        );

        $event->reminded_at = null;
        $event->save();
    }
}
