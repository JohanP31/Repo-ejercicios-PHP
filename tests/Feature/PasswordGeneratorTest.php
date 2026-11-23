<?php

namespace Tests\Feature;

use Tests\TestCase;

class PasswordGeneratorTest extends TestCase
{
    public function test_password_generator_is_available_without_authentication(): void
    {
        $this->get(route('passwords.index'))
            ->assertOk()
            ->assertSee('Generador de contraseñas seguras')
            ->assertSee('Longitud:')
            ->assertSee('Tipos de caracteres')
            ->assertSee('Minúsculas (a-z)')
            ->assertSee('Mayúsculas (A-Z)')
            ->assertSee('Números (0-9)')
            ->assertSee('Símbolos (!@#$...)')
            ->assertSee('Generar contraseña')
            ->assertSee('no se envía al servidor');
    }
}
