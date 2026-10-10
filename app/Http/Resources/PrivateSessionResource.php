<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivateSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_date' => $this->session_date,
            'start_time' => $this->start_time,
            'time_slot' => $this->time_slot,
            'location_type' => $this->location_type,
            'address' => $this->address,
            'price' => $this->price,
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'user' => new UserResource($this->whenLoaded('user')),
            'coach' => new CoachResource($this->whenLoaded('coach')),
        ];
    }
}
