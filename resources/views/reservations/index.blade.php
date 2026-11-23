@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Sistema de reservas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Reservar cita</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if (session('message.content'))
            <div class="alert alert-success" role="status">{{ session('message.content') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Reserva tu cita</h3>
            </div>
            <div class="card-body mt-3">
                <p class="text-muted">
                    Elige un servicio y una fecha para consultar los horarios disponibles.
                    Atendemos de lunes a viernes, de 9:00 a 17:00, en turnos de 30 minutos.
                </p>

                <form action="{{ route('reservations.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                    <div class="col-md-5">
                        <label for="date" class="form-label">Fecha de la cita</label>
                        <input
                            id="date"
                            name="date"
                            type="date"
                            class="form-control"
                            value="{{ $date }}"
                            min="{{ today()->toDateString() }}"
                            required
                        >
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">Ver disponibilidad</button>
                    </div>
                </form>

                <form action="{{ route('reservations.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">

                    <div class="mb-3">
                        <label for="service" class="form-label">Servicio</label>
                        <select
                            id="service"
                            name="service"
                            class="form-select @error('service') is-invalid @enderror"
                            required
                        >
                            <option value="">Selecciona un servicio</option>
                            @foreach ($services as $key => $service)
                                <option value="{{ $key }}" @selected(old('service') === $key)>
                                    {{ $service }} (30 minutos)
                                </option>
                            @endforeach
                        </select>
                        @error('service')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @error('date')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror
                    @error('time')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror

                    <h4 class="h5">Horarios disponibles para {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h4>

                    @if (empty($availableSlots))
                        <p class="text-muted mb-0">
                            No hay horarios disponibles para esta fecha. Selecciona otro día entre semana.
                        </p>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($availableSlots as $slot)
                                <button
                                    type="submit"
                                    name="time"
                                    value="{{ $slot }}"
                                    class="btn btn-outline-primary"
                                >
                                    {{ $slot }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Mis próximas reservas</h3>
            </div>
            <div class="card-body mt-3">
                @if ($reservations->isEmpty())
                    <p class="text-muted mb-0">Todavía no tienes reservas próximas.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservations as $reservation)
                                    <tr>
                                        <td>{{ $reservation->appointment_at->format('d/m/Y') }}</td>
                                        <td>{{ $reservation->appointment_at->format('H:i') }}</td>
                                        <td>{{ $services[$reservation->service] }}</td>
                                        <td><span class="badge bg-success">Confirmada</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
