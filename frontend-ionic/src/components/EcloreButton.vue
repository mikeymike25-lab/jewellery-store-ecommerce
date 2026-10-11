<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'primary' | 'secondary' | 'outline' | 'outline-gold' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
  loading?: boolean;
  block?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false,
  block: false,
});

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void;
}>();

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-[#B6965D] text-white hover:bg-[#9A7E4D] active:bg-[#846B40] border border-[#B6965D] shadow-sm';
    case 'secondary':
      return 'bg-[#1A1A1A] text-white hover:bg-[#2C2C2C] active:bg-[#333333] border border-[#1A1A1A] shadow-sm';
    case 'outline':
      return 'bg-transparent text-[#1A1A1A] border border-[#D4CEB8] hover:border-[#B6965D] hover:text-[#B6965D] hover:bg-[#FAF8F5] active:bg-[#F3EFE7]';
    case 'outline-gold':
      return 'bg-transparent text-[#B6965D] border border-[#B6965D] hover:bg-[#B6965D] hover:text-white active:bg-[#9A7E4D]';
    case 'ghost':
      return 'bg-transparent text-[#1A1A1A] hover:bg-[#EBE6DC] active:bg-[#E2DDD5] border border-transparent';
    case 'danger':
      return 'bg-red-700 text-white hover:bg-red-800 active:bg-red-900 border border-transparent';
    default:
      return 'bg-[#B6965D] text-white border border-[#B6965D]';
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'eclore-btn-sm';
    case 'lg':
      return 'eclore-btn-lg';
    case 'md':
    default:
      return 'eclore-btn-md';
  }
});

function handleClick(e: MouseEvent) {
  if (!props.disabled && !props.loading) {
    emit('click', e);
  }
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="eclore-button relative inline-flex items-center justify-center font-azeret uppercase transition-all duration-200 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed select-none"
    :class="[
      variantClasses,
      sizeClasses,
      block ? 'w-full block-btn' : '',
    ]"
    @click="handleClick"
  >
    <span v-if="loading" class="mr-2 inline-flex items-center">
      <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
  </button>
</template>

<style scoped>
.eclore-button {
  letter-spacing: 0.15em;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  font-weight: 500;
  cursor: pointer;
  border-radius: 0;
  margin: 4px 0;
  -webkit-tap-highlight-color: transparent !important;
  outline: none !important;
  user-select: none;
}

.eclore-btn-sm {
  min-height: 38px !important;
  padding: 8px 16px !important;
  font-size: 11px !important;
  line-height: 1.25 !important;
}

.eclore-btn-md {
  min-height: 48px !important;
  padding: 14px 28px !important;
  font-size: 12px !important;
  line-height: 1.4 !important;
}

.eclore-btn-lg {
  min-height: 56px !important;
  padding: 18px 36px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  line-height: 1.5 !important;
}

.block-btn {
  width: 100% !important;
  display: flex !important;
}
</style>
