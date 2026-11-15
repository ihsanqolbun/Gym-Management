<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\ClassSchedule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    // Carbon dayOfWeekIso: 1 = Senin ... 7 = Minggu
    private const DAYS = [
        1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis',
        5 => 'jumat', 6 => 'sabtu', 7 => 'minggu',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // member: user_id diisi server. Admin/developer: wajib pilih member.
            'user_id' => [
                Rule::requiredIf(fn () => $this->user()->role !== 'member'),
                'exists:users,id',
            ],
            'class_schedule_id' => 'required|exists:class_schedules,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Kalau rules dasar sudah gagal, jangan lanjut (data belum tentu valid)
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $schedule = ClassSchedule::find($this->input('class_schedule_id'));
                $date = $this->date('booking_date');

                if (! $schedule->is_active) {
                    $validator->errors()->add('class_schedule_id', 'This class schedule is not active.');
                    return;
                }

                if (self::DAYS[$date->dayOfWeekIso] !== $schedule->day_of_week) {
                    $validator->errors()->add('booking_date', 'Booking date does not match the class day ('.$schedule->day_of_week.').');
                    return;
                }

                $userId = $this->user()->role === 'member'
                    ? $this->user()->id
                    : $this->input('user_id');

                // Cek semua status, karena unique constraint DB juga nge-block booking yang sudah cancelled
                $alreadyBooked = Booking::where('user_id', $userId)
                    ->where('class_schedule_id', $schedule->id)
                    ->whereDate('booking_date', $date->toDateString())
                    ->exists();

                if ($alreadyBooked) {
                    $validator->errors()->add('booking_date', 'User already has a booking for this class on this date.');
                    return;
                }

                if ($schedule->availableSlots($date->toDateString()) <= 0) {
                    $validator->errors()->add('class_schedule_id', 'This class is full on the selected date.');
                }
            },
        ];
    }
}