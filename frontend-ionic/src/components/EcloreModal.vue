<script setup lang="ts">
import { watch, onMounted, onUnmounted } from 'vue';

interface Props {
  isOpen: boolean;
  title?: string;
  subtitle?: string;
  maxWidth?: string;
  showClose?: boolean;
  closeLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  title: '',
  subtitle: '',
  maxWidth: 'max-w-lg',
  showClose: true,
  closeLabel: 'Back',
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
        class="fixed inset-0 z-[9990] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto overscroll-contain"
        @click="handleBackdropClick"
      >
        <div
          class="modal-card w-full bg-[#F3EFE7] border border-[#E2DDD5] shadow-2xl rounded-none overflow-hidden transform transition-all max-h-[92dvh] sm:max-h-[88vh] flex flex-col p-4 sm:p-7 my-auto"
          :class="maxWidth"
        >
          <!-- Modal Header -->
          <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-[#E2DDD5] shrink-0 sticky top-0 bg-[#F3EFE7] z-20">
            <div class="min-w-0 pr-3">
              <h3 v-if="title" class="font-playfair text-xl sm:text-2xl font-normal text-[#1A1A1A] tracking-wide truncate">
                {{ title }}
              </h3>
              <p v-if="subtitle" class="font-azeret text-[10px] sm:text-xs text-[#767676] mt-0.5 tracking-wider uppercase truncate">
                {{ subtitle }}
              </p>
            </div>
            <button
              v-if="showClose"
              type="button"
              class="text-stone-700 hover:text-black bg-[#EAE5DA] hover:bg-[#DDD6C7] rounded-full px-3.5 py-1.5 text-xs font-azeret uppercase tracking-wider transition-all flex items-center gap-1.5 shrink-0 min-h-[38px] active:scale-95 cursor-pointer shadow-xs border border-[#DCD5C8]"
              :aria-label="closeLabel || 'Back'"
              @click="emit('close')"
            >
              <svg class="w-3.5 h-3.5 text-[#B6965D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span class="font-bold">{{ closeLabel || 'Back' }}</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain py-3 sm:py-4 pr-1">
            <slot />
          </div>

          <!-- Modal Footer -->
          <div v-if="$slots.footer" class="pt-3 border-t border-[#E2DDD5] flex justify-end gap-3 shrink-0">
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
