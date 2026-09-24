<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use HasFactory;

    public const CANCELLATION_WINDOW_HOURS = 4;

    protected $fillable = [
        'user_id',
        'class_schedule_id',
        'booking_date',
        'status',
        'checked_in_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'checked_in_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classSchedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function getClassDateTime(): ?Carbon
    {
        if (! $this->booking_date || ! $this->classSchedule?->start_time) {
            return null;
        }

        $date = $this->booking_date instanceof Carbon
            ? $this->booking_date->format('Y-m-d')
            : Carbon::parse($this->booking_date)->format('Y-m-d');

        return Carbon::parse($date.' '.$this->classSchedule->start_time);
    }

    public function isWithinCancellationWindow(): bool
    {
        $classDateTime = $this->getClassDateTime();

        if (! $classDateTime) {
            return false;
        }

        return now()->lte($classDateTime->copy()->subHours(self::CANCELLATION_WINDOW_HOURS));
    }
}
