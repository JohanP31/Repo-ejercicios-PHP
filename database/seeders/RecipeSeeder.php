<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = [
            [
                'title' => 'Tortitas de avena y plátano',
                'description' => 'Un desayuno sencillo y rápido.',
                'category' => 'breakfast',
                'ingredients' => "1 plátano\n1 huevo\n4 cucharadas de avena\nCanela al gusto",
                'instructions' => "Tritura el plátano y mézclalo con el huevo.\nAñade la avena y la canela.\nCocina porciones pequeñas en una sartén caliente.",
                'prep_time' => 15,
                'servings' => 2,
            ],
            [
                'title' => 'Ensalada de garbanzos',
                'description' => 'Una ensalada fresca para el almuerzo.',
                'category' => 'lunch',
                'ingredients' => "1 taza de garbanzos cocidos\n1 tomate\n1 pepino\nLimón y aceite de oliva",
                'instructions' => "Corta el tomate y el pepino en cubos.\nMezcla con los garbanzos.\nAliña con limón y aceite de oliva.",
                'prep_time' => 15,
                'servings' => 2,
            ],
            [
                'title' => 'Pasta con verduras',
                'description' => 'Una cena colorida con verduras salteadas.',
                'category' => 'dinner',
                'ingredients' => "200 g de pasta\n1 calabacín\n1 pimiento\n2 cucharadas de aceite de oliva",
                'instructions' => "Cuece la pasta según las indicaciones del paquete.\nCorta y saltea las verduras.\nMezcla la pasta con las verduras y sirve.",
                'prep_time' => 25,
                'servings' => 2,
            ],
        ];

        foreach ($recipes as $recipe) {
            Recipe::query()->firstOrCreate(
                ['user_id' => null, 'title' => $recipe['title']],
                $recipe,
            );
        }
    }
}
