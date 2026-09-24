<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCoachRequest;
use App\Http\Requests\Api\UpdateCoachRequest;
use App\Http\Resources\CoachResource;
use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CoachController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $coaches = Coach::latest()->paginate(10);

        return CoachResource::collection($coaches);
    }

    public function store(StoreCoachRequest $request): CoachResource
    {
        $coach = Coach::create($request->validated());

        return new CoachResource($coach);
    }

    public function show(Coach $coach): CoachResource
    {
        return new CoachResource($coach);
    }

    public function update(UpdateCoachRequest $request, Coach $coach): CoachResource
    {
        $coach->update($request->validated());

        return new CoachResource($coach);
    }

    public function destroy(Coach $coach): JsonResponse
    {
        $coach->delete();

        return response()->json(null, 204);
    }
}