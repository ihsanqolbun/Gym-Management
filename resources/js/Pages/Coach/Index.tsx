import { useState, useEffect } from 'react';
import api from '@/lib/api';
import type { Coach, PaginatedResponse } from '@/types';
import useAuth from '@/hooks/useAuth';
import { router } from '@inertiajs/react';

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

  if (error) {
    return <div>{error}</div>;
  }

  async function handleDelete(coachId: number) {
    if (!window.confirm('Yakin mau hapus coach ini?')) {
      return;
    }

    try {
      await api.delete(`/coaches/${coachId}`);
      setCoaches((prev) => prev.filter((c) => c.id !== coachId));
    } catch (err) {
      alert('Gagal menghapus coach.');
    }
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
                <button
                  type="button"
                  style={{ marginLeft: 8 }}
                  onClick={() => router.visit(`/coaches/${coach.id}/edit`)}
                >
                  Edit
                </button>
                <button
                  type="button"
                  style={{ marginLeft: 8 }}
                  onClick={() => handleDelete(coach.id)}
                >
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