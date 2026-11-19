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
            // Status hanya lewat /cancel dan /check-in, jadwal/tanggal lewat booking baru
            'notes' => 'sometimes|nullable|string|max:255',
        ];
    }
}