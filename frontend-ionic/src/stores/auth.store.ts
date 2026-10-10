import { defineStore } from 'pinia';
import { apiClient, getAuthToken, setAuthToken, removeAuthToken } from '@/api/client';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import type { User } from '@/types';

interface AuthState {
  user: User | null;
  token: string | null;
  loading: boolean;
  initialized: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    loading: false,
    initialized: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userName: (state) => state.user?.name || 'Guest',
    userEmail: (state) => state.user?.email || '',
  },

  actions: {
    async init() {
      if (this.initialized) return;
      this.loading = true;
      try {
        const storedToken = await getAuthToken();
        if (storedToken) {
          this.token = storedToken;
          await this.fetchUser();
        }
      } catch (err: any) {
        console.warn('Auth initialization error:', err);
        await this.logout();
      } finally {
        this.loading = false;
        this.initialized = true;
      }
    },

    async fetchUser() {
      try {
        const res = await apiClient.get<{ user: User }>('/v1/auth/user');
        this.user = res.data.user || res.data;
        return this.user;
      } catch (err: any) {
        this.user = null;
        this.token = null;
        await removeAuthToken();
        throw err;
      }
    },

    async login(credentials: { email: string; password: string }) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ token: string; user: User }>('/v1/auth/token', credentials);
        const { token, user } = res.data;
        this.token = token;
        this.user = user;
        await setAuthToken(token);
        
        // Task 4.4: Post-login guest cart and wishlist migration
        try {
          const cartStore = useCartStore();
          const wishlistStore = useWishlistStore();
          await Promise.allSettled([
            cartStore.migrateGuestCart(),
            wishlistStore.migrateGuestWishlist(),
          ]);
        } catch (migErr) {
          console.warn('Post-auth migration warning:', migErr);
        }

        return user;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Login failed. Please check your credentials.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async register(payload: {
      first_name?: string;
      last_name?: string;
      name?: string;
      email: string;
      username?: string;
      password: string;
      password_confirmation: string;
    }) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ token: string; user: User }>('/v1/auth/register', payload);
        const { token, user } = res.data;
        this.token = token;
        this.user = user;
        await setAuthToken(token);

        // Task 4.4: Post-registration guest cart and wishlist migration
        try {
          const cartStore = useCartStore();
          const wishlistStore = useWishlistStore();
          await Promise.allSettled([
            cartStore.migrateGuestCart(),
            wishlistStore.migrateGuestWishlist(),
          ]);
        } catch (migErr) {
          console.warn('Post-auth migration warning:', migErr);
        }

        return user;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Registration failed.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async forgotPassword(login: string) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ success: boolean; message: string; user_email?: string }>('/v1/auth/forgot-password', {
          login,
        });
        return res.data;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to dispatch password reset request.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async resetPassword(payload: { token: string; password: string; password_confirmation: string }) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ success: boolean; message: string }>('/v1/auth/reset-password', payload);
        return res.data;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Password reset failed.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async checkUsername(username: string): Promise<{ available: boolean; message: string }> {
      try {
        const res = await apiClient.get<{ available: boolean; message: string }>(`/v1/auth/check-username/${encodeURIComponent(username)}`);
        return res.data;
      } catch {
        return { available: false, message: 'Could not verify username' };
      }
    },

    async checkEmail(email: string): Promise<{ exists: boolean; message: string }> {
      try {
        const res = await apiClient.get<{ exists: boolean; message: string }>(`/v1/auth/check-email/${encodeURIComponent(email)}`);
        return res.data;
      } catch {
        return { exists: false, message: 'Could not verify email' };
      }
    },

    async logout() {
      this.loading = true;
      try {
        if (this.token) {
          await apiClient.post('/v1/auth/logout').catch(() => {});
        }
      } finally {
        this.user = null;
        this.token = null;
        this.error = null;
        await removeAuthToken();
        this.loading = false;
        
        // Refresh cart and wishlist for guest state
        try {
          useCartStore().fetchCart();
          useWishlistStore().fetchWishlist();
        } catch (_) {}
      }
    },
  },
});
