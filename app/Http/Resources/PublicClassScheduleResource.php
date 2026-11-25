<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicClassScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_name' => $this->class_name,
            'level' => $this->level,
            'day_of_week' => $this->day_of_week,
            'start_time' => substr($this->start_time, 0, 5), // HH:MM
            'end_time' => substr($this->end_time, 0, 5),
            'location' => $this->location,
            'coach_name' => $this->coach?->name, // cuma nama, tanpa email/HP
        ];
    }
}