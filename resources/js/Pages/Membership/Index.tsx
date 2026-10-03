import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';
import type { Membership, PaginatedResponse } from '@/types';
import useAuth from '@/hooks/useAuth';

export default function Index() {
  const [memberships, setMemberships] = useState<Membership[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { role, isLoading: isAuthLoading } = useAuth();

  useEffect(() => {
    api
      .get<PaginatedResponse<Membership>>('/memberships')
      .then((response) => {
        setMemberships(response.data.data);
      })
      .catch(() => {
        setError('Gagal memuat data membership.');
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

  async function handleDelete(membershipId: number) {
    if (!window.confirm('Yakin mau hapus membership ini?')) {
      return;
    }

    try {
      await api.delete(`/memberships/${membershipId}`);
      setMemberships((prev) => prev.filter((m) => m.id !== membershipId));
    } catch (err) {
      alert('Gagal menghapus membership.');
    }
  }

  return (
    <div>
      <h1>Daftar Membership</h1>
      {canManage && (
        <button type="button" onClick={() => router.visit('/memberships/create')}>
          Tambah Membership
        </button>
      )}
      <ul>
        {memberships.map((membership) => (
          <li key={membership.id}>
            {membership.user?.name ?? 'Unknown'} — {membership.type} — Rp{' '}
            {membership.price} — {membership.payment_status} — {membership.start_date} s/d{' '}
            {membership.end_date} — {membership.status}
            {canManage && (
              <>
                <button
                  type="button"
                  style={{ marginLeft: 8 }}
                  onClick={() => router.visit(`/memberships/${membership.id}/edit`)}
                >
                  Edit
                </button>
                <button
                  type="button"
                  style={{ marginLeft: 8 }}
                  onClick={() => handleDelete(membership.id)}
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