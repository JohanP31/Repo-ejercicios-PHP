@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Nuevo evento</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">Calendario</a></li>
                <li class="breadcrumb-item active">Nuevo evento</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Agregar evento</h2>
            </div>
            <form action="{{ route('calendar.store') }}" method="POST">
                @csrf
                @include('calendar.form', ['submitLabel' => 'Guardar evento'])
            </form>
        </div>
    </section>
@endsection
