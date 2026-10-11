<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { Order, OrderItem } from '@/types';
import EcloreButton from '@/components/EcloreButton.vue';
import { useOrdersStore } from '@/stores/orders.store';

const props = defineProps<{
  isOpen: boolean;
  order: Order | null;
  preselectedItem?: OrderItem | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submitted', rmaNumber: string): void;
}>();

const ordersStore = useOrdersStore();

const selectedItemId = ref<number | null>(null);
const reason = ref('defective');
const description = ref('');
const customerNotes = ref('');
const selectedPhotos = ref<File[]>([]);
const photoPreviews = ref<string[]>([]);

const isSubmitting = ref(false);
const errorMessage = ref('');
const submittedRmaNumber = ref<string | null>(null);

const reasonsList = [
  { value: 'item_does_not_fit', label: 'Ring / Bracelet Sizing Adjustment' },
  { value: 'defective', label: 'Manufacturing Defect / Gemstone Setting Inspection' },
  { value: 'quality_issues', label: 'Finish, Luster or Material Concern' },
  { value: 'item_not_as_described', label: 'Discrepancy with Creation Specification' },
  { value: 'customer_dissatisfaction', label: 'Atelier Exchange Consultation' },
  { value: 'wrong_item', label: 'Incorrect Piece Delivered' },
  { value: 'other', label: 'Other Concierge Service' },
];

const availableItems = computed(() => {
  if (!props.order) return [];
  return props.order.order_items || props.order.items || [];
});

watch(
  () => [props.isOpen, props.preselectedItem, props.order],
  () => {
    if (props.isOpen) {
      errorMessage.value = '';
      submittedRmaNumber.value = null;
      description.value = '';
      customerNotes.value = '';
      selectedPhotos.value = [];
      photoPreviews.value = [];
      if (props.preselectedItem) {
        selectedItemId.value = props.preselectedItem.id;
      } else if (availableItems.value.length > 0) {
        selectedItemId.value = availableItems.value[0].id;
      }
    }
  },
  { immediate: true }
);

function handlePhotoChange(event: Event) {
  const input = event.target as HTMLInputElement;
  if (!input.files || input.files.length === 0) return;

  const files = Array.from(input.files);
  for (const file of files) {
    if (selectedPhotos.value.length >= 5) break;
    if (file.size > 2 * 1024 * 1024) {
      errorMessage.value = 'Each attached photograph must be under 2MB.';
      continue;
    }
    selectedPhotos.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => {
      if (e.target?.result) {
        photoPreviews.value.push(e.target.result as string);
      }
    };
    reader.readAsDataURL(file);
  }
}

function removePhoto(index: number) {
  selectedPhotos.value.splice(index, 1);
  photoPreviews.value.splice(index, 1);
}

const isValid = computed(() => {
  return (
    !!props.order &&
    !!selectedItemId.value &&
    reason.value.length > 0 &&
    description.value.trim().length >= 10
  );
});

async function handleSubmit() {
  if (!isValid.value || !props.order || !selectedItemId.value) return;

  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    const formData = new FormData();
    formData.append('order_id', String(props.order.id));
    formData.append('order_item_id', String(selectedItemId.value));
    formData.append('reason', reason.value);
    formData.append('description', description.value.trim());
    if (customerNotes.value.trim()) {
      formData.append('customer_notes', customerNotes.value.trim());
    }

    selectedPhotos.value.forEach((photo) => {
      formData.append('photos[]', photo);
    });

    const res = await ordersStore.submitRmaRequest(formData);
    submittedRmaNumber.value = res.rma_number;
    emit('submitted', res.rma_number);
  } catch (err: any) {
    errorMessage.value = err.message || 'An error occurred submitting your claim. Please review your details.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <div v-if="isOpen && order" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="emit('close')" />

    <div class="flex min-h-full items-center justify-center p-3 sm:p-6">
      <div class="relative w-full max-w-xl bg-[#F8F7F2] border border-[#E2DDD5] shadow-2xl p-5 sm:p-8 text-[#1A1A1A] animate-fadeIn">
        
        <!-- SUCCESS STATE -->
        <div v-if="submittedRmaNumber" class="text-center py-6 space-y-4">
          <div class="w-14 h-14 bg-[#B6965D]/10 border border-[#B6965D] rounded-full mx-auto flex items-center justify-center text-[#B6965D]">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Claim Initialized</span>
          <h3 class="font-playfair text-2xl text-[#1A1A1A]">After-Sales Service Record Created</h3>
          
          <div class="bg-white border border-[#E2DDD5] p-4 max-w-sm mx-auto my-3 font-azeret">
            <span class="text-xs text-[#767676] uppercase block">Reference Number</span>
            <span class="text-xl font-bold text-[#B6965D] tracking-wider mt-0.5 block">{{ submittedRmaNumber }}</span>
          </div>

          <p class="font-azeret text-xs text-[#767676] max-w-md mx-auto leading-relaxed">
            Our master jewelers have received your dossier. Our Client Relations Concierge will contact you within 24 hours to organize insured courier collection or private salon reception.
          </p>

          <div class="pt-4">
            <EcloreButton variant="primary" size="md" @click="emit('close')">
              Return to Orders
            </EcloreButton>
          </div>
        </div>

        <!-- FORM STATE -->
        <div v-else class="space-y-5">
          <!-- Header -->
          <div class="flex items-start justify-between border-b border-[#E2DDD5] pb-4">
            <div>
              <span class="font-azeret text-[10px] uppercase tracking-widest text-[#B6965D]">Atelier Care & Returns</span>
              <h3 class="font-playfair text-xl sm:text-2xl text-[#1A1A1A] mt-1">Initiate After-Sales Claim</h3>
              <p class="font-azeret text-xs text-[#767676] mt-0.5">Order #{{ order.order_number }}</p>
            </div>

            <button
              type="button"
              class="p-2 text-[#767676] hover:text-[#1A1A1A] transition-colors -mr-2 -mt-2"
              @click="emit('close')"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="p-3 text-xs bg-red-50 text-red-700 border border-red-200 font-azeret">
            {{ errorMessage }}
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Piece to Return/Repair -->
            <div>
              <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1.5 font-semibold">
                Select Creation to Service
              </label>
              <select
                v-model="selectedItemId"
                class="w-full bg-white border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                required
              >
                <option v-for="item in availableItems" :key="item.id" :value="item.id">
                  {{ item.product_name }} ({{ item.product_sku || 'SKU ECL' }}) - Qty: {{ item.quantity }}
                </option>
              </select>
            </div>

            <!-- Service Reason -->
            <div>
              <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1.5 font-semibold">
                Primary Reason / Request Type
              </label>
              <select
                v-model="reason"
                class="w-full bg-white border border-[#E2DDD5] px-3.5 py-2.5 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
                required
              >
                <option v-for="opt in reasonsList" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <!-- Description -->
            <div>
              <div class="flex justify-between items-center mb-1.5">
                <label class="font-azeret text-[10px] uppercase tracking-widest text-[#767676] font-semibold">
                  Detailed Atelier Observation
                </label>
                <span class="font-azeret text-[10px] text-[#767676]">{{ description.length }}/1000 (min 10)</span>
              </div>
              <textarea
                v-model="description"
                rows="3"
                placeholder="Please describe the fit adjustment, craftsmanship note, or service request in detail..."
                class="w-full bg-white border border-[#E2DDD5] p-3 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D] resize-none"
                required
                minlength="10"
                maxlength="1000"
              />
            </div>

            <!-- Customer Notes -->
            <div>
              <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1.5 font-semibold">
                Special Instructions (Optional)
              </label>
              <input
                v-model="customerNotes"
                type="text"
                placeholder="e.g., Preferred collection time, flagship salon handoff, etc."
                class="w-full bg-white border border-[#E2DDD5] px-3.5 py-2 text-xs font-azeret text-[#1A1A1A] focus:outline-none focus:border-[#B6965D]"
              />
            </div>

            <!-- Photographic Dossier Upload -->
            <div>
              <label class="block font-azeret text-[10px] uppercase tracking-widest text-[#767676] mb-1.5 font-semibold">
                Photographic Evidence (Optional, Up to 5 photos)
              </label>
              
              <!-- Thumbnails -->
              <div v-if="photoPreviews.length > 0" class="flex flex-wrap gap-2 mb-2">
                <div
                  v-for="(preview, idx) in photoPreviews"
                  :key="idx"
                  class="relative w-16 h-16 border border-[#B6965D] rounded overflow-hidden group"
                >
                  <img :src="preview" alt="Preview" class="w-full h-full object-cover" />
                  <button
                    type="button"
                    class="absolute inset-0 bg-black/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    @click="removePhoto(idx)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="border-2 border-dashed border-[#E2DDD5] bg-white p-3 text-center cursor-pointer hover:border-[#B6965D] transition-colors relative">
                <input
                  type="file"
                  multiple
                  accept="image/jpeg,image/png,image/webp"
                  class="absolute inset-0 opacity-0 cursor-pointer"
                  @change="handlePhotoChange"
                />
                <div class="flex items-center justify-center space-x-2 text-xs font-azeret text-[#767676]">
                  <svg class="w-4 h-4 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>Attach High-Resolution Photographs (Max 5)</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-[#E2DDD5] pt-4 flex items-center justify-end space-x-3">
              <EcloreButton variant="outline" size="sm" type="button" @click="emit('close')">
                Cancel
              </EcloreButton>
              <EcloreButton
                variant="primary"
                size="sm"
                type="submit"
                :disabled="!isValid || isSubmitting"
              >
                {{ isSubmitting ? 'Transmitting Dossier...' : 'Submit Service Claim' }}
              </EcloreButton>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
