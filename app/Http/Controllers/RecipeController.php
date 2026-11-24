<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing(
            $request,
            Recipe::query(),
            'Recetas de la comunidad',
            'No hay recetas que coincidan. Prueba con otros filtros o publica una receta.',
            true,
        );
    }

    public function mine(Request $request): View
    {
        return $this->listing(
            $request,
            $request->user()->recipes()->getQuery(),
            'Mis recetas',
            'Todavía no has publicado recetas.',
            false,
        );
    }

    public function favorites(Request $request): View
    {
        return $this->listing(
            $request,
            $request->user()->favoriteRecipes()->getQuery(),
            'Mis favoritas',
            'Todavía no has guardado recetas favoritas.',
            false,
        );
    }

    public function create(): View
    {
        return view('recipes.create', [
            'recipe' => new Recipe,
            'categories' => Recipe::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedRecipe($request);
        $validated['user_id'] = $request->user()?->id;
        $recipe = Recipe::query()->create($validated);

        return redirect()->route('recipes.show', $recipe)->with('message', [
            'content' => 'Receta publicada.',
            'type' => 'success',
        ]);
    }

    public function show(Request $request, Recipe $recipe): View
    {
        return view('recipes.show', [
            'recipe' => $recipe->load('user'),
            'isFavorite' => $request->user()
                ? $request->user()->favoriteRecipes()->where('recipes.id', $recipe->id)->exists()
                : false,
        ]);
    }

    public function edit(Request $request, Recipe $recipe): View
    {
        $recipe = $request->user()->recipes()->findOrFail($recipe->id);

        return view('recipes.edit', [
            'recipe' => $recipe,
            'categories' => Recipe::CATEGORIES,
        ]);
    }

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $recipe = $request->user()->recipes()->findOrFail($recipe->id);
        $recipe->update($this->validatedRecipe($request));

        return redirect()->route('recipes.show', $recipe)->with('message', [
            'content' => 'Receta actualizada.',
            'type' => 'success',
        ]);
    }

    public function destroy(Request $request, Recipe $recipe): RedirectResponse
    {
        $request->user()->recipes()->findOrFail($recipe->id)->delete();

        return redirect()->route('recipes.index')->with('message', [
            'content' => 'Receta eliminada.',
            'type' => 'success',
        ]);
    }

    public function favorite(Request $request, Recipe $recipe): RedirectResponse
    {
        $request->user()->favoriteRecipes()->syncWithoutDetaching([$recipe->id]);

        return redirect()->route('recipes.show', $recipe)->with('message', [
            'content' => 'Receta guardada en tus favoritas.',
            'type' => 'success',
        ]);
    }

    public function unfavorite(Request $request, Recipe $recipe): RedirectResponse
    {
        $request->user()->favoriteRecipes()->detach($recipe->id);

        return redirect()->route('recipes.show', $recipe)->with('message', [
            'content' => 'Receta eliminada de tus favoritas.',
            'type' => 'success',
        ]);
    }

    /**
     * @param  Builder<Recipe>  $query
     */
    private function listing(
        Request $request,
        Builder $query,
        string $heading,
        string $emptyMessage,
        bool $showFilters,
    ): View {
        $filters = $showFilters
            ? $request->validate([
                'search' => ['nullable', 'string', 'max:100'],
                'category' => ['nullable', Rule::in(array_keys(Recipe::CATEGORIES))],
                'ingredient' => ['nullable', 'string', 'max:100'],
            ])
            : [];

        $recipes = $query
            ->when(isset($filters['search']) && $filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('ingredients', 'like', "%{$search}%")
                        ->orWhere('instructions', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $category): void {
                $query->where('category', $category);
            })
            ->when($filters['ingredient'] ?? null, function (Builder $query, string $ingredient): void {
                $query->where('ingredients', 'like', "%{$ingredient}%");
            })
            ->with('user')
            ->orderByDesc('recipes.created_at')
            ->orderByDesc('recipes.id')
            ->paginate(12)
            ->withQueryString();

        $favoriteIds = $request->user() && $recipes->isNotEmpty()
            ? $request->user()->favoriteRecipes()
                ->whereIn('recipes.id', $recipes->getCollection()->modelKeys())
                ->pluck('recipes.id')
                ->all()
            : [];

        return view('recipes.index', [
            'recipes' => $recipes,
            'categories' => Recipe::CATEGORIES,
            'filters' => $filters,
            'favoriteIds' => $favoriteIds,
            'heading' => $heading,
            'emptyMessage' => $emptyMessage,
            'showFilters' => $showFilters,
        ]);
    }

    /**
     * @return array{title: string, description: ?string, category: string, ingredients: string, instructions: string, prep_time: int|string, servings: int|string}
     */
    private function validatedRecipe(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', Rule::in(array_keys(Recipe::CATEGORIES))],
            'ingredients' => ['required', 'string', 'max:5000'],
            'instructions' => ['required', 'string', 'max:10000'],
            'prep_time' => ['required', 'integer', 'min:1', 'max:1440'],
            'servings' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'title.required' => 'Escribe el nombre de la receta.',
            'category.required' => 'Selecciona un tipo de comida.',
            'ingredients.required' => 'Añade al menos un ingrediente.',
            'instructions.required' => 'Escribe los pasos de preparación.',
            'prep_time.min' => 'El tiempo debe ser de al menos un minuto.',
            'servings.min' => 'Indica al menos una porción.',
        ]);

        $validated['ingredients'] = implode("\n", array_values(array_filter(
            array_map('trim', preg_split('/\R/', $validated['ingredients'])),
            fn (string $ingredient): bool => $ingredient !== '',
        )));

        return $validated;
    }
}
