export interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: PaginationLinks;
  meta: PaginationMeta;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'developer' | 'owner' | 'admin' | 'member';
  phone: string | null;
}

export interface Coach {
  id: number;
  name: string;
  email: string;
  specialty: string;
  phone: string | null;
  created_at: string;
}

export interface ClassSchedule {
  id: number;
  class_name: string;
  level: 'beginner' | 'intermediate' | 'advanced';
  day_of_week: 'senin' | 'selasa' | 'rabu' | 'kamis' | 'jumat' | 'sabtu' | 'minggu';
  start_time: string;
  end_time: string;
  capacity: number;
  location: string;
  is_active: boolean;
  coach: Coach | null;
}

export interface Membership {
  id: number;
  type: 'harian' | 'mingguan' | 'bulanan';
  category: 'pelajar' | 'reguler' | 'dewasa' | 'private_siang' | 'private_malam' | null;
  package: 'visit_1x' | 'visit_10x' | 'all_day_month' | null;
  price: string;
  register_fee: number;
  payment_status: 'pending' | 'paid';
  start_date: string;
  end_date: string;
  status: 'active' | 'expired';
  user: User | null;
}

export interface Booking {
  id: number;
  booking_date: string;
  status: 'booked' | 'cancelled' | 'attended';
  checked_in_at: string | null;
  notes: string | null;
  user: User | null;
  class_schedule: ClassSchedule | null;
}

export interface PrivateSession {
  id: number;
  session_date: string;
  start_time: string;
  time_slot: 'private_siang' | 'private_malam';
  location_type: 'gym' | 'rumah';
  address: string | null;
  price: number;
  status: 'requested' | 'approved' | 'rejected' | 'cancelled';
  admin_note: string | null;
  user: User | null;
  coach: Coach | null;
}