<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const CATEGORIES = [
        'food' => 'Alimentación',
        'transport' => 'Transporte',
        'housing' => 'Vivienda',
        'health' => 'Salud',
        'education' => 'Educación',
        'entertainment' => 'Entretenimiento',
        'other' => 'Otros',
    ];

    protected $fillable = ['description', 'amount', 'category', 'spent_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
