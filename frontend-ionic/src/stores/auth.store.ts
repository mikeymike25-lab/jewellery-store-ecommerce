import { defineStore } from 'pinia';
import {
  apiClient,
  getAuthToken,
  setAuthToken,
  removeAuthToken,
  getCachedUser,
  setCachedUser,
  removeCachedUser,
} from '@/api/client';
import { useCartStore } from '@/stores/cart.store';
import { useWishlistStore } from '@/stores/wishlist.store';
import type { User } from '@/types';

interface AuthState {
  user: User | null;
  token: string | null;
  loading: boolean;
  initialized: boolean;
  error: string | null;
  intendedRoute: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    loading: false,
    initialized: false,
    error: null,
    intendedRoute: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userName: (state) => {
      if (!state.user) return 'Guest';
      const u: any = state.user;
      const combined = `${u.first_name || ''} ${u.last_name || ''}`.trim();
      const name = (u.name && u.name !== 'Guest') ? u.name : (combined || u.username || 'Client');
      return name || 'Guest';
    },
    userEmail: (state) => state.user?.email || '',
  },

  actions: {
    async init() {
      if (this.initialized) return;
      this.loading = true;
      try {
        const [storedToken, cachedUser] = await Promise.all([
          getAuthToken(),
          getCachedUser(),
        ]);
        if (storedToken) {
          this.token = storedToken;
          if (cachedUser) {
            this.user = cachedUser;
          }
          await this.fetchUser().catch((fetchErr) => {
            console.warn('Silent user refresh failed, continuing with cached session:', fetchErr);
          });
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
        const u = res.data.user || res.data;
        if (u) {
          const combined = `${(u as any).first_name || ''} ${(u as any).last_name || ''}`.trim();
          if (!u.name || u.name === 'Guest') {
            u.name = combined || (u as any).username || 'Client';
          }
        }
        this.user = u;
        await setCachedUser(u);
        return this.user;
      } catch (err: any) {
        if (err.response?.status === 401) {
          this.user = null;
          this.token = null;
          await Promise.all([removeAuthToken(), removeCachedUser()]);
        }
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
        await Promise.all([setAuthToken(token), setCachedUser(user)]);
        
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
        await Promise.all([setAuthToken(token), setCachedUser(user)]);

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
        const errData = err.response?.data;
        if (errData?.errors && typeof errData.errors === 'object') {
          const msgs = Object.values(errData.errors).flat().filter(Boolean);
          this.error = msgs.join(' ') || errData.message || 'Registration failed.';
        } else {
          this.error = errData?.message || 'Registration failed.';
        }
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

    async updateProfile(payload: { first_name?: string; last_name?: string; phone?: string; username?: string }) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ success: boolean; message?: string; user?: User }>('/v1/account/profile/update', payload);
        if (this.user) {
          if (payload.first_name !== undefined) (this.user as any).first_name = payload.first_name;
          if (payload.last_name !== undefined) (this.user as any).last_name = payload.last_name;
          if (payload.first_name || payload.last_name) {
            this.user.name = `${(this.user as any).first_name || ''} ${(this.user as any).last_name || ''}`.trim() || this.user.name;
          }
          if (payload.phone !== undefined) this.user.phone = payload.phone;
          if (payload.username !== undefined) (this.user as any).username = payload.username;
          await setCachedUser(this.user);
        }
        return res.data;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to update profile.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateAddress(payload: {
      street: string;
      region: string;
      province?: string;
      city: string;
      barangay: string;
      zip_code: string;
    }) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ success: boolean; message?: string; user?: User }>('/v1/account/address/update', payload);
        if (this.user) {
          this.user.street = payload.street;
          this.user.region = payload.region;
          this.user.province = payload.province || '';
          this.user.city = payload.city;
          this.user.barangay = payload.barangay;
          this.user.zip_code = payload.zip_code;
          await setCachedUser(this.user);
        }
        return res.data;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to update address.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async changePassword(payload: { current_password: string; new_password: string; new_password_confirmation: string }) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{ success: boolean; message?: string }>('/v1/account/password/change', payload);
        return res.data;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to update password.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async handleOAuthCallback(token: string) {
      this.loading = true;
      this.token = token;
      await setAuthToken(token);
      try {
        await this.fetchUser();
        const cartStore = useCartStore();
        const wishlistStore = useWishlistStore();
        await Promise.allSettled([
          cartStore.migrateGuestCart(),
          wishlistStore.migrateGuestWishlist(),
        ]);
      } catch (err) {
        console.warn('OAuth callback user fetch / migration warning:', err);
      } finally {
        this.loading = false;
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
        await Promise.all([removeAuthToken(), removeCachedUser()]);
        this.loading = false;
        
        // Refresh cart and wishlist for guest state
        try {
          useCartStore().fetchCart();
          useWishlistStore().fetchWishlist();
        } catch (_) {}
      }
    },

    setIntendedRoute(route: string) {
      this.intendedRoute = route;
      try {
        sessionStorage.setItem('eclore_intended_route', route);
      } catch (_) {}
    },

    consumeIntendedRoute(defaultRoute = '/tabs/account'): string {
      let route = this.intendedRoute;
      if (!route) {
        try {
          route = sessionStorage.getItem('eclore_intended_route');
        } catch (_) {}
      }
      this.intendedRoute = null;
      try {
        sessionStorage.removeItem('eclore_intended_route');
      } catch (_) {}
      return route || defaultRoute;
    },

    clearIntendedRoute() {
      this.intendedRoute = null;
      try {
        sessionStorage.removeItem('eclore_intended_route');
      } catch (_) {}
    },
  },
});
