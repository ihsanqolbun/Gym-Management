<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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

    public const CANCELLATION_WINDOW_HOURS = 4;

// Waktu mulai sesi sebagai momen absolut, dibaca sebagai WIB (jam di DB adalah jam gym)
    public function startsAt(): Carbon
    {
        return Carbon::parse(
            $this->session_date->toDateString().' '.$this->start_time,
            'Asia/Jakarta'
        );
    }

    public function isWithinCancellationWindow(): bool
    {
        return now()->lte($this->startsAt()->subHours(self::CANCELLATION_WINDOW_HOURS));
    }
}