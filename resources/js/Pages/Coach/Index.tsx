import { useState, useEffect } from 'react';
import api from '@/lib/api';
import type { Coach, PaginatedResponse } from '@/types';
import useAuth from '@/hooks/useAuth';

export default function Index() {
  const [coaches, setCoaches] = useState<Coach[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { role, isLoading: isAuthLoading } = useAuth();

  useEffect(() => {
    api
      .get<PaginatedResponse<Coach>>('/coaches')
      .then((response) => {
        setCoaches(response.data.data);
      })
      .catch(() => {
        setError('Gagal memuat data coach.');
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

  const canManage = role === 'admin' || role === 'developer';

  return (
    <div>
      <h1>Daftar Coach</h1>
      <ul>
        {coaches.map((coach) => (
          <li key={coach.id}>
            {coach.name} — {coach.specialty} — {coach.phone ?? '-'}
            {canManage && (
              <>
                <button type="button" style={{ marginLeft: 8 }}>
                  Edit
                </button>
                <button type="button" style={{ marginLeft: 8 }}>
                  Delete
                </button>
              </>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
}