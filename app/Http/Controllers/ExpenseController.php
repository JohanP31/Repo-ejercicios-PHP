<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = Carbon::parse($month.'-01')->startOfMonth()->toDateString();
        $monthEnd = Carbon::parse($month.'-01')->endOfMonth()->toDateString();
        $monthlyExpenses = $request->user()->expenses()
            ->whereBetween('spent_at', [$monthStart, $monthEnd]);

        return view('expenses.index', [
            'expenses' => (clone $monthlyExpenses)
                ->latest('spent_at')
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'summary' => (clone $monthlyExpenses)
                ->selectRaw('COUNT(*) as expense_count, COALESCE(SUM(amount), 0) as total')
                ->first(),
            'categoryTotals' => (clone $monthlyExpenses)
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->orderBy('category')
                ->get(),
            'categories' => Expense::CATEGORIES,
            'month' => $month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'spent_at' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'description.required' => 'Escribe una descripción para el gasto.',
            'amount.required' => 'Ingresa el monto del gasto.',
            'amount.min' => 'El monto debe ser mayor que cero.',
            'category.required' => 'Selecciona una categoría.',
            'category.in' => 'Selecciona una categoría válida.',
            'spent_at.required' => 'Selecciona la fecha del gasto.',
            'spent_at.before_or_equal' => 'La fecha del gasto no puede ser futura.',
        ]);

        $request->user()->expenses()->create($validated);

        return redirect()->route('expenses.index')->with('message', 'Gasto registrado.');
    }

    public function destroy(Request $request, int $expense): RedirectResponse
    {
        $request->user()->expenses()->findOrFail($expense)->delete();

        return redirect()->route('expenses.index')->with('message', 'Gasto eliminado.');
    }
}
