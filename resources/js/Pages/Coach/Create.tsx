import { useState } from 'react';
import { router } from '@inertiajs/react';
import api from '@/lib/api';

export default function Create() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [specialty, setSpecialty] = useState('');
  const [phone, setPhone] = useState('');
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      await api.post('/coaches', {
        name,
        email: email || null,
        specialty: specialty || null,
        phone: phone || null,
      });
      router.visit('/coaches');
    } catch (err: any) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors);
      } else {
        alert('Gagal menyimpan coach.');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div>
      <h1>Tambah Coach</h1>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Nama</label>
          <input value={name} onChange={(e) => setName(e.target.value)} />
          {errors.name && <div>{errors.name[0]}</div>}
        </div>
        <div>
          <label>Email</label>
          <input value={email} onChange={(e) => setEmail(e.target.value)} />
          {errors.email && <div>{errors.email[0]}</div>}
        </div>
        <div>
          <label>Spesialisasi</label>
          <input value={specialty} onChange={(e) => setSpecialty(e.target.value)} />
          {errors.specialty && <div>{errors.specialty[0]}</div>}
        </div>
        <div>
          <label>Telepon</label>
          <input value={phone} onChange={(e) => setPhone(e.target.value)} />
          {errors.phone && <div>{errors.phone[0]}</div>}
        </div>
        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Menyimpan...' : 'Simpan'}
        </button>
      </form>
    </div>
  );
}