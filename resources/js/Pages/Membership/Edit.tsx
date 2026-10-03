import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import type { Membership } from '@/types';

interface EditProps {
  membershipId: number;
}

export default function Edit({ membershipId }: EditProps) {
  const [type, setType] = useState('harian');
  const [price, setPrice] = useState('');
  const [paymentStatus, setPaymentStatus] = useState('pending');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [status, setStatus] = useState('active');
  const [userName, setUserName] = useState('');
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    api.get<{ data: Membership }>(`/memberships/${membershipId}`).then((response) => {
      const m = response.data.data;
      setType(m.type);
      setPrice(m.price);
      setPaymentStatus(m.payment_status);
      setStartDate(m.start_date.slice(0, 10));
      setEndDate(m.end_date.slice(0, 10));
      setStatus(m.status);
      setUserName(m.user?.name ?? 'Unknown');
      setIsLoading(false);
    });
  }, [membershipId]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      await api.put(`/memberships/${membershipId}`, {
        type,
        price: Number(price),
        payment_status: paymentStatus,
        start_date: startDate,
        end_date: endDate,
        status,
      });
      router.visit('/memberships');
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
      <h1>Edit Membership — {userName}</h1>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Tipe</label>
          <select value={type} onChange={(e) => setType(e.target.value)}>
            <option value="harian">Harian</option>
            <option value="mingguan">Mingguan</option>
            <option value="bulanan">Bulanan</option>
          </select>
          {errors.type && <div>{errors.type[0]}</div>}
        </div>
        <div>
          <label>Harga</label>
          <input type="number" value={price} onChange={(e) => setPrice(e.target.value)} />
          {errors.price && <div>{errors.price[0]}</div>}
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
        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Menyimpan...' : 'Update'}
        </button>
      </form>
    </div>
  );
}