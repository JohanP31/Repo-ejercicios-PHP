@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Calculadora de propinas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Calculadora de propinas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Calcula tu propina</h3>
            </div>

            <div class="card-body mt-3">
                <p class="text-muted">
                    Ingresa el monto de la cuenta y selecciona el porcentaje. Los importes se muestran en la misma moneda de la cuenta.
                </p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="bill-amount" class="form-label">Monto total de la cuenta</label>
                        <input
                            id="bill-amount"
                            type="number"
                            class="form-control"
                            min="0"
                            step="0.01"
                            inputmode="decimal"
                            placeholder="0.00"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="tip-percentage" class="form-label">
                            Porcentaje de propina: <span id="tip-percentage-label">15%</span>
                        </label>
                        <input
                            id="tip-percentage"
                            type="range"
                            class="form-range"
                            min="0"
                            max="50"
                            step="1"
                            value="15"
                        >
                        <div class="d-flex justify-content-between text-muted small" aria-hidden="true">
                            <span>0%</span>
                            <span>50%</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <p class="text-muted mb-1">Propina</p>
                            <h4 id="tip-amount" class="mb-0" aria-live="polite">0,00</h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <p class="text-muted mb-1">Total con propina</p>
                            <h4 id="bill-total" class="mb-0" aria-live="polite">0,00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const billAmountInput = document.getElementById('bill-amount');
            const tipPercentageInput = document.getElementById('tip-percentage');
            const tipPercentageLabel = document.getElementById('tip-percentage-label');
            const tipAmountOutput = document.getElementById('tip-amount');
            const billTotalOutput = document.getElementById('bill-total');
            const amountFormatter = new Intl.NumberFormat('es', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            function updateTip() {
                const billAmount = Number(billAmountInput.value);
                const tipPercentage = Number(tipPercentageInput.value);
                const tipAmount = Number.isFinite(billAmount) && billAmount > 0
                    ? billAmount * tipPercentage / 100
                    : 0;

                tipPercentageLabel.textContent = `${tipPercentage}%`;
                tipAmountOutput.textContent = amountFormatter.format(tipAmount);
                billTotalOutput.textContent = amountFormatter.format(
                    (Number.isFinite(billAmount) && billAmount > 0 ? billAmount : 0) + tipAmount
                );
            }

            billAmountInput.addEventListener('input', updateTip);
            tipPercentageInput.addEventListener('input', updateTip);
            updateTip();
        });
    </script>
@endpush
