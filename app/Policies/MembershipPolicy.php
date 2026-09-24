<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['developer', 'owner', 'admin', 'member']);
    }

    public function view(User $user, Membership $membership): bool
    {
        if (in_array($user->role, ['developer', 'owner', 'admin'])) {
            return true;
        }

        return $user->id === $membership->user_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['developer', 'admin']);
    }

    public function update(User $user, Membership $membership): bool
    {
        return in_array($user->role, ['developer', 'admin']);
    }

    public function delete(User $user, Membership $membership): bool
    {
        return in_array($user->role, ['developer', 'admin']);
    }
}