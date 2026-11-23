@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Nueva nota</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('notes.index') }}">Gestor de notas</a></li>
                <li class="breadcrumb-item active">Nueva nota</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Escribe una nota</h3>
            </div>
            <div class="card-body mt-3">
                @include('notes.form', [
                    'note' => null,
                    'action' => route('notes.store'),
                    'submitLabel' => 'Guardar nota',
                ])
            </div>
        </div>
    </section>
@endsection
