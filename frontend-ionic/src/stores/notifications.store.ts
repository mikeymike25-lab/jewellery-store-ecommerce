import { defineStore } from 'pinia';
import { apiClient } from '@/api/client';
import { useAuthStore } from '@/stores/auth.store';

export interface NotificationItem {
  id: number;
  title: string;
  message: string;
  type?: string;
  status: 'pending' | 'sent' | 'read';
  created_at: string;
  read_at?: string | null;
  data?: any;
}

interface NotificationsState {
  notifications: NotificationItem[];
  unreadCount: number;
  loading: boolean;
  error: string | null;
  lastFetchedTimestamp: number;
}

let inFlightNotificationsPromise: Promise<NotificationItem[]> | null = null;
let inFlightUnreadCountPromise: Promise<number> | null = null;

const NOTIFICATIONS_CACHE_TTL = 30 * 1000; // 30 seconds cache

export const useNotificationsStore = defineStore('notifications', {
  state: (): NotificationsState => ({
    notifications: [],
    unreadCount: 0,
    loading: false,
    error: null,
    lastFetchedTimestamp: 0,
  }),

  getters: {
    hasUnread: (state): boolean => state.unreadCount > 0,
  },

  actions: {
    async fetchUnreadCount(forceRefresh = false): Promise<number> {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.unreadCount = 0;
        return 0;
      }

      const now = Date.now();
      if (!forceRefresh && this.lastFetchedTimestamp > 0 && now - this.lastFetchedTimestamp < NOTIFICATIONS_CACHE_TTL) {
        return this.unreadCount;
      }

      if (inFlightUnreadCountPromise) {
        return inFlightUnreadCountPromise;
      }

      inFlightUnreadCountPromise = (async () => {
        try {
          const res = await apiClient.get<{ count: number }>('/v1/notifications/unread-count');
          this.unreadCount = res.data?.count || 0;
          return this.unreadCount;
        } catch {
          return this.unreadCount;
        } finally {
          inFlightUnreadCountPromise = null;
        }
      })();

      return inFlightUnreadCountPromise;
    },

    async fetchNotifications(forceRefresh = false): Promise<NotificationItem[]> {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.notifications = [];
        this.unreadCount = 0;
        return [];
      }

      const now = Date.now();
      // If we have cached notifications and not forced, return immediately
      if (!forceRefresh && this.notifications.length > 0 && now - this.lastFetchedTimestamp < NOTIFICATIONS_CACHE_TTL) {
        return this.notifications;
      }

      if (inFlightNotificationsPromise) {
        return inFlightNotificationsPromise;
      }

      // If we already have items in memory, don't show blocking loading spinner
      if (this.notifications.length === 0) {
        this.loading = true;
      }
      this.error = null;

      inFlightNotificationsPromise = (async () => {
        try {
          const res = await apiClient.get<{ notifications: NotificationItem[] }>('/v1/notifications');
          const list = res.data?.notifications || [];
          this.notifications = list;
          this.unreadCount = list.filter((n) => n.status !== 'read').length;
          this.lastFetchedTimestamp = Date.now();
          return this.notifications;
        } catch (err: any) {
          console.warn('Failed to load notifications:', err);
          this.error = 'Unable to load notifications at this time.';
          return this.notifications;
        } finally {
          this.loading = false;
          inFlightNotificationsPromise = null;
        }
      })();

      return inFlightNotificationsPromise;
    },

    async markAsRead(notification: NotificationItem) {
      if (notification.status === 'read') return;
      // Optimistic update
      notification.status = 'read';
      notification.read_at = new Date().toISOString();
      if (this.unreadCount > 0) {
        this.unreadCount--;
      }

      try {
        await apiClient.post(`/v1/notifications/${notification.id}/read`);
      } catch (err) {
        console.error('Failed to mark notification as read:', err);
      }
    },

    async markAllAsRead() {
      // Optimistic update
      this.notifications.forEach((n) => {
        n.status = 'read';
        n.read_at = new Date().toISOString();
      });
      this.unreadCount = 0;

      try {
        await apiClient.post('/v1/notifications/read-all');
      } catch (err) {
        console.error('Failed to mark all notifications as read:', err);
      }
    },

    async clearAll() {
      // Optimistic update
      this.notifications = [];
      this.unreadCount = 0;

      try {
        await apiClient.delete('/v1/notifications/clear-all');
      } catch (err) {
        console.error('Failed to clear notifications:', err);
      }
    },
  },
});
