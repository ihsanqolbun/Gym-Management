import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import useAuth from '@/hooks/useAuth';
import type { User } from '@/types';

export default function Create() {
  const { role, isLoading: isAuthLoading } = useAuth();
  const isMember = role === 'member';

  const [userId, setUserId] = useState('');
  const [sessionDate, setSessionDate] = useState('');
  const [startTime, setStartTime] = useState('');
  const [locationType, setLocationType] = useState('gym');
  const [address, setAddress] = useState('');
  const [members, setMembers] = useState<User[]>([]);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (role === 'admin' || role === 'developer') {
      api.get<{ data: User[] }>('/users').then((response) => setMembers(response.data.data));
    }
  }, [role]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      await api.post('/private-sessions', {
        ...(isMember ? {} : { user_id: Number(userId) }),
        session_date: sessionDate,
        start_time: startTime,
        location_type: locationType,
        address: locationType === 'rumah' ? address : null,
      });
      router.visit('/private-sessions');
    } catch (err: any) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors);
      } else {
        alert('Gagal menyimpan request.');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  if (isAuthLoading) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      <h1>Request Private</h1>
      <form onSubmit={handleSubmit}>
        {!isMember && (
          <div>
            <label>Member</label>
            <select value={userId} onChange={(e) => setUserId(e.target.value)}>
              <option value="">-- Pilih Member --</option>
              {members.map((m) => (
                <option key={m.id} value={m.id}>{m.name}</option>
              ))}
            </select>
            {errors.user_id && <div>{errors.user_id[0]}</div>}
          </div>
        )}
        <div>
          <label>Tanggal</label>
          <input type="date" value={sessionDate} onChange={(e) => setSessionDate(e.target.value)} />
          {errors.session_date && <div>{errors.session_date[0]}</div>}
        </div>
        <div>
          <label>Jam Mulai</label>
          <input type="time" value={startTime} onChange={(e) => setStartTime(e.target.value)} />
          {errors.start_time && <div>{errors.start_time[0]}</div>}
        </div>
        <div>
          <label>Lokasi</label>
          <select value={locationType} onChange={(e) => setLocationType(e.target.value)}>
            <option value="gym">Gym</option>
            <option value="rumah">Rumah</option>
          </select>
          {errors.location_type && <div>{errors.location_type[0]}</div>}
        </div>
        {locationType === 'rumah' && (
          <div>
            <label>Alamat</label>
            <input value={address} onChange={(e) => setAddress(e.target.value)} />
            {errors.address && <div>{errors.address[0]}</div>}
          </div>
        )}
        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Menyimpan...' : 'Kirim Request'}
        </button>
      </form>
    </div>
  );
}