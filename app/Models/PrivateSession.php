<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_id',
        'session_date',
        'start_time',
        'time_slot',
        'location_type',
        'address',
        'price',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}