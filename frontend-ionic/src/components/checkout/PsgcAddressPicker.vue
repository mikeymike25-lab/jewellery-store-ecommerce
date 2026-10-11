<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import fallbackData from '@/assets/data/psgc-fallback.json';

interface Props {
  region?: string;
  province?: string | null;
  city?: string;
  barangay?: string;
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  region: '',
  province: '',
  city: '',
  barangay: '',
  disabled: false,
});

const emit = defineEmits<{
  (e: 'update:region', value: string): void;
  (e: 'update:province', value: string): void;
  (e: 'update:city', value: string): void;
  (e: 'update:barangay', value: string): void;
}>();

const PSGC_API = 'https://psgc.cloud/api/v2';

interface PsgcOption {
  code: string;
  name: string;
  type?: string;
}

const regions = ref<PsgcOption[]>([]);
const provinces = ref<PsgcOption[]>([]);
const cities = ref<PsgcOption[]>([]);
const barangays = ref<PsgcOption[]>([]);

const selectedRegion = ref(props.region);
const selectedProvince = ref(props.province || '');
const selectedCity = ref(props.city);
const selectedBarangay = ref(props.barangay);

const isNcr = ref(false);
const loadingRegions = ref(false);
const loadingProvinces = ref(false);
const loadingCities = ref(false);
const loadingBarangays = ref(false);

// Helper to determine if a region name or code is NCR
function checkIsNcr(regionNameOrCode: string): boolean {
  if (!regionNameOrCode) return false;
  const lower = regionNameOrCode.toLowerCase();
  return lower.includes('ncr') || lower.includes('national capital region') || regionNameOrCode === '130000000';
}

// 1. Load All Philippine Regions
async function fetchRegions() {
  loadingRegions.value = true;
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(`${PSGC_API}/regions`, { signal: controller.signal });
    clearTimeout(timeout);

    if (!res.ok) throw new Error('Remote PSGC unreachable');
    const data = await res.json();
    const list = Array.isArray(data) ? data : (data.data || []);
    if (list.length > 0) {
      regions.value = list.map((r: any) => ({
        code: r.code || r.id,
        name: r.name,
      }));
    } else {
      throw new Error('Empty remote regions');
    }
  } catch (err) {
    // Graceful offline fallback
    regions.value = fallbackData.regions.map((r) => ({
      code: r.code,
      name: r.name,
    }));
  } finally {
    loadingRegions.value = false;
  }
}

// 2. Load Provinces for Selected Region
async function fetchProvinces(regionNameOrCode: string) {
  if (!regionNameOrCode) {
    provinces.value = [];
    return;
  }

  isNcr.value = checkIsNcr(regionNameOrCode);

  if (isNcr.value) {
    provinces.value = [];
    selectedProvince.value = '';
    emit('update:province', '');
    await fetchCitiesForRegion(regionNameOrCode);
    return;
  }

  loadingProvinces.value = true;
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(`${PSGC_API}/regions/${encodeURIComponent(regionNameOrCode)}/provinces`, {
      signal: controller.signal,
    });
    clearTimeout(timeout);

    if (!res.ok) throw new Error('Remote provinces error');
    const data = await res.json();
    const list = Array.isArray(data) ? data : (data.data || []);
    if (list.length > 0) {
      provinces.value = list.map((p: any) => ({ code: p.code || p.id, name: p.name }));
    } else {
      throw new Error('No provinces found');
    }
  } catch (err) {
    // Fallback dictionary lookup
    const regObj = regions.value.find((r) => r.name === regionNameOrCode || r.code === regionNameOrCode);
    const code = regObj?.code || regionNameOrCode;
    const fallbackList = (fallbackData.provinces as Record<string, any[]>)[code] || [];
    provinces.value = fallbackList.map((p) => ({ code: p.code, name: p.name }));
  } finally {
    loadingProvinces.value = false;
  }
}

// 3. Load Cities directly for NCR
async function fetchCitiesForRegion(regionNameOrCode: string) {
  loadingCities.value = true;
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(`${PSGC_API}/regions/${encodeURIComponent(regionNameOrCode)}/cities-municipalities`, {
      signal: controller.signal,
    });
    clearTimeout(timeout);

    if (!res.ok) throw new Error('Remote NCR cities error');
    const data = await res.json();
    const list = Array.isArray(data) ? data : (data.data || []);
    if (list.length > 0) {
      cities.value = list.map((c: any) => ({
        code: c.code || c.id,
        name: c.name,
        type: c.type || 'City',
      }));
    } else {
      throw new Error('No NCR cities returned');
    }
  } catch (err) {
    // Fallback for NCR
    const ncrCities = fallbackData.cities['130000000'] || [];
    cities.value = ncrCities.map((c) => ({ code: c.code, name: c.name, type: c.type }));
  } finally {
    loadingCities.value = false;
  }
}

// 4. Load Cities for Selected Province
async function fetchCities(provinceNameOrCode: string) {
  if (!provinceNameOrCode) {
    cities.value = [];
    return;
  }

  loadingCities.value = true;
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(
      `${PSGC_API}/regions/${encodeURIComponent(selectedRegion.value)}/provinces/${encodeURIComponent(provinceNameOrCode)}/cities-municipalities`,
      { signal: controller.signal }
    );
    clearTimeout(timeout);

    if (!res.ok) throw new Error('Remote cities error');
    const data = await res.json();
    const list = Array.isArray(data) ? data : (data.data || []);
    if (list.length > 0) {
      cities.value = list.map((c: any) => ({
        code: c.code || c.id,
        name: c.name,
        type: c.type || 'City',
      }));
    } else {
      throw new Error('No province cities');
    }
  } catch (err) {
    // Fallback lookup
    const provObj = provinces.value.find((p) => p.name === provinceNameOrCode || p.code === provinceNameOrCode);
    const code = provObj?.code || provinceNameOrCode;
    const fallbackList = (fallbackData.cities as Record<string, any[]>)[code] || [];
    cities.value = fallbackList.map((c) => ({ code: c.code, name: c.name, type: c.type }));
  } finally {
    loadingCities.value = false;
  }
}

// 5. Load Barangays for Selected City
async function fetchBarangays(cityNameOrCode: string) {
  if (!cityNameOrCode) {
    barangays.value = [];
    return;
  }

  loadingBarangays.value = true;
  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(`${PSGC_API}/cities-municipalities/${encodeURIComponent(cityNameOrCode)}/barangays`, {
      signal: controller.signal,
    });
    clearTimeout(timeout);

    if (!res.ok) throw new Error('Remote barangays error');
    const data = await res.json();
    const list = Array.isArray(data) ? data : (data.data || []);
    if (list.length > 0) {
      barangays.value = list.map((b: any) => ({ code: b.code || b.id, name: b.name }));
    } else {
      throw new Error('No barangays');
    }
  } catch (err) {
    const cityObj = cities.value.find((c) => c.name === cityNameOrCode || c.code === cityNameOrCode);
    const code = cityObj?.code || cityNameOrCode;
    const fallbackList =
      (fallbackData.barangays as Record<string, any[]>)[code] || fallbackData.barangays.default;
    barangays.value = fallbackList.map((b) => ({ code: b.code, name: b.name }));
  } finally {
    loadingBarangays.value = false;
  }
}

// Handlers for select change
async function handleRegionChange(e: Event) {
  const value = (e.target as HTMLSelectElement).value;
  selectedRegion.value = value;
  selectedProvince.value = '';
  selectedCity.value = '';
  selectedBarangay.value = '';

  emit('update:region', value);
  emit('update:province', '');
  emit('update:city', '');
  emit('update:barangay', '');

  if (value) {
    await fetchProvinces(value);
  }
}

async function handleProvinceChange(e: Event) {
  const value = (e.target as HTMLSelectElement).value;
  selectedProvince.value = value;
  selectedCity.value = '';
  selectedBarangay.value = '';

  emit('update:province', value);
  emit('update:city', '');
  emit('update:barangay', '');

  if (value) {
    await fetchCities(value);
  }
}

async function handleCityChange(e: Event) {
  const value = (e.target as HTMLSelectElement).value;
  selectedCity.value = value;
  selectedBarangay.value = '';

  emit('update:city', value);
  emit('update:barangay', '');

  if (value) {
    await fetchBarangays(value);
  }
}

function handleBarangayChange(e: Event) {
  const value = (e.target as HTMLSelectElement).value;
  selectedBarangay.value = value;
  emit('update:barangay', value);
}

// Synchronize external prop changes
watch(
  () => props.region,
  async (newVal) => {
    if (newVal && newVal !== selectedRegion.value) {
      selectedRegion.value = newVal;
      await fetchProvinces(newVal);
    }
  }
);

watch(
  () => props.province,
  async (newVal) => {
    if (newVal !== undefined && newVal !== selectedProvince.value) {
      selectedProvince.value = newVal || '';
      if (newVal) await fetchCities(newVal);
    }
  }
);

watch(
  () => props.city,
  async (newVal) => {
    if (newVal && newVal !== selectedCity.value) {
      selectedCity.value = newVal;
      await fetchBarangays(newVal);
    }
  }
);

watch(
  () => props.barangay,
  (newVal) => {
    if (newVal !== undefined) {
      selectedBarangay.value = newVal;
    }
  }
);

onMounted(async () => {
  await fetchRegions();
  if (selectedRegion.value) {
    await fetchProvinces(selectedRegion.value);
    if (selectedProvince.value) {
      await fetchCities(selectedProvince.value);
    }
    if (selectedCity.value) {
      await fetchBarangays(selectedCity.value);
    }
  }
});
</script>

<template>
  <div class="space-y-6">
    <!-- Region & Province Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Region Select -->
      <div>
        <label for="psgc_region" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-500 mb-2 block">
          Region *
        </label>
        <div class="relative">
          <select
            id="psgc_region"
            :value="selectedRegion"
            :disabled="disabled || loadingRegions"
            class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm rounded-none px-4 py-3 min-h-[48px] appearance-none focus:border-[#1A1A1A] focus:outline-none transition-colors disabled:bg-stone-100 disabled:cursor-not-allowed"
            @change="handleRegionChange"
            required
          >
            <option value="">Select Region</option>
            <option
              v-for="r in regions"
              :key="r.code"
              :value="r.name"
            >
              {{ r.name }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-stone-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Province Select (Omitted or Disabled for NCR) -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label for="psgc_province" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-500 block">
            Province
          </label>
          <span v-if="isNcr" class="font-azeret text-[9px] uppercase tracking-wider text-[#B6965D]">
            NCR Direct (No Province)
          </span>
        </div>
        <div class="relative">
          <select
            id="psgc_province"
            :value="selectedProvince"
            :disabled="disabled || isNcr || loadingProvinces || provinces.length === 0"
            class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm rounded-none px-4 py-3 min-h-[48px] appearance-none focus:border-[#1A1A1A] focus:outline-none transition-colors disabled:bg-stone-50 disabled:text-stone-400 disabled:cursor-not-allowed"
            @change="handleProvinceChange"
          >
            <option value="">
              {{ isNcr ? 'N/A (National Capital Region Direct)' : 'Select Province' }}
            </option>
            <option
              v-for="p in provinces"
              :key="p.code"
              :value="p.name"
            >
              {{ p.name }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-stone-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- City & Barangay Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- City / Municipality Select -->
      <div>
        <label for="psgc_city" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-500 mb-2 block">
          City / Municipality *
        </label>
        <div class="relative">
          <select
            id="psgc_city"
            :value="selectedCity"
            :disabled="disabled || loadingCities || cities.length === 0"
            class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm rounded-none px-4 py-3 min-h-[48px] appearance-none focus:border-[#1A1A1A] focus:outline-none transition-colors disabled:bg-stone-100 disabled:cursor-not-allowed"
            @change="handleCityChange"
            required
          >
            <option value="">
              {{ cities.length === 0 ? 'Select Region First' : 'Select City / Municipality' }}
            </option>
            <option
              v-for="c in cities"
              :key="c.code"
              :value="c.name"
            >
              {{ c.name }} {{ c.type ? `(${c.type})` : '' }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-stone-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Barangay Select -->
      <div>
        <label for="psgc_barangay" class="font-azeret text-[10px] uppercase tracking-[0.2em] text-stone-500 mb-2 block">
          Barangay *
        </label>
        <div class="relative">
          <select
            id="psgc_barangay"
            :value="selectedBarangay"
            :disabled="disabled || loadingBarangays || barangays.length === 0"
            class="w-full bg-white border border-[#E2DDD5] text-[#1A1A1A] font-sans text-sm rounded-none px-4 py-3 min-h-[48px] appearance-none focus:border-[#1A1A1A] focus:outline-none transition-colors disabled:bg-stone-100 disabled:cursor-not-allowed"
            @change="handleBarangayChange"
            required
          >
            <option value="">
              {{ barangays.length === 0 ? 'Select City First' : 'Select Barangay' }}
            </option>
            <option
              v-for="b in barangays"
              :key="b.code"
              :value="b.name"
            >
              {{ b.name }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-stone-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
