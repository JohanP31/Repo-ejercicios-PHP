<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $selectedDate = isset($filters['date'])
            ? Carbon::createFromFormat('Y-m-d', $filters['date'])->startOfDay()
            : null;
        $month = $selectedDate?->copy()->startOfMonth()
            ?? Carbon::createFromFormat('Y-m-d', ($filters['month'] ?? now()->format('Y-m')).'-01')->startOfMonth();
        $selectedDate ??= $month->copy();

        $now = now();
        $reminders = $request->user()->calendarEvents()
            ->whereNotNull('reminder_minutes')
            ->whereNull('reminded_at')
            ->where('starts_at', '>', $now)
            ->where(function (Builder $query) use ($now): void {
                foreach (CalendarEvent::REMINDER_OPTIONS as $minutes => $label) {
                    $query->orWhere(function (Builder $query) use ($now, $minutes): void {
                        $query->where('reminder_minutes', $minutes)
                            ->where('starts_at', '<=', $now->copy()->addMinutes($minutes));
                    });
                }
            })
            ->orderBy('starts_at')
            ->get();

        foreach ($reminders as $reminder) {
            $reminder->reminded_at = $now;
            $reminder->save();
        }

        $calendarStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = collect();

        for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay()) {
            $days->push($day->copy());
        }

        $eventsByDay = $request->user()->calendarEvents()
            ->whereBetween('starts_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (CalendarEvent $event): string => $event->starts_at->toDateString());

        return view('calendar.index', [
            'month' => $month,
            'selectedDate' => $selectedDate,
            'days' => $days,
            'eventsByDay' => $eventsByDay,
            'selectedEvents' => $eventsByDay->get($selectedDate->toDateString(), collect()),
            'reminders' => $reminders,
        ]);
    }

    public function create(Request $request): View
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $startsAt = isset($filters['date'])
            ? Carbon::createFromFormat('Y-m-d', $filters['date'])->setTime(9, 0)
            : now()->addHour()->startOfHour();

        $event = new CalendarEvent;
        $event->starts_at = $startsAt;
        $event->ends_at = $startsAt->copy()->addHour();

        return view('calendar.create', [
            'event' => $event,
            'reminderOptions' => CalendarEvent::REMINDER_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $event = $request->user()->calendarEvents()->create($this->validatedEvent($request));

        return $this->calendarRedirect($event)->with('message', [
            'content' => 'Evento agregado al calendario.',
            'type' => 'success',
        ]);
    }

    public function edit(Request $request, CalendarEvent $event): View
    {
        $event = $request->user()->calendarEvents()->findOrFail($event->id);

        return view('calendar.edit', [
            'event' => $event,
            'reminderOptions' => CalendarEvent::REMINDER_OPTIONS,
        ]);
    }

    public function update(Request $request, CalendarEvent $event): RedirectResponse
    {
        $event = $request->user()->calendarEvents()->findOrFail($event->id);
        $event->fill($this->validatedEvent($request));
        $event->reminded_at = null;
        $event->save();

        return $this->calendarRedirect($event)->with('message', [
            'content' => 'Evento actualizado.',
            'type' => 'success',
        ]);
    }

    public function destroy(Request $request, CalendarEvent $event): RedirectResponse
    {
        $event = $request->user()->calendarEvents()->findOrFail($event->id);
        $eventDate = $event->starts_at;
        $event->delete();

        return redirect()->route('calendar.index', [
            'month' => $eventDate->format('Y-m'),
            'date' => $eventDate->toDateString(),
        ])->with('message', [
            'content' => 'Evento eliminado.',
            'type' => 'success',
        ]);
    }

    /**
     * @return array{title: string, description: ?string, starts_at: string, ends_at: string, reminder_minutes: int|string|null}
     */
    private function validatedEvent(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'reminder_minutes' => [
                'nullable',
                'integer',
                Rule::in(array_keys(CalendarEvent::REMINDER_OPTIONS)),
            ],
        ], [
            'title.required' => 'Escribe un título para el evento.',
            'starts_at.required' => 'Indica la fecha y hora de inicio.',
            'ends_at.after' => 'La hora de finalización debe ser posterior al inicio.',
            'reminder_minutes.in' => 'Selecciona un recordatorio válido.',
        ]);

        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'reminder_minutes' => $validated['reminder_minutes'] ?? null,
        ];
    }

    private function calendarRedirect(CalendarEvent $event): RedirectResponse
    {
        return redirect()->route('calendar.index', [
            'month' => $event->starts_at->format('Y-m'),
            'date' => $event->starts_at->toDateString(),
        ]);
    }
}
