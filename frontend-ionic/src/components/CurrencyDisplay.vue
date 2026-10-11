<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  amount: number | string | null | undefined;
  showDecimals?: boolean;
  currencySymbol?: string;
  size?: 'xs' | 'sm' | 'base' | 'lg' | 'xl' | '2xl';
  strikethrough?: boolean;
  bold?: boolean;
  colorClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  amount: 0,
  showDecimals: true,
  currencySymbol: '₱',
  size: 'base',
  strikethrough: false,
  bold: false,
  colorClass: '',
});

const formattedAmount = computed(() => {
  const num = typeof props.amount === 'string' ? parseFloat(props.amount) : Number(props.amount || 0);
  if (isNaN(num)) return '0.00';

  return num.toLocaleString('en-PH', {
    minimumFractionDigits: props.showDecimals ? 2 : 0,
    maximumFractionDigits: props.showDecimals ? 2 : 0,
  });
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'xs': return 'text-xs';
    case 'sm': return 'text-sm';
    case 'base': return 'text-base';
    case 'lg': return 'text-lg';
    case 'xl': return 'text-xl font-medium';
    case '2xl': return 'text-2xl font-semibold';
    default: return 'text-base';
  }
});
</script>

<template>
  <span
    class="currency-display inline-flex items-baseline tracking-tight font-azeret"
    :class="[
      sizeClasses,
      bold ? 'font-bold' : '',
      strikethrough ? 'line-through text-stone-400 font-normal' : (colorClass || 'text-stone-900'),
    ]"
  >
    <span class="currency-symbol mr-0.5 text-[0.85em] font-normal opacity-90">{{ currencySymbol }}</span>
    <span class="currency-value">{{ formattedAmount }}</span>
  </span>
</template>

<style scoped>
.currency-display {
  font-feature-settings: "tnum" 1;
}
</style>
