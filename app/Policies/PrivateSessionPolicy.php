<?php

namespace App\Policies;

use App\Models\PrivateSession;
use App\Models\User;

class PrivateSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PrivateSession $session): bool
    {
        return in_array($user->role, ['developer', 'owner', 'admin'])
            || $user->id === $session->user_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['developer', 'admin', 'member']);
    }

    public function update(User $user, PrivateSession $session): bool
    {
        return in_array($user->role, ['developer', 'admin']);
    }

    public function delete(User $user, PrivateSession $session): bool
    {
        return in_array($user->role, ['developer', 'admin']);
    }

    public function cancel(User $user, PrivateSession $session): bool
    {
        if (in_array($user->role, ['developer', 'admin'])) {
            return true;
        }

        return $user->role === 'member'
            && $user->id === $session->user_id
            && in_array($session->status, ['requested', 'approved']);
    }
}