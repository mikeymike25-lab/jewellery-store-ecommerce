import axios, { AxiosError, type InternalAxiosRequestConfig } from 'axios';
import { Preferences } from '@capacitor/preferences';

// Generate or retrieve persistent guest session id
const GUEST_SESSION_KEY = 'eclore_guest_session_id';
const AUTH_TOKEN_KEY = 'eclore_auth_token';

let cachedToken: string | null = null;
let cachedGuestSessionId: string | null = null;

// Simple UUID generator for browser / mobile fallback
function generateUUID(): string {
  if (typeof crypto !== 'undefined' && crypto.randomUUID) {
    return crypto.randomUUID();
  }
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

export async function getGuestSessionId(): Promise<string> {
  if (cachedGuestSessionId) {
    return cachedGuestSessionId;
  }
  const { value } = await Preferences.get({ key: GUEST_SESSION_KEY });
  if (value) {
    cachedGuestSessionId = value;
    return value;
  }
  const newId = generateUUID();
  cachedGuestSessionId = newId;
  await Preferences.set({ key: GUEST_SESSION_KEY, value: newId });
  return newId;
}

export async function getAuthToken(): Promise<string | null> {
  if (cachedToken) {
    return cachedToken;
  }
  const { value } = await Preferences.get({ key: AUTH_TOKEN_KEY });
  cachedToken = value;
  return value;
}

export async function setAuthToken(token: string): Promise<void> {
  cachedToken = token;
  await Preferences.set({ key: AUTH_TOKEN_KEY, value: token });
}

export async function removeAuthToken(): Promise<void> {
  cachedToken = null;
  await Preferences.remove({ key: AUTH_TOKEN_KEY });
}

const USER_CACHE_KEY = 'eclore_user_profile';

export async function getCachedUser(): Promise<any | null> {
  try {
    const { value } = await Preferences.get({ key: USER_CACHE_KEY });
    return value ? JSON.parse(value) : null;
  } catch {
    return null;
  }
}

export async function setCachedUser(user: any): Promise<void> {
  try {
    await Preferences.set({ key: USER_CACHE_KEY, value: JSON.stringify(user) });
  } catch {}
}

export async function removeCachedUser(): Promise<void> {
  try {
    await Preferences.remove({ key: USER_CACHE_KEY });
  } catch {}
}

// Determine default API base URL (127.0.0.1 avoids Windows IPv6 localhost stall)
const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';

export const apiClient = axios.create({
  baseURL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 30000,
});

// Request interceptor: Attach Bearer Token & X-Guest-Session-Id
apiClient.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  const token = await getAuthToken();
  if (token) {
    config.headers.set('Authorization', `Bearer ${token}`);
  }

  const guestSessionId = await getGuestSessionId();
  if (guestSessionId) {
    config.headers.set('X-Guest-Session-Id', guestSessionId);
  }

  return config;
}, (error) => {
  return Promise.reject(error);
});

// Response interceptor: Catch 401s and format errors
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      await removeAuthToken();
      // Dispatch custom event for stores/navigation if needed
      window.dispatchEvent(new CustomEvent('eclore:unauthorized'));
    }
    return Promise.reject(error);
  }
);

export default apiClient;
