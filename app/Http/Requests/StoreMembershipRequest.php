<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->user()->role === 'member') {
            return [
                'package' => 'required|in:visit_1x,visit_10x,all_day_month',
            ];
        }
    
        return [
            'user_id' => 'required|exists:users,id',
            'package' => 'required|in:visit_1x,visit_10x,all_day_month',
            'private_category' => 'nullable|in:private_siang,private_malam',
            'payment_status' => 'required|in:pending,paid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,expired',
        ];
    }
}