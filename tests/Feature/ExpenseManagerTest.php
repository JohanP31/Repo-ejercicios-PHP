<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_expenses(): void
    {
        $this->get(route('expenses.index'))
            ->assertRedirect(route('login'));

        $this->post(route('expenses.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_expense_monthly_summary(): void
    {
        $user = $this->createUser('Content Manager');

        $this->actingAs($user)
            ->post(route('expenses.store'), [
                'description' => 'Compra de mercado',
                'amount' => '125.50',
                'category' => 'food',
                'spent_at' => '2026-11-12',
            ])
            ->assertRedirect(route('expenses.index'));

        $this->post(route('expenses.store'), [
            'description' => 'Medicinas',
            'amount' => '25.25',
            'category' => 'health',
            'spent_at' => '2026-11-18',
        ])->assertRedirect(route('expenses.index'));

        $this->post(route('expenses.store'), [
            'description' => 'Gasto del mes anterior',
            'amount' => '99.00',
            'category' => 'transport',
            'spent_at' => '2026-10-31',
        ])->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => 'Compra de mercado',
            'amount' => '125.50',
            'category' => 'food',
        ]);

        $this->get(route('expenses.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('Compra de mercado')
            ->assertSee('Medicinas')
            ->assertSee('Alimentación')
            ->assertSee('Salud')
            ->assertSee('150,75')
            ->assertDontSee('Gasto del mes anterior');
    }

    public function test_expense_isolation(): void
    {
        $owner = $this->createUser('Administrador');
        $otherUser = $this->createUser('Content Manager');
        $expense = $owner->expenses()->create([
            'description' => 'Gasto privado',
            'amount' => '50.00',
            'category' => 'other',
            'spent_at' => '2026-11-10',
        ]);

        $this->actingAs($otherUser)
            ->get(route('expenses.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertDontSee('Gasto privado');

        $this->delete(route('expenses.destroy', $expense->id))
            ->assertNotFound();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'user_id' => $owner->id]);
    }

    public function test_expense_delete(): void
    {
        $user = $this->createUser('Content Manager');
        $expense = $user->expenses()->create([
            'description' => 'Gasto para eliminar',
            'amount' => '15.00',
            'category' => 'other',
            'spent_at' => '2026-11-10',
        ]);

        $this->actingAs($user)
            ->delete(route('expenses.destroy', $expense->id))
            ->assertRedirect(route('expenses.index'));

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expense_escaping(): void
    {
        $user = $this->createUser('Content Manager');
        $description = "<script>alert('xss')</script>";

        $this->actingAs($user)
            ->post(route('expenses.store'), [
                'description' => $description,
                'amount' => '10.00',
                'category' => 'other',
                'spent_at' => '2026-11-10',
            ])
            ->assertRedirect(route('expenses.index'));

        $this->get(route('expenses.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee($description)
            ->assertDontSee($description, false);
    }

    public function test_month_validation(): void
    {
        $user = $this->createUser('Content Manager');

        $this->actingAs($user)
            ->get(route('expenses.index', ['month' => '2026-99']))
            ->assertSessionHasErrors('month');
    }

    public function test_expense_validation(): void
    {
        $user = $this->createUser('Content Manager');
        $this->travelTo(Carbon::parse('2026-11-22 12:00:00'));

        $this->actingAs($user)
            ->from(route('expenses.index'))
            ->post(route('expenses.store'), [
                'description' => 'Gasto inválido',
                'amount' => '0.00',
                'category' => 'invalid',
                'spent_at' => '2026-11-23',
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors(['amount', 'category', 'spent_at']);

        $this->assertDatabaseCount('expenses', 0);
    }

    private function createUser(string $roleName): User
    {
        $role = new Role;
        $role->name = $roleName;
        $role->save();

        $user = User::factory()->make();
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }
}
