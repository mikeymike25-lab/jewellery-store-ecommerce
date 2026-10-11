import { defineStore } from 'pinia';
import { apiClient } from '@/api/client';
import { Preferences } from '@capacitor/preferences';
import type { CartItem } from '@/types';

const SELECTED_CART_IDS_KEY = 'eclore_selected_cart_ids';

interface CartState {
  items: CartItem[];
  selectedIds: number[];
  loading: boolean;
  isOpen: boolean;
  error: string | null;
  buyNowActive: boolean;
}

export const useCartStore = defineStore('cart', {
  state: (): CartState => ({
    items: [],
    selectedIds: [],
    loading: false,
    isOpen: false,
    error: null,
    buyNowActive: false,
  }),

  getters: {
    itemCount: (state): number => {
      return state.items.reduce((sum, item) => sum + (Number(item.quantity) || 1), 0);
    },

    selectedItems: (state): CartItem[] => {
      return state.items.filter((item) => state.selectedIds.includes(item.id));
    },

    selectedCount: (state): number => {
      return state.items
        .filter((item) => state.selectedIds.includes(item.id))
        .reduce((sum, item) => sum + (Number(item.quantity) || 1), 0);
    },

    subtotal(): number {
      const activeItems = this.selectedItems;
      return activeItems.reduce((sum, item) => {
        const price = Number(item.unit_price) || 0;
        const qty = Number(item.quantity) || 1;
        return sum + (price * qty);
      }, 0);
    },

    // 12% Philippine VAT (VAT inclusive)
    vatAmount(): number {
      return Math.round((this.subtotal * 12 / 112) * 100) / 100;
    },

    netOfVat(): number {
      return Math.round((this.subtotal - this.vatAmount) * 100) / 100;
    },

    total(): number {
      return this.subtotal;
    },

    allSelected: (state): boolean => {
      return state.items.length > 0 && state.items.every((item) => state.selectedIds.includes(item.id));
    },
  },

  actions: {
    openCart() {
      this.isOpen = true;
    },

    closeCart() {
      this.isOpen = false;
    },

    toggleCart() {
      this.isOpen = !this.isOpen;
    },

    async initSelectedIds() {
      try {
        const { value } = await Preferences.get({ key: SELECTED_CART_IDS_KEY });
        if (value) {
          const parsed = JSON.parse(value);
          if (Array.isArray(parsed)) {
            this.selectedIds = parsed;
          }
        }
      } catch (e) {
        console.warn('Failed to load selected cart IDs from preferences:', e);
      }
    },

    async persistSelectedIds() {
      try {
        await Preferences.set({
          key: SELECTED_CART_IDS_KEY,
          value: JSON.stringify(this.selectedIds),
        });
        // Sync with backend session endpoint
        await apiClient.post('/v1/cart/selected-items', {
          selectedItems: this.selectedIds,
        }).catch(() => {});
      } catch (e) {
        console.warn('Failed to persist selected cart IDs:', e);
      }
    },

    async toggleItemSelection(id: number) {
      if (this.selectedIds.includes(id)) {
        this.selectedIds = this.selectedIds.filter((itemId) => itemId !== id);
      } else {
        this.selectedIds.push(id);
      }
      await this.persistSelectedIds();
    },

    async selectAll(select = true) {
      if (select) {
        this.selectedIds = this.items.map((i) => i.id);
      } else {
        this.selectedIds = [];
      }
      await this.persistSelectedIds();
    },

    async fetchCart() {
      this.loading = true;
      this.error = null;
      try {
        await this.initSelectedIds();
        const res = await apiClient.get<any>('/v1/cart');
        let fetchedItems: CartItem[] = [];

        if (res.data?.data?.cart_items && Array.isArray(res.data.data.cart_items)) {
          fetchedItems = res.data.data.cart_items;
        } else if (Array.isArray(res.data)) {
          fetchedItems = res.data;
        } else if (res.data?.items && Array.isArray(res.data.items)) {
          fetchedItems = res.data.items;
        } else if (res.data?.cart && Array.isArray(res.data.cart)) {
          fetchedItems = res.data.cart;
        }

        this.items = fetchedItems.map((item) => ({
          ...item,
          unit_price: Number(item.unit_price) || 0,
          total_price: Number(item.total_price) || (Number(item.unit_price) * Number(item.quantity)),
          line_total: Number(item.total_price) || (Number(item.unit_price) * Number(item.quantity)),
          product_name: item.product_name || item.product?.name || 'Luxury Jewellery',
        }));

        // Preserve selected IDs that still exist in fetched items; default to all selected if empty
        const existingIds = this.items.map((item) => item.id);
        const validSelected = this.selectedIds.filter((id) => existingIds.includes(id));
        if (validSelected.length > 0) {
          this.selectedIds = validSelected;
        } else {
          this.selectedIds = existingIds;
        }
        await this.persistSelectedIds();
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to load shopping bag.';
      } finally {
        this.loading = false;
      }
    },

    async addToCart(productId: number, quantity = 1, options?: { ring_size?: string; metal_type?: string }) {
      this.loading = true;
      this.error = null;
      try {
        await apiClient.post('/v1/cart/add', {
          product_id: productId,
          quantity,
          size: options?.ring_size,
          ring_size: options?.ring_size,
          metal_type: options?.metal_type,
        });
        await this.fetchCart();
        // Ensure added item is in selectedIds
        const addedItem = this.items.find((i) => i.product_id === productId);
        if (addedItem && !this.selectedIds.includes(addedItem.id)) {
          this.selectedIds.push(addedItem.id);
          await this.persistSelectedIds();
        }
        this.openCart();
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Could not add to bag.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateQuantity(productId: number, quantity: number) {
      if (quantity <= 0) {
        return this.removeItem(productId);
      }
      try {
        await apiClient.put('/v1/cart/update', {
          product_id: productId,
          quantity,
        });
        const item = this.items.find((i) => i.product_id === productId);
        if (item) {
          item.quantity = quantity;
          item.total_price = item.unit_price * quantity;
          item.line_total = item.unit_price * quantity;
        }
      } catch (err: any) {
        await this.fetchCart();
        throw err;
      }
    },

    async removeItem(productId: number) {
      try {
        await apiClient.delete('/v1/cart/remove', {
          data: { product_id: productId },
        });
        const removedItem = this.items.find((i) => i.product_id === productId);
        if (removedItem) {
          this.selectedIds = this.selectedIds.filter((id) => id !== removedItem.id);
        }
        this.items = this.items.filter((i) => i.product_id !== productId);
        await this.persistSelectedIds();
      } catch (err: any) {
        await this.fetchCart();
        throw err;
      }
    },

    async clearCart() {
      try {
        await apiClient.delete('/v1/cart/clear');
        this.items = [];
        this.selectedIds = [];
        await this.persistSelectedIds();
      } catch (err: any) {
        await this.fetchCart();
        throw err;
      }
    },

    async migrateGuestCart() {
      try {
        await apiClient.post('/v1/cart/migrate');
        await this.fetchCart();
      } catch (err) {
        console.warn('Guest cart migration notice:', err);
      }
    },

    /**
     * Task 4.5: Buy Now Flow
     * Isolates the single item for checkout bypass without clearing the shopping bag.
     */
    async buyNow(productId: number, quantity = 1, options?: { ring_size?: string; metal_type?: string }): Promise<void> {
      this.loading = true;
      try {
        // Add to cart to ensure server has the record
        await apiClient.post('/v1/cart/add', {
          product_id: productId,
          quantity,
          size: options?.ring_size,
          ring_size: options?.ring_size,
          metal_type: options?.metal_type,
        });
        await this.fetchCart();
        // Isolate selectedIds strictly to this product's cart item
        const targetItem = this.items.find((i) => i.product_id === productId);
        if (targetItem) {
          this.selectedIds = [targetItem.id];
          this.buyNowActive = true;
          await this.persistSelectedIds();
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Could not process Buy Now.';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
