<script setup lang="ts">
import { ref, watch } from 'vue';

interface FilterState {
  color?: string;
  material?: string;
  gemstone?: string;
  diamonds?: string;
  price?: string;
  category?: string;
}

interface Props {
  isOpen: boolean;
  activeFilters?: FilterState;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  activeFilters: () => ({}),
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'apply', filters: FilterState): void;
  (e: 'reset'): void;
}>();

const selectedColor = ref(props.activeFilters.color || '');
const selectedMaterial = ref(props.activeFilters.material || '');
const selectedGemstone = ref(props.activeFilters.gemstone || '');
const selectedDiamonds = ref(props.activeFilters.diamonds || '');
const selectedPrice = ref(props.activeFilters.price || '');
const selectedCategory = ref(props.activeFilters.category || '');

function syncFromProps() {
  selectedColor.value = props.activeFilters?.color || '';
  selectedMaterial.value = props.activeFilters?.material || '';
  selectedGemstone.value = props.activeFilters?.gemstone || '';
  selectedDiamonds.value = props.activeFilters?.diamonds || '';
  selectedPrice.value = props.activeFilters?.price || '';
  selectedCategory.value = props.activeFilters?.category || '';
}

watch(() => props.isOpen, (open) => {
  if (open) {
    syncFromProps();
  }
});

watch(() => props.activeFilters, () => {
  syncFromProps();
}, { deep: true });

const colorOptions = [
  { label: 'Yellow Gold', value: 'yellow gold' },
  { label: 'White Gold', value: 'white gold' },
  { label: 'Rose Gold', value: 'rose gold' },
  { label: 'Platinum', value: 'platinum' },
  { label: 'Sterling Silver', value: 'sterling silver' },
  { label: 'Two-Tone', value: 'two-tone' },
];

const materialOptions = [
  { label: '18K Gold', value: '18k gold' },
  { label: '14K Gold', value: '14k gold' },
  { label: 'Platinum 950', value: 'platinum 950' },
  { label: '925 Sterling Silver', value: '925 sterling silver' },
];

const gemstoneOptions = [
  { label: 'Diamond', value: 'diamond' },
  { label: 'Ruby', value: 'ruby' },
  { label: 'Emerald', value: 'emerald' },
  { label: 'Sapphire', value: 'sapphire' },
  { label: 'Pearl', value: 'pearl' },
  { label: 'No Gemstone', value: 'no gemstone' },
];

const caratOptions = [
  { label: 'Accent (< 0.10 ct)', value: 'accent (< 0.10 ct)' },
  { label: '0.10 - 0.29 ct', value: '0.10 - 0.29 ct' },
  { label: '0.30 - 0.49 ct', value: '0.30 - 0.49 ct' },
  { label: '0.50 - 0.99 ct', value: '0.50 - 0.99 ct' },
  { label: '1.00+ ct', value: '1.00 + ct' },
];

const priceOptions = [
  { label: 'Under ₱30,000', value: 'under-30k' },
  { label: '₱30,000 - ₱60,000', value: '30k-60k' },
  { label: '₱60,000 - ₱100,000', value: '60k-100k' },
  { label: 'Over ₱100,000', value: 'over-100k' },
];

const categoryOptions = [
  { label: 'Rings', value: 'rings' },
  { label: 'Necklaces & Pendants', value: 'necklaces-pendants' },
  { label: 'Earrings', value: 'earrings' },
  { label: 'Bracelets & Bangles', value: 'bracelets-bangles' },
];

function applyFilters() {
  emit('apply', {
    color: selectedColor.value,
    material: selectedMaterial.value,
    gemstone: selectedGemstone.value,
    diamonds: selectedDiamonds.value,
    price: selectedPrice.value,
    category: selectedCategory.value,
  });
  emit('close');
}

function resetFilters() {
  selectedColor.value = '';
  selectedMaterial.value = '';
  selectedGemstone.value = '';
  selectedDiamonds.value = '';
  selectedPrice.value = '';
  selectedCategory.value = '';
  emit('reset');
  emit('close');
}
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[9999] flex justify-end bg-black/60 backdrop-blur-xs"
        @click.self="emit('close')"
      >
        <div
          class="w-full max-w-md bg-[#F3EFE7] h-full h-screen shadow-2xl flex flex-col border-l border-[#E2DDD5] overflow-hidden"
          style="height: 100dvh; max-height: 100dvh;"
        >
          <!-- Header -->
        <div class="p-6 border-b border-[#E2DDD5] flex items-center justify-between">
          <div>
            <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Atelier Facets</span>
            <h3 class="font-playfair text-2xl text-[#1A1A1A]">Refine Selection</h3>
          </div>
          <button
            type="button"
            class="text-stone-500 hover:text-[#1A1A1A] p-1"
            aria-label="Close filters"
            @click="emit('close')"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Facets Body -->
        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-6 space-y-6 font-azeret">
          <!-- Category -->
          <div>
            <h4 class="text-xs uppercase tracking-widest text-[#1A1A1A] mb-3 font-semibold">Category</h4>
            <div class="flex flex-wrap gap-2.5">
              <button
                v-for="cat in categoryOptions"
                :key="cat.value"
                type="button"
                class="rounded-full px-5 py-2.5 min-h-[42px] text-xs tracking-wider uppercase border transition-all duration-200 inline-flex items-center justify-center active:scale-95"
                :class="selectedCategory === cat.value ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow-sm' : 'bg-[#E8E2D5] text-[#1A1A1A] border-[#D4CEB8] hover:border-[#B6965D] hover:bg-[#DDD6C7]'"
                @click="selectedCategory = selectedCategory === cat.value ? '' : cat.value"
              >
                {{ cat.label }}
              </button>
            </div>
          </div>

          <!-- Metal / Color -->
          <div>
            <h4 class="text-xs uppercase tracking-widest text-[#1A1A1A] mb-3 font-semibold">Precious Metal Tone</h4>
            <div class="flex flex-wrap gap-2.5">
              <button
                v-for="color in colorOptions"
                :key="color.value"
                type="button"
                class="rounded-full px-5 py-2.5 min-h-[42px] text-xs tracking-wider uppercase border transition-all duration-200 inline-flex items-center justify-center active:scale-95"
                :class="selectedColor === color.value ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow-sm' : 'bg-[#E8E2D5] text-[#1A1A1A] border-[#D4CEB8] hover:border-[#B6965D] hover:bg-[#DDD6C7]'"
                @click="selectedColor = selectedColor === color.value ? '' : color.value"
              >
                {{ color.label }}
              </button>
            </div>
          </div>

          <!-- Material Purity -->
          <div>
            <h4 class="text-xs uppercase tracking-widest text-[#1A1A1A] mb-3 font-semibold">Metal Purity</h4>
            <div class="flex flex-wrap gap-2.5">
              <button
                v-for="mat in materialOptions"
                :key="mat.value"
                type="button"
                class="rounded-full px-5 py-2.5 min-h-[42px] text-xs tracking-wider uppercase border transition-all duration-200 inline-flex items-center justify-center active:scale-95"
                :class="selectedMaterial === mat.value ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow-sm' : 'bg-[#E8E2D5] text-[#1A1A1A] border-[#D4CEB8] hover:border-[#B6965D] hover:bg-[#DDD6C7]'"
                @click="selectedMaterial = selectedMaterial === mat.value ? '' : mat.value"
              >
                {{ mat.label }}
              </button>
            </div>
          </div>

          <!-- Gemstone -->
          <div>
            <h4 class="text-xs uppercase tracking-widest text-[#1A1A1A] mb-3 font-semibold">Gemstone</h4>
            <div class="flex flex-wrap gap-2.5">
              <button
                v-for="gem in gemstoneOptions"
                :key="gem.value"
                type="button"
                class="rounded-full px-5 py-2.5 min-h-[42px] text-xs tracking-wider uppercase border transition-all duration-200 inline-flex items-center justify-center active:scale-95"
                :class="selectedGemstone === gem.value ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow-sm' : 'bg-[#E8E2D5] text-[#1A1A1A] border-[#D4CEB8] hover:border-[#B6965D] hover:bg-[#DDD6C7]'"
                @click="selectedGemstone = selectedGemstone === gem.value ? '' : gem.value"
              >
                {{ gem.label }}
              </button>
            </div>
          </div>

          <!-- Diamond Carat -->
          <div>
            <h4 class="text-xs uppercase tracking-widest text-[#1A1A1A] mb-3 font-semibold">Diamond Carat Weight</h4>
            <div class="flex flex-wrap gap-2.5">
              <button
                v-for="carat in caratOptions"
                :key="carat.value"
                type="button"
                class="rounded-full px-5 py-2.5 min-h-[42px] text-xs tracking-wider uppercase border transition-all duration-200 inline-flex items-center justify-center active:scale-95"
                :class="selectedDiamonds === carat.value ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow-sm' : 'bg-[#E8E2D5] text-[#1A1A1A] border-[#D4CEB8] hover:border-[#B6965D] hover:bg-[#DDD6C7]'"
                @click="selectedDiamonds = selectedDiamonds === carat.value ? '' : carat.value"
              >
                {{ carat.label }}
              </button>
            </div>
          </div>

          <!-- Price Range -->
          <div>
            <h4 class="text-xs uppercase tracking-widest text-[#1A1A1A] mb-3 font-semibold">Price Bracket (PHP)</h4>
            <div class="flex flex-wrap gap-2.5">
              <button
                v-for="p in priceOptions"
                :key="p.value"
                type="button"
                class="rounded-full px-5 py-2.5 min-h-[42px] text-xs tracking-wider uppercase border transition-all duration-200 inline-flex items-center justify-center active:scale-95"
                :class="selectedPrice === p.value ? 'bg-[#B6965D] text-white border-[#B6965D] font-bold shadow-sm' : 'bg-[#E8E2D5] text-[#1A1A1A] border-[#D4CEB8] hover:border-[#B6965D] hover:bg-[#DDD6C7]'"
                @click="selectedPrice = selectedPrice === p.value ? '' : p.value"
              >
                {{ p.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-4 sm:p-6 border-t border-[#E2DDD5] bg-[#EBE6DC] flex items-center gap-3 shrink-0 sticky bottom-0 z-10 pb-[max(1.5rem,calc(env(safe-area-inset-bottom,0px)+1.25rem))]">
          <button
            type="button"
            class="flex-1 min-h-[48px] py-3.5 px-4 rounded-full border border-[#B6965D] text-[#B6965D] hover:bg-[#B6965D]/15 bg-white font-azeret text-xs uppercase tracking-[0.18em] font-bold transition-all shadow-xs active:scale-95 inline-flex items-center justify-center cursor-pointer"
            @click="resetFilters"
          >
            Reset All
          </button>
          <button
            type="button"
            class="flex-1 min-h-[48px] py-3.5 px-4 rounded-full bg-[#B6965D] hover:bg-[#9A7E4D] text-white border border-[#B6965D] font-azeret text-xs uppercase tracking-[0.18em] font-bold transition-all shadow-md active:scale-95 inline-flex items-center justify-center gap-2 cursor-pointer"
            @click="applyFilters"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-white inline-block flex-shrink-0"></span>
            <span>Apply Filters</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
  </Teleport>
</template>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.3s ease;
}

.drawer-enter-active > div,
.drawer-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}

.drawer-enter-from > div,
.drawer-leave-to > div {
  transform: translateX(100%);
}
</style>
