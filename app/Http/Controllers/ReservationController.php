<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $date = $validated['date'] ?? today()->toDateString();

        return view('reservations.index', [
            'date' => $date,
            'services' => Reservation::SERVICES,
            'availableSlots' => $this->availableSlotsFor($date),
            'reservations' => $request->user()->reservations()
                ->where('appointment_at', '>=', now())
                ->orderBy('appointment_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service' => ['required', Rule::in(array_keys(Reservation::SERVICES))],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ], [
            'service.required' => 'Selecciona un servicio.',
            'service.in' => 'Selecciona un servicio válido.',
            'date.required' => 'Selecciona una fecha para la reserva.',
            'date.after_or_equal' => 'La fecha de la reserva no puede estar en el pasado.',
            'time.required' => 'Selecciona un horario disponible.',
        ]);

        if (! in_array($validated['time'], $this->availableSlotsFor($validated['date']), true)) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'Ese horario ya no está disponible. Selecciona otro.']);
        }

        $appointmentAt = Carbon::parse($validated['date'].' '.$validated['time']);

        $reservation = Reservation::query()->firstOrCreate(
            ['appointment_at' => $appointmentAt],
            [
                'user_id' => $request->user()->id,
                'service' => $validated['service'],
            ]
        );

        if (! $reservation->wasRecentlyCreated) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'Ese horario acaba de ser reservado. Selecciona otro.']);
        }

        $service = Reservation::SERVICES[$reservation->service];

        return redirect()
            ->route('reservations.index', ['date' => $validated['date']])
            ->with('message', [
                'content' => "Reserva confirmada: {$service} el {$appointmentAt->format('d/m/Y')} a las {$appointmentAt->format('H:i')}.",
                'type' => 'success',
            ]);
    }

    /**
     * @return list<string>
     */
    private function availableSlotsFor(string $date): array
    {
        $day = Carbon::parse($date)->startOfDay();

        if (! $day->isWeekday()) {
            return [];
        }

        $bookedSlots = Reservation::query()
            ->whereBetween('appointment_at', [
                $day->toDateTimeString(),
                $day->copy()->endOfDay()->toDateTimeString(),
            ])
            ->pluck('appointment_at')
            ->map(fn (mixed $appointmentAt): string => Carbon::parse($appointmentAt)->format('H:i'))
            ->all();

        $availableSlots = [];

        for ($minutes = 9 * 60; $minutes < 17 * 60; $minutes += 30) {
            $slot = $day->copy()->setTime(intdiv($minutes, 60), $minutes % 60);
            $time = $slot->format('H:i');

            if ($slot->lessThanOrEqualTo(now()) || in_array($time, $bookedSlots, true)) {
                continue;
            }

            $availableSlots[] = $time;
        }

        return $availableSlots;
    }
}
