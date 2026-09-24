<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Booking::class);

        $user = $request->user();

        $query = Booking::query()->with(['user', 'classSchedule.coach']);

        if ($user->role === 'member') {
            $query->where('user_id', $user->id);
        }

        $bookings = $query->latest()->paginate(10);

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $this->authorize('create', Booking::class);

        $validated = $request->validated();

        if ($request->user()->role === 'member') {
            $validated['user_id'] = $request->user()->id;
        }

        $validated['status'] = 'booked';

        $booking = Booking::create($validated);

        return new BookingResource($booking->load(['user', 'classSchedule.coach']));
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        return new BookingResource($booking->load(['user', 'classSchedule.coach']));
    }

    public function update(UpdateBookingRequest $request, Booking $booking): BookingResource
    {
        $this->authorize('update', $booking);

        $booking->update($request->validated());

        return new BookingResource($booking->load(['user', 'classSchedule.coach']));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);

        $booking->delete();

        return response()->json(null, 204);
    }

    public function cancel(Booking $booking): JsonResponse|BookingResource
    {
        if (auth()->user()->role === 'member' && $booking->user_id === auth()->id() && ! $booking->isWithinCancellationWindow()) {
            return response()->json([
                'message' => 'Cancellation failed. Member can only cancel at least '.Booking::CANCELLATION_WINDOW_HOURS.' hours before class start time.',
            ], 422);
        }

        $this->authorize('cancel', $booking);

        $booking->update(['status' => 'cancelled']);

        return new BookingResource($booking->load(['user', 'classSchedule.coach']));
    }
}