@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Lista de tareas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Lista de tareas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Tareas compartidas</h3>
            </div>

            <div class="card-body mt-3">
                @if (session('message'))
                    <div class="alert alert-success" role="status">{{ session('message') }}</div>
                @endif

                <form action="{{ route('tasks.store') }}" method="POST" class="row g-2 mb-4">
                    @csrf
                    <div class="col">
                        <label for="title" class="visually-hidden">Nueva tarea</label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            class="form-control @error('title') is-invalid @enderror"
                            placeholder="Escribe una tarea..."
                            value="{{ old('title') }}"
                            maxlength="255"
                            required
                        >
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Agregar
                        </button>
                    </div>
                </form>

                @if ($tasks->isEmpty())
                    <p class="text-muted mb-0">Todavía no hay tareas. Agrega la primera.</p>
                @else
                    <ul class="list-group">
                        @foreach ($tasks as $task)
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="btn btn-sm {{ $task->completed ? 'btn-success' : 'btn-outline-secondary' }}"
                                        aria-label="{{ $task->completed ? 'Marcar como pendiente' : 'Marcar como realizada' }}: {{ $task->title }}"
                                        title="{{ $task->completed ? 'Marcar como pendiente' : 'Marcar como realizada' }}"
                                    >
                                        <i class="bi {{ $task->completed ? 'bi-check-lg' : 'bi-circle' }}"></i>
                                    </button>
                                </form>

                                <span class="flex-grow-1 {{ $task->completed ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->title }}
                                </span>

                                <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        aria-label="Eliminar tarea: {{ $task->title }}"
                                        title="Eliminar tarea"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>
@endsection
