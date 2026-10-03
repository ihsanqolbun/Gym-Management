import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import type { ClassSchedule, PaginatedResponse } from '@/types';
import useAuth from '@/hooks/useAuth';

export default function Index() {
  const [schedules, setSchedules] = useState<ClassSchedule[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { role, isLoading: isAuthLoading } = useAuth();

  useEffect(() => {
    api
      .get<PaginatedResponse<ClassSchedule>>('/class-schedules')
      .then((response) => {
        setSchedules(response.data.data);
      })
      .catch(() => {
        setError('Gagal memuat data jadwal.');
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

  async function handleDelete(scheduleId: number) {
    if (!window.confirm('Yakin mau hapus jadwal ini?')) {
      return;
    }

    try {
      await api.delete(`/class-schedules/${scheduleId}`);
      setSchedules((prev) => prev.filter((s) => s.id !== scheduleId));
    } catch (err) {
      alert('Gagal menghapus jadwal.');
    }
  }

  return (
    <div>
      <h1>Daftar Jadwal Kelas</h1>
      {canManage && (
        <button type="button" onClick={() => router.visit('/class-schedules/create')}>
          Tambah Jadwal
        </button>
      )}
      <ul>
        {schedules.map((schedule) => (
          <li key={schedule.id}>
            {schedule.class_name} — {schedule.level} — {schedule.day_of_week} —{' '}
            {schedule.start_time}-{schedule.end_time} — {schedule.location ?? '-'} —{' '}
            {schedule.coach?.name ?? 'Belum ada coach'} —{' '}
            {schedule.is_active ? 'Aktif' : 'Nonaktif'}
            {canManage && (
              <>
                <button
                  type="button"
                  style={{ marginLeft: 8 }}
                  onClick={() => router.visit(`/class-schedules/${schedule.id}/edit`)}
                >
                  Edit
                </button>
                <button
                  type="button"
                  style={{ marginLeft: 8 }}
                  onClick={() => handleDelete(schedule.id)}
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