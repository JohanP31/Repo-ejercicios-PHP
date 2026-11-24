<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(array_keys(Recipe::CATEGORIES)),
            'ingredients' => "2 tomates\n1 cebolla\n1 cucharada de aceite",
            'instructions' => fake()->paragraphs(3, true),
            'prep_time' => fake()->numberBetween(10, 90),
            'servings' => fake()->numberBetween(1, 8),
        ];
    }
}
