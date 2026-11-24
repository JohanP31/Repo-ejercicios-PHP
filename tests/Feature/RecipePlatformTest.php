<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipePlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_publish_and_share_recipes(): void
    {
        $existingUser = $this->createUser();

        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('Recetas para compartir')
            ->assertSee('ingredient');

        $this->get(route('recipes.create'))
            ->assertOk()
            ->assertSee('Publicar receta');

        $this->post(route('recipes.store'), [
            ...$this->recipeData(),
            'user_id' => $existingUser->id,
        ])->assertRedirect(route('recipes.show', Recipe::query()->firstOrFail()));

        $recipe = Recipe::query()->firstOrFail();
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'user_id' => null,
            'title' => 'Sopa de verduras',
        ]);

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('Enlace público para compartir')
            ->assertSee(route('recipes.show', $recipe));

        $this->get(route('recipes.edit', $recipe))
            ->assertRedirect(route('login'));

        $this->put(route('recipes.update', $recipe), $this->recipeData())
            ->assertRedirect(route('login'));

        $this->delete(route('recipes.destroy', $recipe))
            ->assertRedirect(route('login'));

        $this->post(route('recipes.favorite', $recipe))
            ->assertRedirect(route('login'));

        $this->get(route('recipes.mine'))
            ->assertRedirect(route('login'));

        $this->get(route('recipes.favorites'))
            ->assertRedirect(route('login'));
    }

    public function test_recipes_can_be_filtered_by_search_type_and_ingredient(): void
    {
        Recipe::factory()->create([
            'title' => 'Galletas de avena',
            'category' => 'dessert',
            'ingredients' => "avena\nmiel\nhuevo",
        ]);
        Recipe::factory()->create([
            'title' => 'Pasta con verduras',
            'category' => 'dinner',
            'ingredients' => "pasta\ntomate",
        ]);

        $this->get(route('recipes.index', [
            'search' => 'avena',
            'category' => 'dessert',
            'ingredient' => 'miel',
        ]))
            ->assertOk()
            ->assertSee('Galletas de avena')
            ->assertDontSee('Pasta con verduras');
    }

    public function test_user_can_manage_own_recipes_and_save_favorites(): void
    {
        $user = $this->createUser();
        $communityRecipe = Recipe::factory()->create([
            'title' => 'Receta de la comunidad',
        ]);

        $this->actingAs($user)
            ->post(route('recipes.store'), [
                ...$this->recipeData(),
                'user_id' => $communityRecipe->user_id,
            ])
            ->assertRedirect(route('recipes.show', Recipe::query()->where('title', 'Sopa de verduras')->firstOrFail()));

        $ownRecipe = Recipe::query()->where('title', 'Sopa de verduras')->firstOrFail();
        $this->assertDatabaseHas('recipes', [
            'id' => $ownRecipe->id,
            'user_id' => $user->id,
        ]);

        $this->get(route('recipes.mine'))
            ->assertOk()
            ->assertSee('Sopa de verduras');

        $this->post(route('recipes.favorite', $communityRecipe))
            ->assertRedirect(route('recipes.show', $communityRecipe));

        $this->assertDatabaseHas('recipe_user', [
            'user_id' => $user->id,
            'recipe_id' => $communityRecipe->id,
        ]);

        $this->get(route('recipes.favorites'))
            ->assertOk()
            ->assertSee('Receta de la comunidad');

        $this->delete(route('recipes.unfavorite', $communityRecipe))
            ->assertRedirect(route('recipes.show', $communityRecipe));

        $this->assertDatabaseMissing('recipe_user', [
            'user_id' => $user->id,
            'recipe_id' => $communityRecipe->id,
        ]);

        $this->put(route('recipes.update', $ownRecipe), [
            ...$this->recipeData(),
            'title' => 'Sopa casera',
        ])->assertRedirect(route('recipes.show', $ownRecipe));

        $this->assertDatabaseHas('recipes', [
            'id' => $ownRecipe->id,
            'title' => 'Sopa casera',
            'user_id' => $user->id,
        ]);

        $this->delete(route('recipes.destroy', $ownRecipe))
            ->assertRedirect(route('recipes.index'));

        $this->assertDatabaseMissing('recipes', ['id' => $ownRecipe->id]);
    }

    public function test_users_cannot_manage_another_users_recipes(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $recipeData = $this->recipeData();
        $recipeData['ingredients'] = "2 tomates\n1 zanahoria\n1 cebolla";
        $recipe = $owner->recipes()->create($recipeData);

        $this->actingAs($otherUser)
            ->get(route('recipes.edit', $recipe))
            ->assertNotFound();

        $this->put(route('recipes.update', $recipe), $this->recipeData())
            ->assertNotFound();

        $this->delete(route('recipes.destroy', $recipe))
            ->assertNotFound();

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'user_id' => $owner->id,
            'title' => 'Sopa de verduras',
        ]);
    }

    public function test_recipe_and_filter_validation(): void
    {
        $this->from(route('recipes.create'))
            ->post(route('recipes.store'), [
                ...$this->recipeData(),
                'title' => '',
                'category' => 'private',
                'prep_time' => 0,
            ])
            ->assertRedirect(route('recipes.create'))
            ->assertSessionHasErrors(['title', 'category', 'prep_time']);

        $this->assertDatabaseCount('recipes', 0);

        $this->get(route('recipes.index', ['category' => 'invalid']))
            ->assertSessionHasErrors('category');
    }

    public function test_recipe_content_is_escaped(): void
    {
        $script = '<script>alert(1)</script>';

        $this->post(route('recipes.store'), [
            ...$this->recipeData(),
            'title' => $script,
            'description' => $script,
            'ingredients' => $script,
        ])->assertRedirect(route('recipes.show', Recipe::query()->firstOrFail()));

        $recipe = Recipe::query()->firstOrFail();

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee($script)
            ->assertDontSee($script, false);
    }

    public function test_recipe_seeder_creates_sample_recipes_once(): void
    {
        $this->seed(RecipeSeeder::class);
        $this->seed(RecipeSeeder::class);

        $this->assertDatabaseCount('recipes', 3);
        $this->assertDatabaseHas('recipes', [
            'title' => 'Tortitas de avena y plátano',
            'category' => 'breakfast',
        ]);
    }

    /**
     * @return array{title: string, description: string, category: string, ingredients: string, instructions: string, prep_time: int, servings: int}
     */
    private function recipeData(): array
    {
        return [
            'title' => 'Sopa de verduras',
            'description' => 'Una sopa sencilla para compartir.',
            'category' => 'lunch',
            'ingredients' => "2 tomates\n1 zanahoria\n1 cebolla",
            'instructions' => "Lava y corta las verduras.\nCocínalas en una olla con agua.",
            'prep_time' => 35,
            'servings' => 4,
        ];
    }

    private function createUser(): User
    {
        $role = new Role;
        $role->name = 'Recipe User';
        $role->save();

        $user = User::factory()->make();
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }
}
