<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { IonPage, IonContent } from '@ionic/vue';
import EcloreHeader from '@/components/EcloreHeader.vue';
import EcloreFooter from '@/components/EcloreFooter.vue';
import { apiClient } from '@/api/client';

interface Story {
  id: number;
  title: string;
  slug: string;
  excerpt?: string | null;
  content?: string | null;
  type?: string;
  category?: string;
  featured_image?: string | null;
  published_at?: string | null;
  is_featured?: boolean;
}

const router = useRouter();
const route = useRoute();

const categories = ['All', 'Latest', 'Press Releases', 'Exhibitions', 'Insights'];
const selectedCategory = ref('All');

const featuredStory = ref<Story | null>(null);
const stories = ref<Story[]>([]);
const loading = ref(false);

function resolveStoryImage(story: Story): string {
  if (story.featured_image) return story.featured_image;
  const defaults: Record<string, string> = {
    'Exhibitions': '/assets/necklace.webp',
    'Press Releases': '/assets/bracelet.webp',
    'Insights': '/assets/ring.webp',
    'Latest': '/assets/earrings.webp',
  };
  return defaults[story.category || ''] || '/assets/necklace.webp';
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

async function loadStories() {
  loading.value = true;
  try {
    const res = await apiClient.get<{
      success: boolean;
      featured: Story | null;
      stories: Story[];
    }>('/v1/stories', {
      params: { category: selectedCategory.value === 'All' ? undefined : selectedCategory.value },
    });
    featuredStory.value = res.data?.featured || null;
    stories.value = res.data?.stories || [];
  } catch (err) {
    console.warn('Failed to load stories from API, using fallback data:', err);
    featuredStory.value = {
      id: 1,
      title: 'The Art of Savoir-Faire: Inside the Vendôme Atelier',
      slug: 'art-of-savoir-faire',
      category: 'Insights',
      excerpt: 'Discover how master jewelers transform uncut gemstones into timeless works of high joaillerie through centuries-old Parisian craftsmanship.',
      published_at: '2026-02-14T00:00:00.000Z',
    };
    stories.value = [
      {
        id: 2,
        title: 'New Collection: Celestial Dreams Launch',
        slug: 'celestial-dreams-launch',
        category: 'Latest',
        excerpt: 'An astral odyssey captured in brilliant diamonds and royal blue sapphires.',
        published_at: '2026-02-01T00:00:00.000Z',
      },
      {
        id: 3,
        title: 'Éclore at the Grand Palais Retrospective',
        slug: 'grand-palais-exhibition',
        category: 'Exhibitions',
        excerpt: 'A hundred years of heirloom design exhibited in the heart of Paris.',
        published_at: '2026-01-15T00:00:00.000Z',
      },
      {
        id: 4,
        title: 'The Rarity of Natural Pink Diamonds',
        slug: 'pink-diamond-insights',
        category: 'Insights',
        excerpt: 'Understanding the geological miracle behind nature’s most coveted treasures.',
        published_at: '2026-01-08T00:00:00.000Z',
      },
      {
        id: 5,
        title: '2026 Annual Sustainability Report',
        slug: 'sustainability-report-2026',
        category: 'Press Releases',
        excerpt: 'Our ongoing dedication to 100% recycled metals and Kimberley-certified mining ethics.',
        published_at: '2025-12-28T00:00:00.000Z',
      },
    ];
  } finally {
    loading.value = false;
  }
}

function selectCategory(cat: string) {
  selectedCategory.value = cat;
  loadStories();
}

function openStory(slug: string) {
  router.push(`/journal/${slug}`);
}

onMounted(() => {
  const queryCat = route.query.category?.toString();
  if (queryCat && categories.includes(queryCat)) {
    selectedCategory.value = queryCat;
  }
  loadStories();
});
</script>

<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-[#F3EFE7]">
      <EcloreHeader />

      <main class="min-h-screen bg-[#F3EFE7] text-[#1A1A1A]">
        <!-- Hero Section -->
        <header class="py-16 sm:py-24 border-b border-[#E2DDD5] text-center px-4 max-w-4xl mx-auto space-y-4">
          <nav class="font-azeret text-[10px] uppercase tracking-[0.35em] text-[#B6965D]">
            Éclore Editorial Journal &bull; Gazette
          </nav>
          <h1 class="font-playfair text-4xl sm:text-6xl text-[#1A1A1A] font-light tracking-wide">
            Le Journal de la Maison
          </h1>
          <p class="font-azeret text-xs sm:text-sm text-stone-500 uppercase tracking-widest max-w-xl mx-auto leading-relaxed">
            Chronicles of fine craftsmanship, rare gemstone discoveries, and global cultural showcases.
          </p>
        </header>

        <!-- Category Filter Sub-nav -->
        <nav class="sticky top-16 z-30 bg-[#F3EFE7]/95 backdrop-blur-md border-b border-[#E2DDD5] py-3 px-4">
          <div class="max-w-6xl mx-auto flex items-center justify-center space-x-6 sm:space-x-10 overflow-x-auto whitespace-nowrap font-azeret text-[11px] uppercase tracking-[0.25em]">
            <button
              v-for="cat in categories"
              :key="cat"
              type="button"
              class="pb-1 transition-colors relative"
              :class="selectedCategory === cat ? 'text-[#1A1A1A] font-bold border-b-2 border-[#B6965D]' : 'text-stone-500 hover:text-[#1A1A1A]'"
              @click="selectCategory(cat)"
            >
              {{ cat }}
            </button>
          </div>
        </nav>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-16">
          <!-- Loading State -->
          <div v-if="loading" class="py-16 space-y-8">
            <div class="animate-pulse bg-[#FAF8F5] border border-[#E2DDD5] h-96 rounded"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              <div v-for="i in 3" :key="i" class="animate-pulse bg-[#FAF8F5] border border-[#E2DDD5] h-64 rounded"></div>
            </div>
          </div>

          <div v-else class="space-y-16">
            <!-- Featured Story Hero Card -->
            <article
              v-if="featuredStory"
              class="group bg-[#FAF8F5] border border-[#E2DDD5] overflow-hidden grid grid-cols-1 lg:grid-cols-12 cursor-pointer transition-all hover:border-[#B6965D]/60 hover:shadow-xl"
              @click="openStory(featuredStory.slug)"
            >
              <div class="lg:col-span-7 aspect-[16/10] overflow-hidden bg-stone-200">
                <img
                  :src="resolveStoryImage(featuredStory)"
                  :alt="featuredStory.title"
                  class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                />
              </div>

              <div class="lg:col-span-5 p-8 sm:p-12 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                  <div class="flex items-center gap-3">
                    <span class="font-azeret text-[9px] uppercase tracking-widest bg-[#B6965D] text-white px-2.5 py-0.5 font-bold">
                      {{ featuredStory.category || 'Featured Story' }}
                    </span>
                    <span class="font-azeret text-[10px] text-stone-400">
                      {{ formatDate(featuredStory.published_at) }}
                    </span>
                  </div>

                  <h2 class="font-playfair text-2xl sm:text-4xl text-[#1A1A1A] group-hover:text-[#B6965D] transition-colors leading-tight">
                    {{ featuredStory.title }}
                  </h2>

                  <p class="font-azeret text-xs sm:text-sm text-stone-600 leading-relaxed">
                    {{ featuredStory.excerpt }}
                  </p>
                </div>

                <div class="pt-4 border-t border-[#E2DDD5]/60 flex items-center justify-between font-azeret text-[11px] uppercase tracking-widest text-[#1A1A1A] group-hover:text-[#B6965D]">
                  <span class="font-semibold">Read Full Article</span>
                  <span>&rarr;</span>
                </div>
              </div>
            </article>

            <!-- Secondary Stories Grid -->
            <div>
              <div class="border-b border-[#E2DDD5] pb-4 mb-8 flex items-center justify-between">
                <h3 class="font-playfair text-2xl text-[#1A1A1A]">Curated Chronicles</h3>
                <span class="font-azeret text-[10px] text-stone-500 uppercase tracking-widest">
                  {{ stories.length }} Publications
                </span>
              </div>

              <div v-if="stories.length === 0" class="py-12 text-center text-stone-500 font-azeret text-xs">
                No articles published in this category yet.
              </div>

              <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <article
                  v-for="story in stories"
                  :key="story.id"
                  class="group bg-[#FAF8F5] border border-[#E2DDD5] overflow-hidden flex flex-col justify-between cursor-pointer transition-all hover:border-[#B6965D]/60 hover:shadow-md"
                  @click="openStory(story.slug)"
                >
                  <div>
                    <div class="aspect-[16/10] overflow-hidden bg-stone-200">
                      <img
                        :src="resolveStoryImage(story)"
                        :alt="story.title"
                        class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>

                    <div class="p-6 space-y-3">
                      <div class="flex items-center justify-between font-azeret text-[9px] uppercase tracking-widest text-stone-400">
                        <span class="text-[#B6965D] font-bold">{{ story.category }}</span>
                        <span>{{ formatDate(story.published_at) }}</span>
                      </div>

                      <h4 class="font-playfair text-xl text-[#1A1A1A] group-hover:text-[#B6965D] transition-colors leading-snug">
                        {{ story.title }}
                      </h4>

                      <p class="font-azeret text-xs text-stone-600 line-clamp-3 leading-relaxed">
                        {{ story.excerpt }}
                      </p>
                    </div>
                  </div>

                  <div class="p-6 pt-0 border-t border-[#E2DDD5]/60 mt-4 flex items-center justify-between font-azeret text-[10px] uppercase tracking-widest text-stone-500 group-hover:text-[#B6965D]">
                    <span>Explore Story</span>
                    <span>&rarr;</span>
                  </div>
                </article>
              </div>
            </div>
          </div>
        </div>
      </main>

      <EcloreFooter />
    </ion-content>
  </ion-page>
</template>
