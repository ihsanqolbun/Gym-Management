<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['developer', 'owner', 'admin', 'member']);
    }

    public function view(User $user, Booking $booking): bool
    {
        if (in_array($user->role, ['developer', 'owner', 'admin'])) {
            return true;
        }

        return $user->role === 'member' && $user->id === $booking->user_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['developer', 'admin', 'member']);
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->role === 'developer' || $user->role === 'admin';
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->role === 'developer' || $user->role === 'admin';
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role === 'developer' || $user->role === 'admin') {
            return true;
        }

        if ($user->role === 'member') {
            return $booking->user_id === $user->id && $this->isWithinCancellationWindow($booking);
        }

        return false;
    }

    protected function isWithinCancellationWindow(Booking $booking): bool
    {
        return $booking->isWithinCancellationWindow();
    }
}