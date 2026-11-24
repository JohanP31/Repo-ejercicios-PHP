@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Editar receta</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recipes.index') }}">Recetas</a></li>
                <li class="breadcrumb-item active">Editar receta</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Actualizar {{ $recipe->title }}</h2>
            </div>
            <form action="{{ route('recipes.update', $recipe) }}" method="POST">
                @csrf
                @method('PUT')
                @include('recipes.form', ['submitLabel' => 'Guardar cambios'])
            </form>
        </div>
    </section>
@endsection
