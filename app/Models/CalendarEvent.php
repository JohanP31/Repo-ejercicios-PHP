<?php

namespace App\Models;

use Database\Factories\CalendarEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    public const REMINDER_OPTIONS = [
        5 => '5 minutos antes',
        15 => '15 minutos antes',
        30 => '30 minutos antes',
        60 => '1 hora antes',
        1440 => '1 día antes',
    ];

    /** @use HasFactory<CalendarEventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'reminder_minutes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reminded_at' => 'datetime',
            'reminder_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
