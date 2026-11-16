<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Membership;
use App\Models\PrivateSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // Carbon dayOfWeekIso: 1 = Senin ... 7 = Minggu
    private const DAYS = [
        1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis',
        5 => 'jumat', 6 => 'sabtu', 7 => 'minggu',
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // "Hari ini" dihitung WIB, app sendiri jalan di UTC
        $today = now('Asia/Jakarta');

        $data = $user->role === 'member'
            ? $this->memberSummary($user, $today)
            : $this->staffSummary($today);

        return response()->json(['data' => $data]);
    }

    private function staffSummary(Carbon $today): array
    {
        $date = $today->toDateString();

        $classesToday = ClassSchedule::where('is_active', true)
            ->where('day_of_week', self::DAYS[$today->dayOfWeekIso])
            ->with('coach')
            ->orderBy('start_time')
            ->get()
            ->map(fn (ClassSchedule $s) => [
                'id' => $s->id,
                'class_name' => $s->class_name,
                'start_time' => substr($s->start_time, 0, 5),
                'end_time' => substr($s->end_time, 0, 5),
                'coach' => $s->coach?->name, // coach bisa null (nullOnDelete)
                'capacity' => $s->capacity,
                'available_slots' => $s->availableSlots($date),
            ]);

        return [
            'role_view' => 'staff',
            'date' => $date,
            'classes_today' => $classesToday,
            'active_memberships' => Membership::where('status', 'active')
                ->whereDate('end_date', '>=', $date)
                ->count(),
            'pending_payments' => Membership::where('payment_status', 'pending')->count(),
            'pending_private_sessions' => PrivateSession::where('status', 'requested')->count(),
        ];
    }

    private function memberSummary(User $user, Carbon $today): array
    {
        $date = $today->toDateString();

        $membership = $user->memberships()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $date)
            ->latest('end_date')
            ->first();

        $upcomingBookings = $user->bookings()
            ->with('classSchedule')
            ->where('status', 'booked')
            ->whereDate('booking_date', '>=', $date)
            ->orderBy('booking_date')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'booking_date' => $b->booking_date->toDateString(),
                'class_name' => $b->classSchedule->class_name,
                'start_time' => substr($b->classSchedule->start_time, 0, 5),
            ]);

        $upcomingPrivateSessions = PrivateSession::where('user_id', $user->id)
            ->whereIn('status', ['requested', 'approved'])
            ->whereDate('session_date', '>=', $date)
            ->orderBy('session_date')
            ->limit(5)
            ->get()
            ->map(fn (PrivateSession $s) => [
                'id' => $s->id,
                'session_date' => $s->session_date->toDateString(),
                'start_time' => substr($s->start_time, 0, 5),
                'status' => $s->status,
            ]);

        return [
            'role_view' => 'member',
            'date' => $date,
            'membership' => $membership ? [
                'id' => $membership->id,
                'package' => $membership->package,
                'category' => $membership->category,
                'end_date' => $membership->end_date->toDateString(),
                'payment_status' => $membership->payment_status,
            ] : null,
            'upcoming_bookings' => $upcomingBookings,
            'upcoming_private_sessions' => $upcomingPrivateSessions,
        ];
    }
}