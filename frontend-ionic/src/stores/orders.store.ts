import { defineStore } from 'pinia';
import { apiClient } from '@/api/client';
import { useAuthStore } from '@/stores/auth.store';
import type { Order, ReturnRepair, OrderTrackingResult } from '@/types';

interface PaginationState {
  total: number;
  current_page: number;
  last_page: number;
  per_page: number;
}

interface OrdersState {
  orders: Order[];
  activeOrder: Order | null;
  returnsRepairs: ReturnRepair[];
  trackingResult: OrderTrackingResult | null;
  pagination: PaginationState;
  loading: boolean;
  trackingLoading: boolean;
  error: string | null;
  ordersCacheTimestamp: number;
  returnsCacheTimestamp: number;
}

let inFlightOrdersPromise: Promise<Order[]> | null = null;
let inFlightReturnsPromise: Promise<ReturnRepair[]> | null = null;

const ORDERS_CACHE_TTL = 45 * 1000; // 45 seconds

export const useOrdersStore = defineStore('orders', {
  state: (): OrdersState => ({
    orders: [],
    activeOrder: null,
    returnsRepairs: [],
    trackingResult: null,
    pagination: {
      total: 0,
      current_page: 1,
      last_page: 1,
      per_page: 10,
    },
    loading: false,
    trackingLoading: false,
    error: null,
    ordersCacheTimestamp: 0,
    returnsCacheTimestamp: 0,
  }),

  actions: {
    async fetchOrders(page = 1, status = 'all', forceRefresh = false) {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.orders = [];
        return [];
      }

      const now = Date.now();
      const isDefaultQuery = page === 1 && (status === 'all' || !status);

      // If fresh cached orders exist and not forcing, return immediately
      if (!forceRefresh && isDefaultQuery && this.orders.length > 0 && now - this.ordersCacheTimestamp < ORDERS_CACHE_TTL) {
        return this.orders;
      }

      // Deduplicate in-flight default query
      if (isDefaultQuery && inFlightOrdersPromise) {
        return inFlightOrdersPromise;
      }

      // If we don't have orders yet, show loading spinner; if we do, load in background
      if (this.orders.length === 0) {
        this.loading = true;
      }
      this.error = null;

      const runFetch = async () => {
        try {
          const params: Record<string, any> = { page };
          if (status && status !== 'all') {
            params.status = status;
          }
          const res = await apiClient.get<{
            success: boolean;
            orders: Order[];
            pagination: PaginationState;
          }>('/v1/account/orders', { params });

          if (res.data.success) {
            this.orders = res.data.orders || [];
            this.pagination = res.data.pagination || this.pagination;
            if (isDefaultQuery) {
              this.ordersCacheTimestamp = Date.now();
            }
          }
          return this.orders;
        } catch (err: any) {
          this.error = err.response?.data?.message || 'Failed to load order history.';
          return this.orders;
        } finally {
          this.loading = false;
          if (isDefaultQuery) {
            inFlightOrdersPromise = null;
          }
        }
      };

      if (isDefaultQuery) {
        inFlightOrdersPromise = runFetch();
        return inFlightOrdersPromise;
      }

      return runFetch();
    },

    async fetchOrderDetails(orderNumber: string) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.get<{
          success: boolean;
          order: Order;
        }>(`/v1/orders/${encodeURIComponent(orderNumber)}`);

        if (res.data.success) {
          this.activeOrder = res.data.order;
        }
        return res.data.order;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to fetch order details.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async trackOrder(orderNumber: string, email: string) {
      this.trackingLoading = true;
      this.error = null;
      this.trackingResult = null;
      try {
        const res = await apiClient.post<{
          success: boolean;
          order: OrderTrackingResult;
        }>('/v1/orders/track', {
          order_number: orderNumber.trim(),
          email: email.trim(),
        });

        if (res.data.success) {
          this.trackingResult = res.data.order;
        }
        return res.data.order;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'No acquisition found matching this reference number and email.';
        throw err;
      } finally {
        this.trackingLoading = false;
      }
    },

    async downloadReceiptPdf(orderNumber: string) {
      try {
        const res = await apiClient.get(`/v1/account/orders/${encodeURIComponent(orderNumber)}/receipt-pdf`, {
          responseType: 'blob',
        });
        const blob = new Blob([res.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `eclore-receipt-${orderNumber}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        return true;
      } catch (err: any) {
        const errorMsg = err.response?.data?.message || 'Official tax receipt is only available for confirmed paid orders.';
        throw new Error(errorMsg);
      }
    },

    async fetchReturnsRepairs(forceRefresh = false) {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        this.returnsRepairs = [];
        return [];
      }

      const now = Date.now();
      if (!forceRefresh && this.returnsRepairs.length > 0 && now - this.returnsCacheTimestamp < ORDERS_CACHE_TTL) {
        return this.returnsRepairs;
      }

      if (inFlightReturnsPromise) {
        return inFlightReturnsPromise;
      }

      inFlightReturnsPromise = (async () => {
        try {
          const res = await apiClient.get<{
            success: boolean;
            returns_repairs: ReturnRepair[];
          }>('/v1/account/returns-repairs');

          if (res.data.success) {
            this.returnsRepairs = res.data.returns_repairs || [];
            this.returnsCacheTimestamp = Date.now();
          }
          return this.returnsRepairs;
        } catch (err: any) {
          console.warn('Could not fetch RMA records:', err);
          return this.returnsRepairs;
        } finally {
          inFlightReturnsPromise = null;
        }
      })();

      return inFlightReturnsPromise;
    },

    async submitRmaRequest(payload: FormData) {
      this.loading = true;
      this.error = null;
      try {
        const res = await apiClient.post<{
          success: boolean;
          message: string;
          rma_number: string;
        }>('/v1/account/returns-repairs', payload, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        // Refresh returns list
        await this.fetchReturnsRepairs().catch(() => {});
        return res.data;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to submit return/repair claim.';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
