<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePrivateSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'location_type' => 'required|in:gym,rumah',
            'address' => 'required_if:location_type,rumah|nullable|string|max:255',
            'coach_id' => 'nullable|exists:coaches,id',
        ];

        if ($this->user()->role !== 'member') {
            $rules['user_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
