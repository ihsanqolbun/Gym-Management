import axios from 'axios';

function getXsrfToken(): string | null {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = getXsrfToken();
  if (token && config.headers) {
    config.headers['X-XSRF-TOKEN'] = token;
  }
  return config;
});

export function csrf() {
  return axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

export default api;
