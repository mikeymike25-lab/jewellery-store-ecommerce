<script setup lang="ts">
interface SortOption {
  value: string;
  label: string;
  description: string;
}

interface Props {
  isOpen: boolean;
  selectedSort: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'select', value: string): void;
}>();

const sortOptions: SortOption[] = [
  {
    value: 'popularity',
    label: 'Relevance',
    description: 'Curated Atelier Selection',
  },
  {
    value: 'newest',
    label: 'Newest Arrivals',
    description: 'Freshly Crafted Additions',
  },
  {
    value: 'price-low',
    label: 'Price: Low to High',
    description: 'Ascending Acquisition Value',
  },
  {
    value: 'price-high',
    label: 'Price: High to Low',
    description: 'Descending Acquisition Value',
  },
];

function handleSelect(val: string) {
  emit('select', val);
  emit('close');
}
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
        @click="emit('close')"
      >
        <!-- Modal Card (Bottom Sheet on Mobile, Centered on Tablet/Desktop) -->
        <div
          class="w-full sm:max-w-md bg-[#F3EFE7] border border-[#E2DDD5] shadow-2xl overflow-hidden flex flex-col max-h-[85vh] animate-slide-up sm:animate-fade-in"
          @click.stop
        >
          <!-- Header -->
          <div class="p-6 border-b border-[#E2DDD5] flex items-center justify-between bg-[#F8F5EE]">
            <div>
              <span class="font-azeret text-[10px] uppercase tracking-[0.25em] text-[#B6965D] font-semibold block mb-1">
                Atelier Curation
              </span>
              <h3 class="font-playfair text-2xl text-[#1A1A1A]">
                Sort Creations
              </h3>
            </div>
            <button
              type="button"
              class="w-9 h-9 rounded-full border border-stone-300 flex items-center justify-center text-stone-500 hover:text-black hover:border-black transition-colors"
              aria-label="Close sort modal"
              @click="emit('close')"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Options List -->
          <div class="p-6 space-y-3 overflow-y-auto flex-grow">
            <button
              v-for="opt in sortOptions"
              :key="opt.value"
              type="button"
              class="w-full text-left p-4 min-h-[56px] border transition-all duration-200 flex items-center justify-between font-azeret active:scale-[0.99] group"
              :class="
                selectedSort === opt.value
                  ? 'border-[#B6965D] bg-white ring-1 ring-[#B6965D] shadow-sm'
                  : 'bg-white/70 border-[#E2DDD5] hover:border-[#B6965D] hover:bg-white'
              "
              @click="handleSelect(opt.value)"
            >
              <div>
                <p
                  class="text-xs uppercase tracking-[0.15em] font-semibold"
                  :class="selectedSort === opt.value ? 'text-[#B6965D]' : 'text-[#1A1A1A] group-hover:text-[#B6965D]'"
                >
                  {{ opt.label }}
                </p>
                <p class="text-[10px] text-stone-500 tracking-wider mt-0.5">
                  {{ opt.description }}
                </p>
              </div>

              <!-- Selection Indicator -->
              <div
                class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                :class="
                  selectedSort === opt.value
                    ? 'bg-[#B6965D] text-white'
                    : 'border border-stone-300 group-hover:border-[#B6965D]'
                "
              >
                <svg
                  v-if="selectedSort === opt.value"
                  class="w-3.5 h-3.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </button>
          </div>

          <!-- Footer -->
          <div class="p-4 border-t border-[#E2DDD5] bg-[#EBE6DC] flex justify-end">
            <button
              type="button"
              class="w-full sm:w-auto px-8 py-3.5 min-h-[48px] bg-black hover:bg-stone-900 text-white font-azeret text-xs uppercase tracking-[0.2em] font-bold transition-all shadow-sm active:scale-95 inline-flex items-center justify-center"
              @click="emit('close')"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
