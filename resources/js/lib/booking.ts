import type { Booking } from '@/types';

export function canCancelBooking(booking: Booking, role: string | null): boolean {
  if (booking.status !== 'booked') {
    return false;
  }

  if (role === 'owner') {
    return false;
  }

  if (role === 'admin' || role === 'developer') {
    return true;
  }

  // member (atau role lain yang nggak dikenal): tetap kena window 4 jam
  const schedule = booking.class_schedule;
  if (!schedule) {
    return false;
  }

  const datePart = booking.booking_date.slice(0, 10);
  const classStart = new Date(`${datePart}T${schedule.start_time}`);

  const now = new Date();
  const diffMs = classStart.getTime() - now.getTime();
  const diffHours = diffMs / (1000 * 60 * 60);

  return diffHours >= 4;
}