<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_schedule_id' => 'sometimes|nullable|exists:class_schedules,id',
            'booking_date' => 'sometimes|nullable|date|after_or_equal:today',
            'status' => 'sometimes|nullable|in:booked,cancelled,attended',
            'notes' => 'nullable|string|max:255',
        ];
    }
}