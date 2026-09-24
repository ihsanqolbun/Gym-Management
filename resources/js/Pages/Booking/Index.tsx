import { useState, useEffect } from 'react';
import api from '@/lib/api';
import type { Booking, PaginatedResponse } from '@/types';

export default function Index() {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

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

  if (isLoading) {
    return <div>Loading...</div>;
  }

  if (error) {
    return <div>{error}</div>;
  }

  return (
    <div>
      <h1>Booking Saya</h1>
      <ul>
        {bookings.map((booking) => (
          <li key={booking.id}>
            {booking.class_schedule?.class_name} — {booking.booking_date} — {booking.status}
          </li>
        ))}
      </ul>
    </div>
  );
}