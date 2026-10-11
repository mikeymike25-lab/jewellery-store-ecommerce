<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import EcloreButton from '@/components/EcloreButton.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { apiClient } from '@/api/client';
import type { Product } from '@/types';

interface Story {
  id: number;
  title: string;
  slug: string;
  excerpt?: string | null;
  content?: string | null;
  category?: string;
  featured_image?: string | null;
  published_at?: string | null;
}

const route = useRoute();
const router = useRouter();

const story = ref<Story | null>(null);
const featuredProducts = ref<Product[]>([]);
const relatedStories = ref<Story[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

function resolveStoryImage(s: Story | null): string {
  if (!s) return '/assets/necklace.webp';
  if (s.featured_image) return s.featured_image;
  return '/assets/necklace.webp';
}

function resolveProductImage(p: Product): string {
  if (p.primary_image) return p.primary_image;
  if (p.images && p.images.length > 0) {
    const first = p.images[0];
    return typeof first === 'string' ? first : first.image_url;
  }
  return '/assets/ring.webp';
}

function formatDate(dateStr?: string | null): string {
  if (!dateStr) return '2026';
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  } catch {
    return dateStr;
  }
}

async function loadStory() {
  const slug = route.params.slug as string;
  if (!slug) return;
  loading.value = true;
  error.value = null;

  try {
    const res = await apiClient.get<{
      success: boolean;
      story: Story;
      featured_products?: Product[];
      related_stories?: Story[];
    }>(`/v1/stories/${slug}`);

    story.value = res.data?.story || null;
    featuredProducts.value = res.data?.featured_products || [];
    relatedStories.value = res.data?.related_stories || [];
  } catch (err: any) {
    console.warn('Failed to load story from API:', err);
    error.value = 'Article not found.';
  } finally {
    loading.value = false;
  }
}

function navigateToProduct(p: Product) {
  router.push(`/products/${p.slug || p.id}`);
}

onMounted(() => {
  loadStory();
});

watch(
  () => route.params.slug,
  () => {
    loadStory();
  }
);
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <EcloreHeader />

      <main class="min-h-screen bg-[#F3EFE7] text-[#1A1A1A]">
        <!-- Breadcrumb Bar -->
        <nav class="max-w-4xl mx-auto px-4 sm:px-6 pt-8 pb-4 font-azeret text-[10px] uppercase tracking-[0.3em] text-stone-500 flex items-center gap-2">
          <router-link to="/journal" class="hover:text-[#B6965D] transition-colors">Journal</router-link>
          <span>/</span>
          <span class="text-[#1A1A1A] truncate">{{ story?.title || 'Story' }}</span>
        </nav>

        <!-- Loading State -->
        <div v-if="loading" class="max-w-4xl mx-auto px-4 py-16 space-y-8 animate-pulse">
          <div class="h-8 bg-stone-300 w-3/4 rounded"></div>
          <div class="h-96 bg-stone-300 rounded"></div>
          <div class="space-y-4">
            <div class="h-4 bg-stone-300 w-full rounded"></div>
            <div class="h-4 bg-stone-300 w-5/6 rounded"></div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error || !story" class="max-w-xl mx-auto px-4 py-24 text-center space-y-6">
          <h2 class="font-playfair text-3xl text-[#1A1A1A]">Story Not Found</h2>
          <p class="font-azeret text-xs text-stone-500">
            The requested editorial chronicle could not be located or has concluded its public publication cycle.
          </p>
          <EcloreButton variant="primary" size="md" @click="router.push('/journal')">
            Return to Journal
          </EcloreButton>
        </div>

        <!-- Article Layout -->
        <article v-else class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-12">
          <!-- Article Header -->
          <header class="space-y-6 text-center sm:text-left border-b border-[#E2DDD5] pb-10">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
              <span class="font-azeret text-[10px] uppercase tracking-[0.25em] bg-[#B6965D] text-white px-3 py-1 font-bold">
                {{ story.category || 'Maison Feature' }}
              </span>
              <span class="font-azeret text-xs text-stone-500">
                Published &bull; {{ formatDate(story.published_at) }}
              </span>
            </div>

            <h1 class="font-playfair text-3xl sm:text-5xl lg:text-6xl text-[#1A1A1A] font-light leading-[1.15]">
              {{ story.title }}
            </h1>

            <p v-if="story.excerpt" class="font-azeret text-sm sm:text-base text-stone-600 leading-relaxed font-light">
              {{ story.excerpt }}
            </p>

            <!-- Author Byline -->
            <div class="flex items-center justify-center sm:justify-start gap-4 pt-4 border-t border-[#E2DDD5]/60">
              <div class="w-12 h-12 rounded-full bg-[#1A1A1A] text-[#B6965D] flex items-center justify-center font-playfair font-bold text-lg">
                É
              </div>
              <div class="text-left font-azeret text-xs">
                <p class="font-semibold text-[#1A1A1A] uppercase tracking-wider">Julianne de Mornay</p>
                <p class="text-stone-500 text-[11px]">Global Artistic Director &bull; Paris Atelier</p>
              </div>
            </div>
          </header>

          <!-- Featured Cover Image -->
          <div class="aspect-[16/9] w-full overflow-hidden bg-stone-200 border border-[#E2DDD5] shadow-lg">
            <img
              :src="resolveStoryImage(story)"
              :alt="story.title"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Article Body -->
          <div class="prose prose-stone max-w-none space-y-6 font-serif text-base sm:text-lg text-stone-800 leading-loose">
            <div v-if="story.content" v-html="story.content" class="space-y-6"></div>
            <div v-else class="space-y-6 font-playfair text-stone-800 leading-relaxed text-lg">
              <p>
                In the historic heart of Place Vendôme, the craftsmen of Maison Éclore practice an art form where patience and metallurgical mastery merge. Every creation begins with a single pencil sketch, followed by wax hand-carving and exhaustive gemological selection.
              </p>
              <blockquote class="p-6 my-6 border-l-4 border-[#B6965D] bg-[#FAF8F5] italic text-xl text-[#1A1A1A] font-playfair">
                "A true piece of high joaillerie is not simply worn; it becomes an indelible custodian of memory and light."
              </blockquote>
              <p>
                Our master lapidaries examine thousands of carats of ethically mined diamonds, seeking stones that display exceptional fire, clarity, and personality. Through traditional burnishing techniques, 18-karat recycled gold is sculpted to hold these natural wonders with whisper-weight prongs, allowing pure light to illuminate the facets from every direction.
              </p>
            </div>
          </div>

          <!-- Featured Creations Section (Product Cards linking to PDP) -->
          <section v-if="featuredProducts.length > 0" class="border-t border-[#E2DDD5] pt-12 space-y-8">
            <div class="text-center sm:text-left space-y-1">
              <span class="font-azeret text-[10px] uppercase tracking-[0.3em] text-[#B6965D]">Atelier Showcase</span>
              <h3 class="font-playfair text-2xl sm:text-3xl text-[#1A1A1A]">Curated Pieces In This Story</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="prod in featuredProducts"
                :key="prod.id"
                class="group bg-[#FAF8F5] border border-[#E2DDD5] p-4 flex flex-col justify-between cursor-pointer transition-all hover:border-[#B6965D] hover:shadow-md"
                @click="navigateToProduct(prod)"
              >
                <div>
                  <div class="aspect-square bg-stone-100 overflow-hidden mb-4">
                    <img
                      :src="resolveProductImage(prod)"
                      :alt="prod.name"
                      class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                    />
                  </div>
                  <span class="font-azeret text-[9px] text-[#B6965D] uppercase tracking-widest block mb-1">
                    {{ prod.category?.name || 'Fine Jewellery' }}
                  </span>
                  <h4 class="font-playfair text-lg text-[#1A1A1A] group-hover:text-[#B6965D] transition-colors truncate">
                    {{ prod.name }}
                  </h4>
                </div>

                <div class="pt-4 border-t border-[#E2DDD5]/60 mt-4 flex items-center justify-between">
                  <span class="font-azeret text-xs font-bold text-[#1A1A1A]">
                    <CurrencyDisplay :amount="prod.price || prod.base_price" />
                  </span>
                  <span class="font-azeret text-[10px] uppercase tracking-wider text-[#B6965D] font-semibold">
                    Discover &rarr;
                  </span>
                </div>
              </div>
            </div>
          </section>

          <!-- Back Button Footer -->
          <div class="pt-12 border-t border-[#E2DDD5] flex justify-between items-center">
            <EcloreButton variant="outline" size="sm" @click="router.push('/journal')">
              &larr; Back to Journal
            </EcloreButton>
            <EcloreButton variant="primary" size="sm" @click="router.push('/appointments')">
              Book Private Salon Appointment
            </EcloreButton>
          </div>
        </article>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
