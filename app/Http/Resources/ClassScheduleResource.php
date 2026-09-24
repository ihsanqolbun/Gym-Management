<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_name' => $this->class_name,
            'level' => $this->level,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'capacity' => $this->capacity,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'coach' => new CoachResource($this->whenLoaded('coach')),
        ];
    }
}