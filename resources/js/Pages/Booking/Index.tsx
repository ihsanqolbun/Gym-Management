import { useState, useEffect } from 'react';
import api from '@/lib/api';
import type { Booking, PaginatedResponse } from '@/types';
import { canCancelBooking } from '@/lib/booking';
import useAuth from '@/hooks/useAuth';

export default function Index() {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { role, isLoading: isAuthLoading } = useAuth();
  useEffect(() => {
    api
      .get<PaginatedResponse<Booking>>('/bookings')
      .then((response) => {
        setBookings(response.data.data);
      })
      .catch(() => {
        setError('Gagal memuat data booking.');
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, []);

  if (isLoading || isAuthLoading) {
    return <div>Loading...</div>;
  }

  if (error) {
    return <div>{error}</div>;
  }

  async function handleCancel(bookingId: number) {
    try {
      await api.patch(`/bookings/${bookingId}/cancel`);
      setBookings((prev) =>
        prev.map((b) =>
          b.id === bookingId ? { ...b, status: 'cancelled' } : b
        )
      );
    } catch (err: any) {
      if (err.response?.status === 422) {
        alert(err.response.data.message);
      } else {
        alert('Gagal membatalkan booking.');
      }
    }
  }
  
  return (
    <div>
      <h1>Booking Saya</h1>
      <ul>
        {bookings.map((booking) => (
          <li key={booking.id}>
          {booking.class_schedule?.class_name} — {booking.booking_date} — {booking.status}
          {canCancelBooking(booking, role) && (
            <button
              type="button"
              style={{ marginLeft: 8 }}
              onClick={() => handleCancel(booking.id)}
            >
              Cancel
            </button>
          )}
        </li>
        ))}
      </ul>
    </div>
  );
}