<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coach_id' => 'nullable|exists:coaches,id',
            'class_name' => 'required|string|max:255',
            'level' => 'required|in:beginner,intermediate,advanced',
            'day_of_week' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}