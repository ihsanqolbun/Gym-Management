<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Requests\UpdateMembershipRequest;
use App\Http\Resources\MembershipResource;
use App\Models\Membership;
use App\Models\User;
use App\Services\MembershipPricing;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class MembershipController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Membership::class);

        $user = auth()->user();

        if (in_array($user->role, ['developer', 'owner', 'admin'])) {
            $memberships = Membership::with('user')->latest()->paginate(10);
        } else {
            $memberships = Membership::with('user')->where('user_id', $user->id)->latest()->paginate(10);
        }

        return MembershipResource::collection($memberships);
    }

    public function show(Membership $membership): MembershipResource
    {
        $this->authorize('view', $membership);

        return new MembershipResource($membership->load('user'));
    }

    public function store(StoreMembershipRequest $request, MembershipPricing $pricing): MembershipResource
    {
        $this->authorize('create', Membership::class);

        $data = $request->validated();

        if ($request->user()->role === 'member') {
            $today = now('Asia/Jakarta');

            $data['user_id'] = $request->user()->id;
            $data['start_date'] = $today->toDateString();
            $data['end_date'] = $data['package'] === 'visit_1x'
                ? $today->copy()->addDay()->toDateString()
                : $today->copy()->addMonth()->toDateString();
            $data['payment_status'] = 'pending';
            $data['status'] = 'active';
        }

        $member = User::findOrFail($data['user_id']);

        if (! $member->date_of_birth) {
            throw ValidationException::withMessages([
                'user_id' => 'Member ini belum punya tanggal lahir.',
            ]);
        }

        try {
            $result = $pricing->resolve(
                $member->date_of_birth,
                Carbon::parse($data['start_date']),
                $data['package'],
                $data['private_category'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'package' => $e->getMessage(),
            ]);
        }

        $isFirstMembership = ! Membership::where('user_id', $data['user_id'])->exists();
        
        $membership = Membership::create([
            'user_id' => $data['user_id'],
            'type' => $data['package'] === 'visit_1x' ? 'harian' : 'bulanan',
            'category' => $result['category'],
            'package' => $data['package'],
            'price' => $result['price'],
            'register_fee' => $isFirstMembership ? config('pricelist.register_fee') : 0,
            'payment_status' => $data['payment_status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
        ]);

        return new MembershipResource($membership->load('user'));
    }

    public function update(UpdateMembershipRequest $request, Membership $membership): MembershipResource
    {
        $this->authorize('update', $membership);

        $membership->update($request->validated());

        return new MembershipResource($membership->load('user'));
    }

    public function destroy(Membership $membership): JsonResponse
    {
        $this->authorize('delete', $membership);

        $membership->delete();

        return response()->json(null, 204);
    }
}