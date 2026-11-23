@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Generador de contraseñas seguras</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Generador de contraseñas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Crea una contraseña segura</h3>
            </div>

            <div class="card-body mt-3">
                <p class="text-muted">
                    Elige la longitud y los tipos de caracteres. La contraseña se genera en tu navegador y no se envía al servidor.
                </p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="password-length" class="form-label">
                            Longitud: <span id="password-length-label">16</span> caracteres
                        </label>
                        <input
                            id="password-length"
                            type="range"
                            class="form-range"
                            min="8"
                            max="64"
                            step="1"
                            value="16"
                        >
                        <div class="d-flex justify-content-between text-muted small" aria-hidden="true">
                            <span>8</span>
                            <span>64</span>
                        </div>
                    </div>

                    <fieldset class="col-md-6">
                        <legend class="form-label">Tipos de caracteres</legend>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input id="include-lowercase" class="form-check-input character-set" type="checkbox" checked>
                                <label class="form-check-label" for="include-lowercase">Minúsculas (a-z)</label>
                            </div>
                            <div class="form-check">
                                <input id="include-uppercase" class="form-check-input character-set" type="checkbox" checked>
                                <label class="form-check-label" for="include-uppercase">Mayúsculas (A-Z)</label>
                            </div>
                            <div class="form-check">
                                <input id="include-numbers" class="form-check-input character-set" type="checkbox" checked>
                                <label class="form-check-label" for="include-numbers">Números (0-9)</label>
                            </div>
                            <div class="form-check">
                                <input id="include-symbols" class="form-check-input character-set" type="checkbox" checked>
                                <label class="form-check-label" for="include-symbols">Símbolos (!@#$...)</label>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="mt-4">
                    <label for="generated-password" class="form-label">Tu contraseña</label>
                    <div class="input-group">
                        <input
                            id="generated-password"
                            type="password"
                            class="form-control font-monospace"
                            placeholder="Genera una contraseña segura"
                            autocomplete="off"
                            readonly
                            aria-describedby="password-status"
                        >
                        <button id="toggle-password-visibility" class="btn btn-outline-secondary" type="button" disabled>
                            <i class="bi bi-eye" aria-hidden="true"></i>
                            <span class="visually-hidden">Mostrar contraseña</span>
                        </button>
                        <button id="copy-password" class="btn btn-outline-primary" type="button" disabled>
                            <i class="bi bi-clipboard" aria-hidden="true"></i>
                            Copiar
                        </button>
                    </div>
                    <div id="password-status" class="form-text" role="status" aria-live="polite"></div>
                </div>

                <button id="generate-password" class="btn btn-primary mt-3" type="button">
                    <i class="bi bi-shield-lock me-1" aria-hidden="true"></i>
                    Generar contraseña
                </button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lengthInput = document.getElementById('password-length');
            const lengthLabel = document.getElementById('password-length-label');
            const passwordOutput = document.getElementById('generated-password');
            const statusOutput = document.getElementById('password-status');
            const generateButton = document.getElementById('generate-password');
            const copyButton = document.getElementById('copy-password');
            const visibilityButton = document.getElementById('toggle-password-visibility');
            const characterSets = [
                { input: document.getElementById('include-lowercase'), characters: 'abcdefghijklmnopqrstuvwxyz' },
                { input: document.getElementById('include-uppercase'), characters: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' },
                { input: document.getElementById('include-numbers'), characters: '0123456789' },
                { input: document.getElementById('include-symbols'), characters: '!@#$%^&*()-_=+[]{};:,.?' },
            ];

            function secureRandomIndex(maximum) {
                if (!window.crypto || typeof window.crypto.getRandomValues !== 'function') {
                    throw new Error('Este navegador no permite generar números aleatorios seguros.');
                }

                const limit = Math.floor(256 / maximum) * maximum;
                const randomByte = new Uint8Array(1);
                let value;

                do {
                    window.crypto.getRandomValues(randomByte);
                    value = randomByte[0];
                } while (value >= limit);

                return value % maximum;
            }

            function generatePassword() {
                const selectedSets = characterSets.filter(function (set) {
                    return set.input.checked;
                });

                if (selectedSets.length === 0) {
                    statusOutput.textContent = 'Selecciona al menos un tipo de carácter.';
                    return;
                }

                const passwordLength = Number(lengthInput.value);
                const allCharacters = selectedSets.map(function (set) {
                    return set.characters;
                }).join('');
                const passwordCharacters = selectedSets.map(function (set) {
                    return set.characters[secureRandomIndex(set.characters.length)];
                });

                while (passwordCharacters.length < passwordLength) {
                    passwordCharacters.push(allCharacters[secureRandomIndex(allCharacters.length)]);
                }

                for (let index = passwordCharacters.length - 1; index > 0; index--) {
                    const swapIndex = secureRandomIndex(index + 1);
                    [passwordCharacters[index], passwordCharacters[swapIndex]] =
                        [passwordCharacters[swapIndex], passwordCharacters[index]];
                }

                passwordOutput.value = passwordCharacters.join('');
                passwordOutput.type = 'password';
                copyButton.disabled = false;
                visibilityButton.disabled = false;
                visibilityButton.querySelector('span').textContent = 'Mostrar contraseña';
                visibilityButton.querySelector('i').className = 'bi bi-eye';
                statusOutput.textContent = 'Contraseña generada de forma segura en este navegador.';
            }

            lengthInput.addEventListener('input', function () {
                lengthLabel.textContent = lengthInput.value;
            });

            generateButton.addEventListener('click', function () {
                try {
                    generatePassword();
                } catch (error) {
                    statusOutput.textContent = error.message;
                }
            });

            visibilityButton.addEventListener('click', function () {
                const isHidden = passwordOutput.type === 'password';
                passwordOutput.type = isHidden ? 'text' : 'password';
                visibilityButton.querySelector('span').textContent =
                    isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña';
                visibilityButton.querySelector('i').className =
                    isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            });

            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(passwordOutput.value);
                    statusOutput.textContent = 'Contraseña copiada al portapapeles.';
                } catch (error) {
                    statusOutput.textContent = 'No se pudo copiar. Selecciona y copia la contraseña manualmente.';
                }
            });
        });
    </script>
@endpush
