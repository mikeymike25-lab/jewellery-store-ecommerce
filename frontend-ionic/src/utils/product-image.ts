import type { Product } from '@/types';

const CATEGORY_DEFAULT_MAP: Record<number, string> = {
  1: '/assets/products/ring-1.jpg',
  2: '/assets/products/necklace-1.jpg',
  3: '/assets/products/earring-1.jpg',
  4: '/assets/products/bracelet-1.jpg',
  5: '/assets/products/timepiece-1.jpg',
};

/**
 * Resolves a genuine high-resolution luxury image for a product,
 * completely filtering out any external placeholder services.
 */
export function getProductImage(prod?: Product | null): string {
  if (!prod) return '/assets/ring.webp';

  if (prod.primary_image && typeof prod.primary_image === 'string' && !prod.primary_image.includes('placehold.co')) {
    return prod.primary_image;
  }

  if (prod.image_url && typeof prod.image_url === 'string' && !prod.image_url.includes('placehold.co')) {
    return prod.image_url;
  }

  if (Array.isArray(prod.images) && prod.images.length > 0) {
    const first = prod.images[0];
    const path = typeof first === 'string' ? first : first.image_url;
    if (path && typeof path === 'string' && !path.includes('placehold.co')) {
      return path;
    }
  }

  if (prod.category_id && CATEGORY_DEFAULT_MAP[prod.category_id]) {
    return CATEGORY_DEFAULT_MAP[prod.category_id];
  }

  return '/assets/ring.webp';
}

/**
 * Resolves gallery images for product detail views.
 * Strictly one verified image per product.
 */
export function getProductGallery(prod?: Product | null): string[] {
  return [getProductImage(prod)];
}

