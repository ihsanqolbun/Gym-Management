<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrivateSessionRequest extends FormRequest
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
        return [
            'status' => 'sometimes|required|in:approved,rejected',
            'coach_id' => 'sometimes|nullable|exists:coaches,id',
            'admin_note' => 'sometimes|nullable|string|max:255',
        ];
    }
}
