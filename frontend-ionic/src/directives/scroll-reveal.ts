import type { Directive, DirectiveBinding } from 'vue';

/**
 * Lightweight, hardware-accelerated scroll & entrance directive.
 * Uses GPU-accelerated CSS animations with staggered delays,
 * eliminating JavaScript scroll lag and avoiding IntersectionObserver delays inside Ionic shadow roots.
 */
export const vScrollReveal: Directive<HTMLElement, number | { delay?: number } | undefined> = {
  mounted(el: HTMLElement, binding: DirectiveBinding<number | { delay?: number } | undefined>) {
    const prefersReducedMotion = typeof window !== 'undefined' &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
      el.style.opacity = '1';
      return;
    }

    el.classList.add('product-card-scroll');

    let delay = 0;
    if (typeof binding.value === 'number') {
      delay = binding.value;
    } else if (binding.value && typeof binding.value.delay === 'number') {
      delay = binding.value.delay;
    }

    if (delay > 0) {
      el.style.animationDelay = `${delay}ms`;
    }
  },

  updated(el: HTMLElement, binding: DirectiveBinding<number | { delay?: number } | undefined>) {
    let delay = 0;
    if (typeof binding.value === 'number') {
      delay = binding.value;
    } else if (binding.value && typeof binding.value.delay === 'number') {
      delay = binding.value.delay;
    }

    if (delay > 0) {
      el.style.animationDelay = `${delay}ms`;
    }
  },
};
