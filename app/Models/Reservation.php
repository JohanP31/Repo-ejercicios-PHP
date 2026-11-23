<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    public const SERVICES = [
        'general_consultation' => 'Consulta general',
        'personal_advice' => 'Asesoría personalizada',
        'general_service' => 'Servicio general',
    ];

    protected $fillable = ['user_id', 'service', 'appointment_at'];

    protected function casts(): array
    {
        return [
            'appointment_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
