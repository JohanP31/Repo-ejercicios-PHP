<?php

namespace Tests\Feature;

use Tests\TestCase;

class TipCalculatorTest extends TestCase
{
    public function test_tip_calculator_public(): void
    {
        $this->get(route('tips.index'))
            ->assertOk()
            ->assertSee('Calculadora de propinas')
            ->assertSee('Monto total de la cuenta')
            ->assertSee('Porcentaje de propina')
            ->assertSee('Total con propina');
    }
}
