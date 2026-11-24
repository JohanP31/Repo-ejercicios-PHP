@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>{{ $heading }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">{{ $heading }}</li>
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
                    <h2 class="h4 mb-1">Recetas para compartir</h2>
                    <p class="text-muted mb-0">Descubre platos de la comunidad y publica tus propias ideas.</p>
                </div>
                <a href="{{ route('recipes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Publicar receta
                </a>
            </div>

            <div class="card-body mt-3">
                @if ($showFilters)
                    <form action="{{ route('recipes.index') }}" method="GET" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label for="search" class="visually-hidden">Buscar recetas</label>
                            <input
                                id="search"
                                name="search"
                                type="search"
                                class="form-control"
                                placeholder="Nombre, ingredientes o pasos..."
                                value="{{ $filters['search'] ?? '' }}"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="visually-hidden">Tipo de comida</label>
                            <select id="category" name="category" class="form-select">
                                <option value="">Todos los tipos</option>
                                @foreach ($categories as $key => $category)
                                    <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ingredient" class="visually-hidden">Filtrar por ingrediente</label>
                            <input
                                id="ingredient"
                                name="ingredient"
                                type="search"
                                class="form-control"
                                placeholder="Ingrediente, ej. tomate"
                                value="{{ $filters['ingredient'] ?? '' }}"
                            >
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                    </form>
                @endif

                @if ($recipes->isEmpty())
                    <p class="text-muted mb-0">{{ $emptyMessage }}</p>
                @else
                    <div class="row g-3">
                        @foreach ($recipes as $recipe)
                            @php
                                $ingredients = $recipe->ingredientList();
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4">
                                <article class="card h-100 mb-0">
                                    <div class="card-body">
                                        <span class="badge bg-primary mb-2">{{ $categories[$recipe->category] }}</span>
                                        <h3 class="h5">
                                            <a href="{{ route('recipes.show', $recipe) }}" class="text-decoration-none">
                                                {{ $recipe->title }}
                                            </a>
                                        </h3>
                                        @if ($recipe->description)
                                            <p class="text-muted">{{ $recipe->description }}</p>
                                        @endif
                                        <p class="small mb-2">
                                            <i class="bi bi-clock me-1"></i>{{ $recipe->prep_time }} min
                                            <span class="mx-1">·</span>
                                            <i class="bi bi-people me-1"></i>{{ $recipe->servings }} porciones
                                        </p>
                                        <p class="small text-muted mb-2">
                                            Por {{ $recipe->user?->name ?? 'la comunidad' }}
                                        </p>
                                        <p class="small mb-0">
                                            <strong>Ingredientes:</strong>
                                            {{ implode(', ', array_slice($ingredients, 0, 3)) }}
                                            @if (count($ingredients) > 3)
                                                y {{ count($ingredients) - 3 }} más
                                            @endif
                                        </p>
                                    </div>
                                    <div class="card-footer d-flex flex-wrap gap-2">
                                        <a href="{{ route('recipes.show', $recipe) }}" class="btn btn-sm btn-outline-primary">
                                            Ver receta
                                        </a>
                                        @auth
                                            @if (in_array($recipe->id, $favoriteIds, true))
                                                <form action="{{ route('recipes.unfavorite', $recipe) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-heart-fill me-1"></i>Quitar favorita
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('recipes.favorite', $recipe) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-heart me-1"></i>Guardar
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($recipe->user_id === auth()->id())
                                                <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-sm btn-outline-secondary">
                                                    Editar
                                                </a>
                                                <form
                                                    action="{{ route('recipes.destroy', $recipe) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Deseas eliminar esta receta?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Eliminar</button>
                                                </form>
                                            @endif
                                        @endauth
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $recipes->links('components.customPaginator') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
