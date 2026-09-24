<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coach_id' => 'sometimes|nullable|exists:coaches,id',
            'class_name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|in:beginner,intermediate,advanced',
            'day_of_week' => 'sometimes|required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'capacity' => 'sometimes|required|integer|min:1',
            'location' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}