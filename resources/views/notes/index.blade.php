@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Gestor de notas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Gestor de notas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if (session('message.content'))
            <div class="alert alert-success" role="status">{{ session('message.content') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h3 class="mb-1">Notas compartidas</h3>
                    <p class="text-muted mb-0">Desde esta sección, crea notas para eventos importantes o recordatorios.</p>
                </div>
                <a href="{{ route('notes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Nueva nota
                </a>
            </div>

            <div class="card-body mt-3">
                <form action="{{ route('notes.index') }}" method="GET" class="row g-2 mb-4">
                    <div class="col-md-7">
                        <label for="search" class="visually-hidden">Buscar notas</label>
                        <input
                            id="search"
                            name="search"
                            type="search"
                            class="form-control"
                            placeholder="Buscar por título o contenido..."
                            value="{{ $filters['search'] ?? '' }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="visually-hidden">Filtrar por categoría</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">Todas las categorías</option>
                            @foreach ($categories as $key => $category)
                                <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                    </div>
                </form>

                @if ($notes->isEmpty())
                    <p class="text-muted mb-0">
                        No hay notas que coincidan. Crea una nueva o cambia los filtros.
                    </p>
                @else
                    <div class="row g-3">
                        @foreach ($notes as $note)
                            <div class="col-12 col-md-6 col-xl-4">
                                <article class="card h-100 mb-0">
                                    <div class="card-body">
                                        <span class="badge bg-primary mb-2">{{ $categories[$note->category] }}</span>
                                        <h4 class="h5">{{ $note->title }}</h4>
                                        <p class="text-break mb-3" style="white-space: pre-wrap;">{{ $note->content }}</p>
                                        <p class="text-muted small mb-0">
                                            Actualizada {{ $note->updated_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <div class="card-footer d-flex gap-2">
                                        <a href="{{ route('notes.edit', $note) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Editar
                                        </a>
                                        <form
                                            action="{{ route('notes.destroy', $note) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Deseas eliminar esta nota para todos?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i>Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $notes->links('components.customPaginator') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
