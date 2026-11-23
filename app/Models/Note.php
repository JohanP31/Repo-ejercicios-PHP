<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    public const CATEGORIES = [
        'personal' => 'Personal',
        'work' => 'Trabajo',
        'study' => 'Estudios',
        'ideas' => 'Ideas',
        'other' => 'Otros',
    ];

    protected $fillable = ['title', 'content', 'category'];
}
