import { defineStore } from 'pinia';
import { apiClient } from '@/api/client';
import { useAuthStore } from '@/stores/auth.store';
import type { WishlistItem, Product } from '@/types';

interface WishlistState {
  items: WishlistItem[];
  loading: boolean;
  isOpen: boolean;
  error: string | null;
  lastFetchedTimestamp: number;
}

let inFlightWishlistPromise: Promise<WishlistItem[]> | null = null;

export const useWishlistStore = defineStore('wishlist', {
  state: (): WishlistState => ({
    items: [],
    loading: false,
    isOpen: false,
    error: null,
    lastFetchedTimestamp: 0,
  }),

  getters: {
    itemCount: (state): number => state.items.length,

    isInWishlist: (state) => {
      return (productId: number | string): boolean => {
        if (!productId) return false;
        const idNum = Number(productId);
        return state.items.some((item) => Number(item.product_id) === idNum || Number(item.product?.id) === idNum);
      };
    },
  },

  actions: {
    openWishlist() {
      this.isOpen = true;
    },

    closeWishlist() {
      this.isOpen = false;
    },

    toggleWishlistDrawer() {
      this.isOpen = !this.isOpen;
    },

    async fetchWishlist(forceRefresh = false) {
      const now = Date.now();
      if (!forceRefresh && this.items.length > 0 && now - this.lastFetchedTimestamp < 60000) {
        return this.items;
      }

      if (inFlightWishlistPromise) {
        return inFlightWishlistPromise;
      }

      this.loading = true;
      this.error = null;

      inFlightWishlistPromise = (async () => {
        try {
          const res = await apiClient.get<any>('/v1/wishlist');
          if (Array.isArray(res.data)) {
            this.items = res.data;
          } else if (res.data?.data && Array.isArray(res.data.data)) {
            this.items = res.data.data;
          } else {
            this.items = [];
          }
          this.lastFetchedTimestamp = Date.now();
          return this.items;
        } catch (err: any) {
          this.error = err.response?.data?.message || 'Failed to load favorites.';
          return this.items;
        } finally {
          this.loading = false;
          inFlightWishlistPromise = null;
        }
      })();

      return inFlightWishlistPromise;
    },

    async toggle(productId: number | string, productData?: any) {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        authStore.openAuthModal('Please sign in to save creations to your Wishlist.');
        return false;
      }

      const idNum = Number(productId);
      if (!idNum) return false;

      // Optimistic update
      const wasIn = this.isInWishlist(idNum);
      if (wasIn) {
        this.items = this.items.filter((item) => Number(item.product_id) !== idNum && Number(item.product?.id) !== idNum);
      } else {
        const optimisticItem: WishlistItem = {
          id: Date.now(),
          product_id: idNum,
          product: productData || ({ id: idNum } as Product),
          created_at: new Date().toISOString(),
        };
        this.items.unshift(optimisticItem);
      }

      try {
        const res = await apiClient.post<{ success: boolean; was_added?: boolean }>('/v1/wishlist/toggle', {
          product_id: idNum,
        });
        // Re-fetch to sync full product relationship from database/session
        await this.fetchWishlist(true);
        return res.data?.was_added ?? !wasIn;
      } catch (err: any) {
        await this.fetchWishlist(true);
        this.error = err.response?.data?.message || 'Could not update favorites.';
        throw err;
      }
    },

    async toggleItem(product: Product | number | string) {
      if (typeof product === 'object' && product !== null) {
        return this.toggle(product.id, product);
      }
      return this.toggle(product);
    },

    async add(productId: number | string, productData?: any) {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated) {
        authStore.openAuthModal('Please sign in to save creations to your Wishlist.');
        return;
      }

      const idNum = Number(productId);
      if (!idNum) return;
      if (!this.isInWishlist(idNum)) {
        this.items.unshift({
          id: Date.now(),
          product_id: idNum,
          product: productData || ({ id: idNum } as Product),
          created_at: new Date().toISOString(),
        } as WishlistItem);
      }
      try {
        await apiClient.post('/v1/wishlist/add', { product_id: idNum });
        await this.fetchWishlist();
      } catch (err: any) {
        await this.fetchWishlist();
        this.error = err.response?.data?.message || 'Could not add to favorites.';
        throw err;
      }
    },

    async remove(productId: number | string) {
      const idNum = Number(productId);
      this.items = this.items.filter((item) => Number(item.product_id) !== idNum && Number(item.product?.id) !== idNum);
      try {
        await apiClient.delete('/v1/wishlist/remove', {
          data: { product_id: idNum },
        });
        await this.fetchWishlist();
      } catch (err: any) {
        await this.fetchWishlist();
        throw err;
      }
    },

    async clearWishlist() {
      try {
        await apiClient.delete('/v1/wishlist/clear');
        this.items = [];
      } catch (err: any) {
        await this.fetchWishlist();
        throw err;
      }
    },

    async migrateGuestWishlist() {
      try {
        await apiClient.post('/v1/wishlist/migrate');
        await this.fetchWishlist();
      } catch (err) {
        console.warn('Guest wishlist migration notice:', err);
      }
    },
  },
});
