import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import useAuth from '@/hooks/useAuth';
import type { User } from '@/types';

export default function Create() {
  const { role, isLoading: isAuthLoading } = useAuth();
  const isMember = role === 'member';

  const [userId, setUserId] = useState('');
  const [pkg, setPkg] = useState('visit_1x');
  const [privateCategory, setPrivateCategory] = useState('');
  const [paymentStatus, setPaymentStatus] = useState('pending');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [status, setStatus] = useState('active');
  const [members, setMembers] = useState<User[]>([]);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (role === 'admin' || role === 'developer') {
      api.get<{ data: User[] }>('/users').then((response) => {
        setMembers(response.data.data);
      });
    }
  }, [role]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    const payload = isMember
      ? { package: pkg }
      : {
          user_id: Number(userId),
          package: pkg,
          private_category: privateCategory || null,
          payment_status: paymentStatus,
          start_date: startDate,
          end_date: endDate,
          status,
        };

    try {
      await api.post('/memberships', payload);
      router.visit('/memberships');
    } catch (err: any) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors);
      } else {
        alert('Gagal menyimpan membership.');
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
      <h1>{isMember ? 'Daftar Membership' : 'Tambah Membership'}</h1>
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
          <label>Paket</label>
          <select value={pkg} onChange={(e) => setPkg(e.target.value)}>
            <option value="visit_1x">1x Visit</option>
            <option value="visit_10x">10x Visit</option>
            <option value="all_day_month">All Day / Month</option>
          </select>
          {errors.package && <div>{errors.package[0]}</div>}
        </div>
        {!isMember && (
          <>
            <div>
              <label>Private (kosongkan kalau member biasa)</label>
              <select value={privateCategory} onChange={(e) => setPrivateCategory(e.target.value)}>
                <option value="">Bukan private</option>
                <option value="private_siang">Private Siang</option>
                <option value="private_malam">Private Pagi/Malam</option>
              </select>
              {errors.private_category && <div>{errors.private_category[0]}</div>}
            </div>
            <div>
              <label>Status Pembayaran</label>
              <select value={paymentStatus} onChange={(e) => setPaymentStatus(e.target.value)}>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
              </select>
              {errors.payment_status && <div>{errors.payment_status[0]}</div>}
            </div>
            <div>
              <label>Tanggal Mulai</label>
              <input type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} />
              {errors.start_date && <div>{errors.start_date[0]}</div>}
            </div>
            <div>
              <label>Tanggal Selesai</label>
              <input type="date" value={endDate} onChange={(e) => setEndDate(e.target.value)} />
              {errors.end_date && <div>{errors.end_date[0]}</div>}
            </div>
            <div>
              <label>Status</label>
              <select value={status} onChange={(e) => setStatus(e.target.value)}>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
              </select>
              {errors.status && <div>{errors.status[0]}</div>}
            </div>
          </>
        )}
        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Menyimpan...' : 'Simpan'}
        </button>
      </form>
    </div>
  );
}