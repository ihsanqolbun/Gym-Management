<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicClassScheduleResource;
use App\Models\ClassSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicController extends Controller
{
    public function classSchedules(): AnonymousResourceCollection
    {
        $schedules = ClassSchedule::with('coach')
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        return PublicClassScheduleResource::collection($schedules);
    }

    public function pricelist(): JsonResponse
    {
        return response()->json([
            'data' => [
                'register_fee' => config('pricelist.register_fee'),
                'prices' => config('pricelist.prices'),
            ],
        ]);
    }
}