@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Gestor de gastos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Gestor de gastos</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if (session('message'))
            <div class="alert alert-success" role="status">{{ session('message') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Registrar un gasto</h3>
            </div>
            <div class="card-body mt-3">
                <p class="text-muted">Registra todos los montos en la misma moneda para que el resumen mensual sea coherente.</p>

                <form action="{{ route('expenses.store') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label for="description" class="form-label">Descripción</label>
                        <input
                            id="description"
                            name="description"
                            type="text"
                            class="form-control @error('description') is-invalid @enderror"
                            value="{{ old('description') }}"
                            maxlength="255"
                            required
                        >
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="amount" class="form-label">Monto</label>
                        <input
                            id="amount"
                            name="amount"
                            type="number"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}"
                            min="0.01"
                            max="9999999999.99"
                            step="0.01"
                            inputmode="decimal"
                            required
                        >
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="spent_at" class="form-label">Fecha</label>
                        <input
                            id="spent_at"
                            name="spent_at"
                            type="date"
                            class="form-control @error('spent_at') is-invalid @enderror"
                            value="{{ old('spent_at', now()->toDateString()) }}"
                            max="{{ now()->toDateString() }}"
                            required
                        >
                        @error('spent_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">Categoría</label>
                        <select
                            id="category"
                            name="category"
                            class="form-select @error('category') is-invalid @enderror"
                            required
                        >
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Registrar gasto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h3 class="mb-0">Resumen mensual</h3>
                <form action="{{ route('expenses.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <label for="month" class="form-label mb-0">Mes</label>
                    <input id="month" name="month" type="month" class="form-control" value="{{ $month }}">
                    <button class="btn btn-outline-primary" type="submit">Ver</button>
                </form>
            </div>

            <div class="card-body mt-3">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded h-100">
                            <p class="text-muted mb-1">Total del mes</p>
                            <h4 class="mb-0">{{ number_format((float) $summary->total, 2, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded h-100">
                            <p class="text-muted mb-1">Gastos registrados</p>
                            <h4 class="mb-0">{{ $summary->expense_count }}</h4>
                        </div>
                    </div>
                </div>

                <h4 class="h5">Totales por categoría</h4>
                @if ($categoryTotals->isEmpty())
                    <p class="text-muted">No hay gastos registrados en este mes.</p>
                @else
                    <ul class="list-group mb-4">
                        @foreach ($categoryTotals as $categoryTotal)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $categories[$categoryTotal->category] }}</span>
                                <strong>{{ number_format((float) $categoryTotal->total, 2, ',', '.') }}</strong>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <h4 class="h5">Detalle de gastos</h4>
                @if ($expenses->isEmpty())
                    <p class="text-muted mb-0">No hay gastos para mostrar en este mes.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Monto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->spent_at->format('d/m/Y') }}</td>
                                        <td>{{ $expense->description }}</td>
                                        <td>{{ $categories[$expense->category] }}</td>
                                        <td class="text-end">{{ number_format((float) $expense->amount, 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            <form
                                                action="{{ route('expenses.destroy', $expense->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Deseas eliminar este gasto?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    aria-label="Eliminar gasto: {{ $expense->description }}"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $expenses->links('components.customPaginator') }}
                @endif
            </div>
        </div>
    </section>
@endsection
