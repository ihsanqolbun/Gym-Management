import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import type { ClassSchedule, Coach, PaginatedResponse } from '@/types';

interface EditProps {
  scheduleId: number;
}

export default function Edit({ scheduleId }: EditProps) {
  const [className, setClassName] = useState('');
  const [level, setLevel] = useState('beginner');
  const [dayOfWeek, setDayOfWeek] = useState('senin');
  const [startTime, setStartTime] = useState('');
  const [endTime, setEndTime] = useState('');
  const [capacity, setCapacity] = useState('');
  const [location, setLocation] = useState('');
  const [isActive, setIsActive] = useState(true);
  const [coachId, setCoachId] = useState('');
  const [coaches, setCoaches] = useState<Coach[]>([]);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    api.get<PaginatedResponse<Coach>>('/coaches').then((response) => {
      setCoaches(response.data.data);
    });

    api.get<{ data: ClassSchedule }>(`/class-schedules/${scheduleId}`).then((response) => {
      const s = response.data.data;
      setClassName(s.class_name);
      setLevel(s.level);
      setDayOfWeek(s.day_of_week);
      setStartTime(s.start_time.slice(0, 5));
      setEndTime(s.end_time.slice(0, 5));
      setCapacity(String(s.capacity));
      setLocation(s.location ?? '');
      setIsActive(s.is_active);
      setCoachId(s.coach ? String(s.coach.id) : '');
      setIsLoading(false);
    });
  }, [scheduleId]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      await api.put(`/class-schedules/${scheduleId}`, {
        coach_id: coachId || null,
        class_name: className,
        level,
        day_of_week: dayOfWeek,
        start_time: startTime,
        end_time: endTime,
        capacity: Number(capacity),
        location: location || null,
        is_active: isActive,
      });
      router.visit('/class-schedules');
    } catch (err: any) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors);
      } else {
        alert('Gagal menyimpan perubahan.');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      <h1>Edit Jadwal Kelas</h1>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Nama Kelas</label>
          <input value={className} onChange={(e) => setClassName(e.target.value)} />
          {errors.class_name && <div>{errors.class_name[0]}</div>}
        </div>
        <div>
          <label>Level</label>
          <select value={level} onChange={(e) => setLevel(e.target.value)}>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
          {errors.level && <div>{errors.level[0]}</div>}
        </div>
        <div>
          <label>Hari</label>
          <select value={dayOfWeek} onChange={(e) => setDayOfWeek(e.target.value)}>
            <option value="senin">Senin</option>
            <option value="selasa">Selasa</option>
            <option value="rabu">Rabu</option>
            <option value="kamis">Kamis</option>
            <option value="jumat">Jumat</option>
            <option value="sabtu">Sabtu</option>
            <option value="minggu">Minggu</option>
          </select>
          {errors.day_of_week && <div>{errors.day_of_week[0]}</div>}
        </div>
        <div>
          <label>Jam Mulai</label>
          <input type="time" value={startTime} onChange={(e) => setStartTime(e.target.value)} />
          {errors.start_time && <div>{errors.start_time[0]}</div>}
        </div>
        <div>
          <label>Jam Selesai</label>
          <input type="time" value={endTime} onChange={(e) => setEndTime(e.target.value)} />
          {errors.end_time && <div>{errors.end_time[0]}</div>}
        </div>
        <div>
          <label>Kapasitas</label>
          <input type="number" value={capacity} onChange={(e) => setCapacity(e.target.value)} />
          {errors.capacity && <div>{errors.capacity[0]}</div>}
        </div>
        <div>
          <label>Lokasi</label>
          <input value={location} onChange={(e) => setLocation(e.target.value)} />
          {errors.location && <div>{errors.location[0]}</div>}
        </div>
        <div>
          <label>Coach</label>
          <select value={coachId} onChange={(e) => setCoachId(e.target.value)}>
            <option value="">-- Belum ada coach --</option>
            {coaches.map((coach) => (
              <option key={coach.id} value={coach.id}>
                {coach.name}
              </option>
            ))}
          </select>
          {errors.coach_id && <div>{errors.coach_id[0]}</div>}
        </div>
        <div>
          <label>
            <input
              type="checkbox"
              checked={isActive}
              onChange={(e) => setIsActive(e.target.checked)}
            />
            Aktif
          </label>
        </div>
        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Menyimpan...' : 'Update'}
        </button>
      </form>
    </div>
  );
}