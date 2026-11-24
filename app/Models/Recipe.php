<?php

namespace App\Models;

use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recipe extends Model
{
    public const CATEGORIES = [
        'breakfast' => 'Desayuno',
        'lunch' => 'Almuerzo',
        'dinner' => 'Cena',
        'dessert' => 'Postre',
        'snack' => 'Merienda',
        'drinks' => 'Bebidas',
        'other' => 'Otro',
    ];

    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'ingredients',
        'instructions',
        'prep_time',
        'servings',
    ];

    protected function casts(): array
    {
        return [
            'prep_time' => 'integer',
            'servings' => 'integer',
        ];
    }

    /**
     * @return list<string>
     */
    public function ingredientList(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $this->ingredients) ?: []),
            fn (string $ingredient): bool => $ingredient !== '',
        ));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
