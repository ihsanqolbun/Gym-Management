<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'class_name',
        'level',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Hitung sisa slot yang masih tersedia di tanggal tertentu.
     * Dihitung real-time dari data booking, bukan dari counter kolom.
     */
    public function availableSlots(string $date): int
    {
        $booked = $this->bookings()
            ->where('booking_date', $date)
            ->where('status', 'booked')
            ->count();

        return max(0, $this->capacity - $booked);
    }
}
