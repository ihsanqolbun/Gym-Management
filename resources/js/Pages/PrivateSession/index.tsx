import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import type { PrivateSession, PaginatedResponse } from '@/types';
import useAuth from '@/hooks/useAuth';

export default function Index() {
  const [sessions, setSessions] = useState<PrivateSession[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { user, role, isLoading: isAuthLoading } = useAuth();

  useEffect(() => {
    api
      .get<PaginatedResponse<PrivateSession>>('/private-sessions')
      .then((response) => setSessions(response.data.data))
      .catch(() => setError('Gagal memuat data private session.'))
      .finally(() => setIsLoading(false));
  }, []);

  if (isLoading || isAuthLoading) {
    return <div>Loading...</div>;
  }

  if (error) {
    return <div>{error}</div>;
  }

  const canManage = role === 'admin' || role === 'developer';

  function replaceSession(updated: PrivateSession) {
    setSessions((prev) => prev.map((s) => (s.id === updated.id ? updated : s)));
  }

  async function handleStatus(id: number, status: 'approved' | 'rejected') {
    try {
      const response = await api.put<{ data: PrivateSession }>(`/private-sessions/${id}`, { status });
      replaceSession(response.data.data);
    } catch {
      alert('Gagal mengubah status.');
    }
  }

  async function handleCancel(id: number) {
    if (!window.confirm('Yakin mau cancel sesi ini?')) {
      return;
    }
    try {
      const response = await api.patch<{ data: PrivateSession }>(`/private-sessions/${id}/cancel`);
      replaceSession(response.data.data);
    } catch {
      alert('Gagal cancel sesi.');
    }
  }

  async function handleDelete(id: number) {
    if (!window.confirm('Yakin mau hapus sesi ini?')) {
      return;
    }
    try {
      await api.delete(`/private-sessions/${id}`);
      setSessions((prev) => prev.filter((s) => s.id !== id));
    } catch {
      alert('Gagal menghapus sesi.');
    }
  }

  return (
    <div>
      <h1>Private Session</h1>
      {(canManage || role === 'member') && (
        <button type="button" onClick={() => router.visit('/private-sessions/create')}>
          Request Private
        </button>
      )}
      <ul>
        {sessions.map((s) => {
          const canCancel =
            s.status === 'requested' || s.status === 'approved'
              ? canManage || (role === 'member' && s.user?.id === user?.id)
              : false;

          return (
            <li key={s.id}>
              {s.user?.name ?? 'Unknown'} — {s.session_date.slice(0, 10)} {s.start_time.slice(0, 5)} —{' '}
              {s.time_slot === 'private_siang' ? 'Siang' : 'Pagi/Malam'} — Rp {s.price} —{' '}
              {s.location_type === 'rumah' ? `Rumah (${s.address})` : 'Gym'} — {s.status}
              {canManage && s.status === 'requested' && (
                <>
                  <button type="button" style={{ marginLeft: 8 }} onClick={() => handleStatus(s.id, 'approved')}>
                    Approve
                  </button>
                  <button type="button" style={{ marginLeft: 8 }} onClick={() => handleStatus(s.id, 'rejected')}>
                    Reject
                  </button>
                </>
              )}
              {canCancel && (
                <button type="button" style={{ marginLeft: 8 }} onClick={() => handleCancel(s.id)}>
                  Cancel
                </button>
              )}
              {canManage && (
                <button type="button" style={{ marginLeft: 8 }} onClick={() => handleDelete(s.id)}>
                  Delete
                </button>
              )}
            </li>
          );
        })}
      </ul>
    </div>
  );
}