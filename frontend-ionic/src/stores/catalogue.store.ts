import { defineStore } from 'pinia';
import { apiClient } from '@/api/client';
import type { Product, Category } from '@/types';

interface CacheEntry<T> {
  data: T;
  meta?: any;
  timestamp: number;
}

interface CatalogueState {
  productsCache: Record<string, CacheEntry<Product[]>>;
  productDetailCache: Record<string, CacheEntry<Product>>;
  categories: Category[];
  categoriesTimestamp: number;
  loading: boolean;
}

const PRODUCTS_CACHE_TTL = 3 * 60 * 1000; // 3 minutes
const CATEGORIES_CACHE_TTL = 10 * 60 * 1000; // 10 minutes
const PRODUCT_DETAIL_TTL = 5 * 60 * 1000; // 5 minutes

// In-flight request deduplication map to prevent multiple requests to PHP dev server
const inFlightProductRequests = new Map<string, Promise<Product>>();

export const useCatalogueStore = defineStore('catalogue', {
  state: (): CatalogueState => ({
    productsCache: {},
    productDetailCache: {},
    categories: [],
    categoriesTimestamp: 0,
    loading: false,
  }),

  actions: {
    generateKey(params: Record<string, any>): string {
      const keys = Object.keys(params).sort();
      return keys.map((k) => `${k}=${params[k] ?? ''}`).join('&');
    },

    getCachedProduct(idOrSlug: string | number): Product | null {
      const key = String(idOrSlug);
      if (this.productDetailCache[key]?.data) {
        return this.productDetailCache[key].data;
      }
      // Check within productsCache lists
      for (const cacheKey in this.productsCache) {
        const list = this.productsCache[cacheKey]?.data;
        if (Array.isArray(list)) {
          const match = list.find((p) => String(p.id) === key || p.slug === key);
          if (match) {
            // Index for future fast lookups
            this.productDetailCache[key] = { data: match, timestamp: Date.now() };
            if (match.slug) this.productDetailCache[match.slug] = this.productDetailCache[key];
            if (match.id) this.productDetailCache[String(match.id)] = this.productDetailCache[key];
            return match;
          }
        }
      }
      return null;
    },

    async fetchProducts(
      params: Record<string, any> = {},
      options: { forceRefresh?: boolean; backgroundRevalidate?: boolean } = {}
    ): Promise<{ data: Product[]; meta: any; fromCache: boolean }> {
      const key = this.generateKey(params);
      const now = Date.now();
      const cached = this.productsCache[key];

      // If fresh cache exists and not forced, return immediately
      if (cached && Array.isArray(cached.data) && cached.data.length > 0 && !options.forceRefresh && now - cached.timestamp < PRODUCTS_CACHE_TTL) {
        return { data: cached.data, meta: cached.meta, fromCache: true };
      }

      // If stale cache exists, we can return it immediately and revalidate in background
      const hasStale = !!cached && Array.isArray(cached.data) && cached.data.length > 0 && !options.forceRefresh;

      const networkFetch = async () => {
        try {
          const res = await apiClient.get('/v1/products', { params });
          const fetchedData: Product[] = res.data.data || res.data || [];
          const meta = res.data.meta || {
            current_page: params.page || 1,
            last_page: 1,
            per_page: params.per_page || 12,
            total: fetchedData.length,
            has_more: fetchedData.length >= (params.per_page || 12),
          };

          this.productsCache[key] = {
            data: fetchedData,
            meta,
            timestamp: Date.now(),
          };

          // Index products into detail cache for 0ms instant detail navigation
          for (const item of fetchedData) {
            if (item && (item.slug || item.id)) {
              const entry = { data: item, timestamp: Date.now() };
              if (item.slug) this.productDetailCache[item.slug] = entry;
              if (item.id) this.productDetailCache[String(item.id)] = entry;
            }
          }

          return { data: fetchedData, meta, fromCache: false };
        } catch (err) {
          // If network failed but we have stale cache, keep stale
          if (cached) {
            return { data: cached.data, meta: cached.meta, fromCache: true };
          }
          throw err;
        }
      };

      if (hasStale) {
        // Trigger background revalidation without blocking
        networkFetch().catch((err) => console.warn('Background catalogue revalidation notice:', err));
        return { data: cached.data, meta: cached.meta, fromCache: true };
      }

      this.loading = true;
      try {
        return await networkFetch();
      } finally {
        this.loading = false;
      }
    },

    async fetchProduct(
      idOrSlug: string | number,
      options: { forceRefresh?: boolean } = {}
    ): Promise<Product> {
      const key = String(idOrSlug);
      const now = Date.now();
      const cached = this.productDetailCache[key] || (this.getCachedProduct(key) ? { data: this.getCachedProduct(key)!, timestamp: now } : null);

      if (cached && !options.forceRefresh && now - cached.timestamp < PRODUCT_DETAIL_TTL) {
        return cached.data;
      }

      // Check if this exact product is already being fetched
      if (inFlightProductRequests.has(key)) {
        return inFlightProductRequests.get(key)!;
      }

      const fetchPromise = (async () => {
        try {
          const res = await apiClient.get(`/v1/products/${idOrSlug}`);
          const product: Product = res.data.data || res.data;

          const entry = {
            data: product,
            timestamp: Date.now(),
          };
          this.productDetailCache[key] = entry;
          if (product.slug) {
            this.productDetailCache[product.slug] = entry;
          }
          if (product.id) {
            this.productDetailCache[String(product.id)] = entry;
          }

          return product;
        } catch (err) {
          if (cached?.data) {
            return cached.data;
          }
          throw err;
        } finally {
          inFlightProductRequests.delete(key);
        }
      })();

      inFlightProductRequests.set(key, fetchPromise);
      return fetchPromise;
    },

    async fetchCategories(forceRefresh = false): Promise<Category[]> {
      const now = Date.now();
      if (this.categories.length > 0 && !forceRefresh && now - this.categoriesTimestamp < CATEGORIES_CACHE_TTL) {
        return this.categories;
      }

      try {
        const res = await apiClient.get('/v1/categories');
        const list = res.data.data || res.data || [];
        this.categories = list;
        this.categoriesTimestamp = Date.now();
        return list;
      } catch (err) {
        if (this.categories.length > 0) return this.categories;
        throw err;
      }
    },

    clearCache() {
      this.productsCache = {};
      this.productDetailCache = {};
      this.categoriesTimestamp = 0;
    },
  },
});
