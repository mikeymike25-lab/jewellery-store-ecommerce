<script setup lang="ts">
import { watch, onMounted, onUnmounted } from 'vue';

interface Props {
  isOpen: boolean;
  title?: string;
  subtitle?: string;
  maxWidth?: string;
  showClose?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  title: '',
  subtitle: '',
  maxWidth: 'max-w-lg',
  showClose: true,
});

const emit = defineEmits<{
  (e: 'close'): void;
}>();

function handleBackdropClick(e: MouseEvent) {
  if (e.target === e.currentTarget) {
    emit('close');
  }
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && props.isOpen) {
    emit('close');
  }
}

watch(() => props.isOpen, (open) => {
  if (open) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
  document.body.style.overflow = '';
});
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-luxury">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        @click="handleBackdropClick"
      >
        <div
          class="modal-card w-full bg-[#F3EFE7] border border-[#E2DDD5] shadow-2xl rounded-none overflow-hidden transform transition-all p-6 sm:p-8"
          :class="maxWidth"
        >
          <!-- Modal Header -->
          <div class="flex items-start justify-between pb-4 border-b border-[#E2DDD5]">
            <div>
              <h3 v-if="title" class="font-playfair text-2xl font-normal text-[#1A1A1A] tracking-wide">
                {{ title }}
              </h3>
              <p v-if="subtitle" class="font-azeret text-xs text-[#767676] mt-1 tracking-wider uppercase">
                {{ subtitle }}
              </p>
            </div>
            <button
              v-if="showClose"
              type="button"
              class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors p-1"
              aria-label="Close modal"
              @click="emit('close')"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="py-4">
            <slot />
          </div>

          <!-- Modal Footer -->
          <div v-if="$slots.footer" class="pt-4 border-t border-[#E2DDD5] flex justify-end gap-3">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-luxury-enter-active,
.modal-luxury-leave-active {
  transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-luxury-enter-active .modal-card,
.modal-luxury-leave-active .modal-card {
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
  will-change: transform, opacity;
}
.modal-luxury-enter-from,
.modal-luxury-leave-to {
  opacity: 0;
}
.modal-luxury-enter-from .modal-card,
.modal-luxury-leave-to .modal-card {
  opacity: 0;
  transform: scale3d(0.95, 0.95, 1) translate3d(0, 10px, 0);
}
</style>
