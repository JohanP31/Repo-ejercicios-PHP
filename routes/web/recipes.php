<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/recipes/create', [RecipeController::class, 'create'])->name('recipes.create');
Route::post('/recipes', [RecipeController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('recipes.store');
Route::get('/recipes/mine', [RecipeController::class, 'mine'])
    ->middleware('auth')
    ->name('recipes.mine');
Route::get('/recipes/favorites', [RecipeController::class, 'favorites'])
    ->middleware('auth')
    ->name('recipes.favorites');
Route::get('/recipes/{recipe}', [RecipeController::class, 'show'])->name('recipes.show');

Route::middleware('auth')->group(function () {
    Route::get('/recipes/{recipe}/edit', [RecipeController::class, 'edit'])->name('recipes.edit');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
    Route::post('/recipes/{recipe}/favorite', [RecipeController::class, 'favorite'])->name('recipes.favorite');
    Route::delete('/recipes/{recipe}/favorite', [RecipeController::class, 'unfavorite'])->name('recipes.unfavorite');
});
