@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Cronómetro</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Cronómetro</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h2 class="h4 mb-2">Cronómetro online</h2>
                        <p class="text-muted mb-4">Inicia, pausa o reinicia el cronómetro.</p>

                        <p
                            id="stopwatch-display"
                            class="display-2 font-monospace mb-4"
                            role="timer"
                            aria-live="off"
                        >00:00:00.00</p>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <button id="stopwatch-start" type="button" class="btn btn-primary px-4">
                                Iniciar
                            </button>
                            <button id="stopwatch-pause" type="button" class="btn btn-warning px-4" disabled>
                                Pausar
                            </button>
                            <button id="stopwatch-reset" type="button" class="btn btn-secondary px-4" disabled>
                                Reiniciar
                            </button>
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
            const display = document.getElementById('stopwatch-display');
            const startButton = document.getElementById('stopwatch-start');
            const pauseButton = document.getElementById('stopwatch-pause');
            const resetButton = document.getElementById('stopwatch-reset');
            let elapsedMilliseconds = 0;
            let startedAt = null;
            let intervalId = null;

            function renderTime() {
                const currentElapsed = startedAt === null
                    ? elapsedMilliseconds
                    : elapsedMilliseconds + performance.now() - startedAt;
                const totalHundredths = Math.floor(currentElapsed / 10);
                const totalSeconds = Math.floor(totalHundredths / 100);
                const totalMinutes = Math.floor(totalSeconds / 60);
                const hours = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;
                const seconds = totalSeconds % 60;
                const hundredths = totalHundredths % 100;
                const pad = (value) => String(value).padStart(2, '0');

                display.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}.${pad(hundredths)}`;
            }

            startButton.addEventListener('click', function () {
                if (startedAt !== null) {
                    return;
                }

                startedAt = performance.now();
                intervalId = window.setInterval(renderTime, 10);
                startButton.disabled = true;
                pauseButton.disabled = false;
                resetButton.disabled = false;
            });

            pauseButton.addEventListener('click', function () {
                if (startedAt === null) {
                    return;
                }

                elapsedMilliseconds += performance.now() - startedAt;
                startedAt = null;
                window.clearInterval(intervalId);
                intervalId = null;
                renderTime();
                startButton.disabled = false;
                pauseButton.disabled = true;
            });

            resetButton.addEventListener('click', function () {
                if (intervalId !== null) {
                    window.clearInterval(intervalId);
                }

                elapsedMilliseconds = 0;
                startedAt = null;
                intervalId = null;
                renderTime();
                startButton.disabled = false;
                pauseButton.disabled = true;
                resetButton.disabled = true;
            });
        });
    </script>
@endpush
