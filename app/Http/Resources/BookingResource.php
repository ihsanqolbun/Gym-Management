<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_date' => $this->booking_date,
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'class_schedule' => new ClassScheduleResource($this->whenLoaded('classSchedule')),
        ];
    }
}