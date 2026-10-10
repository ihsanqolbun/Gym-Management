<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $startDate = $this->route('membership')->start_date->toDateString();
    
        return [
            'payment_status' => 'sometimes|required|in:pending,paid',
            'status' => 'sometimes|required|in:active,expired',
            'end_date' => "sometimes|required|date|after:{$startDate}",
        ];
    }
}