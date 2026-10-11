<script setup lang="ts">
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { apiClient } from '@/api/client';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { getProductImage } from '@/utils/product-image';
import type { Product } from '@/types';
interface Props {
  isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'close'): void;
}>();

const router = useRouter();
const searchQuery = ref('');
const results = ref<Product[]>([]);
const loading = ref(false);
const searched = ref(false);

const quickTags = [
  { label: 'Diamond Rings', category: 'rings' },
  { label: 'Gold Necklaces', category: 'necklaces-pendants' },
  { label: 'Tennis Bracelets', category: 'bracelets-bangles' },
  { label: 'Stud Earrings', category: 'earrings' },
  { label: 'Solitaire', search: 'solitaire' },
];

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function performSearch(query: string) {
  if (!query || query.trim().length < 2) {
    results.value = [];
    searched.value = false;
    loading.value = false;
    return;
  }

  loading.value = true;
  searched.value = true;
  try {
    const res = await apiClient.get<{ data: Product[] }>('/v1/search', {
      params: { q: query.trim() },
    });
    results.value = res.data.data || [];
  } catch (err) {
    console.error('Search error:', err);
    results.value = [];
  } finally {
    loading.value = false;
  }
}

watch(searchQuery, (newVal) => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    performSearch(newVal);
  }, 300);
});

function handleClose() {
  searchQuery.value = '';
  results.value = [];
  searched.value = false;
  emit('close');
}

function selectProduct(product: Product) {
  handleClose();
  router.push(`/products/${product.slug || product.id}`);
}

function selectTag(tag: { category?: string; search?: string }) {
  if (tag.category) {
    handleClose();
    router.push(`/catalogue?category=${tag.category}`);
  } else if (tag.search) {
    searchQuery.value = tag.search;
  }
}
</script>

<template>
  <Transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-black/60 backdrop-blur-sm"
      @click.self="handleClose"
    >
      <div class="w-full max-w-2xl bg-[#F3EFE7] border border-[#E2DDD5] shadow-2xl overflow-hidden animate-slide-down">
        <!-- Search Header -->
        <div class="p-6 border-b border-[#E2DDD5] flex items-center justify-between">
          <div class="flex-grow flex items-center space-x-3">
            <svg class="w-5 h-5 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="SEARCH BY CREATION, METAL, OR GEMSTONE..."
              class="w-full bg-transparent font-azeret text-xs sm:text-sm tracking-widest text-[#1A1A1A] placeholder-stone-400 focus:outline-none uppercase"
              autofocus
            />
          </div>
          <button
            type="button"
            class="text-stone-400 hover:text-[#1A1A1A] transition-colors p-1"
            aria-label="Close search"
            @click="handleClose"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Quick Tags -->
        <div class="px-6 py-3 bg-[#EBE6DC] border-b border-[#E2DDD5] flex items-center gap-2 overflow-x-auto no-scrollbar">
          <span class="font-azeret text-[9px] uppercase tracking-widest text-stone-500 shrink-0">Suggestions:</span>
          <button
            v-for="tag in quickTags"
            :key="tag.label"
            type="button"
            class="font-azeret text-[10px] tracking-wider uppercase px-2.5 py-1 bg-[#F3EFE7] hover:bg-[#B6965D] hover:text-white border border-[#E2DDD5] text-stone-700 transition-colors shrink-0"
            @click="selectTag(tag)"
          >
            {{ tag.label }}
          </button>
        </div>

        <!-- Search Results List -->
        <div class="max-h-96 overflow-y-auto p-6 space-y-3">
          <div v-if="loading" class="text-center py-10 font-azeret text-xs tracking-widest uppercase text-stone-500">
            Searching the atelier collection...
          </div>

          <div v-else-if="searched && results.length === 0" class="text-center py-10 font-azeret text-xs tracking-widest uppercase text-stone-500">
            No creations found matching "{{ searchQuery }}".
          </div>

          <div v-else-if="!searched" class="text-center py-8 font-azeret text-xs tracking-widest uppercase text-stone-400">
            Type at least 2 characters to discover pieces.
          </div>

          <div
            v-for="product in results"
            :key="product.id"
            class="group flex items-center justify-between p-3 bg-white border border-[#E2DDD5] hover:border-[#B6965D] transition-all cursor-pointer"
            @click="selectProduct(product)"
          >
            <div class="flex items-center space-x-4">
              <div class="w-14 h-14 bg-stone-50 flex items-center justify-center overflow-hidden border border-stone-100 shrink-0">
                <img :src="getProductImage(product)" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
              </div>
              <div>
                <span class="font-azeret text-[9px] uppercase tracking-widest text-[#B6965D] block">
                  {{ product.category?.name || 'Fine Jewellery' }}
                </span>
                <h4 class="font-playfair text-base text-[#1A1A1A] group-hover:text-[#B6965D] transition-colors">
                  {{ product.name }}
                </h4>
              </div>
            </div>

            <div class="text-right shrink-0 ml-4">
              <CurrencyDisplay :amount="product.final_price || product.price || product.base_price" bold />
              <span class="block font-azeret text-[9px] text-stone-400 uppercase tracking-wider">VAT Inc.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-down {
  animation: slideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
