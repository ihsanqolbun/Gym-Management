<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'class_schedule_id' => 'required|exists:class_schedules,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:255',
        ];
    }
}