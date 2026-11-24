@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Calendario de eventos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Calendario de eventos</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if ($reminders->isNotEmpty())
            <div class="alert alert-warning" role="status" aria-live="polite">
                <h2 class="h5">Tienes próximos recordatorios</h2>
                <ul class="mb-0">
                    @foreach ($reminders as $reminder)
                        <li>
                            <strong>{{ $reminder->title }}</strong>:
                            {{ $reminder->starts_at->format('d/m/Y H:i') }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h4 mb-1">Mi calendario</h2>
                    <p class="text-muted mb-0">Tus eventos y recordatorios son privados.</p>
                </div>
                <a href="{{ route('calendar.create', ['date' => $selectedDate->toDateString()]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo evento
                </a>
            </div>

            <div class="card-body mt-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <a
                            href="{{ route('calendar.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
                            class="btn btn-outline-secondary"
                            aria-label="Mes anterior"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <h3 class="h5 text-capitalize mb-0">
                            {{ $month->copy()->locale('es')->translatedFormat('F Y') }}
                        </h3>
                        <a
                            href="{{ route('calendar.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
                            class="btn btn-outline-secondary"
                            aria-label="Mes siguiente"
                        >
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>

                    <form action="{{ route('calendar.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        <label for="month" class="form-label mb-0">Ir al mes</label>
                        <input id="month" name="month" type="month" class="form-control" value="{{ $month->format('Y-m') }}">
                        <button type="submit" class="btn btn-outline-primary">Ver</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="min-width: 840px;">
                        <thead>
                            <tr>
                                <th scope="col">Lun</th>
                                <th scope="col">Mar</th>
                                <th scope="col">Mié</th>
                                <th scope="col">Jue</th>
                                <th scope="col">Vie</th>
                                <th scope="col">Sáb</th>
                                <th scope="col">Dom</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($days->chunk(7) as $week)
                                <tr>
                                    @foreach ($week as $day)
                                        @php
                                            $dayEvents = $eventsByDay->get($day->toDateString(), collect());
                                        @endphp
                                        <td class="align-top {{ $day->format('Y-m') === $month->format('Y-m') ? '' : 'bg-light text-muted' }}" style="height: 120px; width: 14.28%;">
                                            <a
                                                href="{{ route('calendar.index', ['month' => $month->format('Y-m'), 'date' => $day->toDateString()]) }}"
                                                class="btn btn-sm {{ $selectedDate->isSameDay($day) ? 'btn-primary' : 'btn-light' }} mb-1"
                                                aria-label="Ver eventos del {{ $day->format('d/m/Y') }}"
                                            >
                                                {{ $day->day }}
                                            </a>
                                            @foreach ($dayEvents->take(2) as $event)
                                                <a
                                                    href="{{ route('calendar.edit', $event) }}"
                                                    class="d-block text-truncate small text-decoration-none"
                                                    title="{{ $event->title }}"
                                                >
                                                    {{ $event->starts_at->format('H:i') }} {{ $event->title }}
                                                </a>
                                            @endforeach
                                            @if ($dayEvents->count() > 2)
                                                <span class="small text-muted">+ {{ $dayEvents->count() - 2 }} más</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="h5 mb-0">Eventos del {{ $selectedDate->format('d/m/Y') }}</h2>
                <a href="{{ route('calendar.create', ['date' => $selectedDate->toDateString()]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i>Agregar para este día
                </a>
            </div>
            <div class="card-body mt-3">
                @if ($selectedEvents->isEmpty())
                    <p class="text-muted mb-0">No tienes eventos para este día.</p>
                @else
                    <div class="list-group">
                        @foreach ($selectedEvents as $event)
                            <article class="list-group-item">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                    <div>
                                        <h3 class="h5 mb-1">{{ $event->title }}</h3>
                                        <p class="text-muted mb-1">
                                            {{ $event->starts_at->format('H:i') }}–{{ $event->ends_at->format('H:i') }}
                                        </p>
                                        @if ($event->description)
                                            <p class="mb-1" style="white-space: pre-wrap;">{{ $event->description }}</p>
                                        @endif
                                        @if ($event->reminder_minutes !== null)
                                            <span class="badge bg-info text-dark">
                                                Recordatorio: {{ \App\Models\CalendarEvent::REMINDER_OPTIONS[$event->reminder_minutes] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('calendar.edit', $event) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Editar
                                        </a>
                                        <form
                                            action="{{ route('calendar.destroy', $event) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Deseas eliminar este evento?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i>Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
