<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Requests\UpdateMembershipRequest;
use App\Http\Resources\MembershipResource;
use App\Models\Membership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

    public function store(StoreMembershipRequest $request): MembershipResource
    {
        $this->authorize('create', Membership::class);

        $membership = Membership::create($request->validated());

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