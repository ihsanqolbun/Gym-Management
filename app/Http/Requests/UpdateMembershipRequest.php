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
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'type' => 'sometimes|required|in:harian,mingguan,bulanan',
            'price' => 'sometimes|required|numeric|min:0',
            'payment_status' => 'sometimes|required|in:pending,paid',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'status' => 'sometimes|required|in:active,expired',
        ];
    }
}