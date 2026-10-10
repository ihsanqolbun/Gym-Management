<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrivateSessionRequest;
use App\Http\Requests\UpdatePrivateSessionRequest;
use App\Http\Resources\PrivateSessionResource;
use App\Models\PrivateSession;
use App\Services\PrivateSessionPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PrivateSessionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PrivateSession::class);

        $user = $request->user();
        $query = PrivateSession::with(['user', 'coach']);

        if ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        return PrivateSessionResource::collection($query->latest()->paginate(10));
    }

    public function show(PrivateSession $privateSession): PrivateSessionResource
    {
        $this->authorize('view', $privateSession);

        return new PrivateSessionResource($privateSession->load(['user', 'coach']));
    }

    public function store(StorePrivateSessionRequest $request, PrivateSessionPricing $pricing): PrivateSessionResource
    {
        $this->authorize('create', PrivateSession::class);

        $data = $request->validated();

        if ($request->user()->role === 'member') {
            $data['user_id'] = $request->user()->id;
        }

        try {
            $result = $pricing->resolve($data['start_time']);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'start_time' => $e->getMessage(),
            ]);
        }

        $session = PrivateSession::create([
            'user_id' => $data['user_id'],
            'coach_id' => $data['coach_id'] ?? null,
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'time_slot' => $result['time_slot'],
            'location_type' => $data['location_type'],
            'address' => $data['location_type'] === 'rumah' ? $data['address'] : null,
            'price' => $result['price'],
            'status' => 'requested',
        ]);

        return new PrivateSessionResource($session->load(['user', 'coach']));
    }

    public function update(UpdatePrivateSessionRequest $request, PrivateSession $privateSession): PrivateSessionResource
    {
        $this->authorize('update', $privateSession);

        $privateSession->update($request->validated());

        return new PrivateSessionResource($privateSession->load(['user', 'coach']));
    }

    public function cancel(PrivateSession $privateSession): PrivateSessionResource
    {
        $this->authorize('cancel', $privateSession);

        $privateSession->update(['status' => 'cancelled']);

        return new PrivateSessionResource($privateSession->load(['user', 'coach']));
    }

    public function destroy(PrivateSession $privateSession): JsonResponse
    {
        $this->authorize('delete', $privateSession);

        $privateSession->delete();

        return response()->json(null, 204);
    }
}