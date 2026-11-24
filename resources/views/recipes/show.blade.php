@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>{{ $recipe->title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recipes.index') }}">Recetas</a></li>
                <li class="breadcrumb-item active">{{ $recipe->title }}</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if (session('message.content'))
            <div class="alert alert-success" role="status">{{ session('message.content') }}</div>
        @endif

        <article class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <span class="badge bg-primary mb-2">{{ \App\Models\Recipe::CATEGORIES[$recipe->category] }}</span>
                    <h2 class="h3 mb-1">{{ $recipe->title }}</h2>
                    <p class="text-muted mb-0">Compartida por {{ $recipe->user?->name ?? 'la comunidad' }}</p>
                </div>
                @auth
                    @if ($isFavorite)
                        <form action="{{ route('recipes.unfavorite', $recipe) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-heart-fill me-1"></i>Quitar de favoritas
                            </button>
                        </form>
                    @else
                        <form action="{{ route('recipes.favorite', $recipe) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-heart me-1"></i>Guardar en favoritas
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-danger">
                        Inicia sesión para guardar
                    </a>
                @endauth
            </div>

            <div class="card-body mt-3">
                @if ($recipe->description)
                    <p class="lead">{{ $recipe->description }}</p>
                @endif

                <div class="d-flex flex-wrap gap-4 mb-4">
                    <span><i class="bi bi-clock me-1"></i>{{ $recipe->prep_time }} minutos</span>
                    <span><i class="bi bi-people me-1"></i>{{ $recipe->servings }} porciones</span>
                </div>

                <div class="row g-4">
                    <div class="col-md-5">
                        <h3 class="h5">Ingredientes</h3>
                        <ul class="list-group">
                            @foreach ($recipe->ingredientList() as $ingredient)
                                <li class="list-group-item">{{ $ingredient }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-7">
                        <h3 class="h5">Preparación</h3>
                        <ol class="list-group list-group-numbered">
                            @foreach (preg_split('/\R/u', $recipe->instructions, -1, PREG_SPLIT_NO_EMPTY) as $step)
                                <li class="list-group-item">{{ $step }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="recipe-share-link" class="form-label">Enlace público para compartir</label>
                    <input
                        id="recipe-share-link"
                        type="url"
                        class="form-control"
                        value="{{ route('recipes.show', $recipe) }}"
                        readonly
                        onclick="this.select()"
                    >
                    <div class="form-text">Selecciona y copia el enlace para compartir esta receta.</div>
                </div>

                @auth
                    @if ($recipe->user_id === auth()->id())
                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-outline-primary">Editar receta</a>
                            <form
                                action="{{ route('recipes.destroy', $recipe) }}"
                                method="POST"
                                onsubmit="return confirm('¿Deseas eliminar esta receta?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Eliminar receta</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </article>
    </section>
@endsection
