<?php

namespace Tests\Feature;

use Tests\TestCase;

class StopwatchTest extends TestCase
{
    public function test_stopwatch_is_public_with_only_three_controls(): void
    {
        $this->get(route('stopwatch.index'))
            ->assertOk()
            ->assertSee('Cronómetro')
            ->assertSee('id="stopwatch-start"', false)
            ->assertSee('id="stopwatch-pause"', false)
            ->assertSee('id="stopwatch-reset"', false)
            ->assertDontSee('Vuelta');
    }
}
