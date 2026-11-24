@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Publicar receta</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recipes.index') }}">Recetas</a></li>
                <li class="breadcrumb-item active">Publicar receta</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Comparte una receta</h2>
            </div>
            <form action="{{ route('recipes.store') }}" method="POST">
                @csrf
                @include('recipes.form', ['submitLabel' => 'Publicar receta'])
            </form>
        </div>
    </section>
@endsection
