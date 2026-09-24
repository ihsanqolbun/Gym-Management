<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassScheduleRequest;
use App\Http\Requests\UpdateClassScheduleRequest;
use App\Http\Resources\ClassScheduleResource;
use App\Models\ClassSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassScheduleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $schedules = ClassSchedule::with('coach')->latest()->paginate(10);

        return ClassScheduleResource::collection($schedules);
    }

    public function show(ClassSchedule $classSchedule): ClassScheduleResource
    {
        return new ClassScheduleResource($classSchedule->load('coach'));
    }

    public function store(StoreClassScheduleRequest $request): ClassScheduleResource
    {
        $schedule = ClassSchedule::create($request->validated());

        return new ClassScheduleResource($schedule->load('coach'));
    }

    public function update(UpdateClassScheduleRequest $request, ClassSchedule $classSchedule): ClassScheduleResource
    {
        $classSchedule->update($request->validated());

        return new ClassScheduleResource($classSchedule->load('coach'));
    }

    public function destroy(ClassSchedule $classSchedule): JsonResponse
    {
        $classSchedule->delete();

        return response()->json(null, 204);
    }
}