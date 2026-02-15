<template>
    <div class="min-h-screen bg-zinc-950">
        <SessionBar />

        <!-- Header with search and filter toggle -->
        <div class="sticky top-[41px] z-40 border-b border-zinc-800 bg-zinc-950/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3">
                <button
                    @click="drawerOpen = !drawerOpen"
                    class="flex items-center gap-2 rounded-lg border border-zinc-700 px-3 py-2 text-sm text-zinc-300 transition hover:border-zinc-500 hover:text-zinc-100 lg:hidden"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    Filters
                    <span v-if="activeFilterCount" class="rounded-full bg-indigo-600 px-1.5 text-xs font-medium text-white">
                        {{ activeFilterCount }}
                    </span>
                </button>
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Search matches..."
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-900 py-2 pl-10 pr-4 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-6">
            <div class="flex gap-8">
                <!-- Desktop sidebar -->
                <aside class="hidden w-64 shrink-0 lg:block">
                    <FilterPanel
                        :competitions="filterOptions.competitions"
                        :teams="filterOptions.teams"
                        :venues="filterOptions.venues"
                        :selected="filters"
                        @update:filters="updateFilters"
                        @clear="clearFilters"
                    />
                </aside>

                <!-- Product list -->
                <main class="min-w-0 flex-1">
                    <div v-if="loading && products.length === 0" class="space-y-3">
                        <div v-for="i in 8" :key="i" class="animate-pulse rounded-lg border border-zinc-800 bg-zinc-900 p-4">
                            <div class="flex gap-4">
                                <div class="h-4 w-24 rounded bg-zinc-800"></div>
                                <div class="h-4 w-48 rounded bg-zinc-800"></div>
                                <div class="h-4 w-32 rounded bg-zinc-800"></div>
                                <div class="h-4 w-36 rounded bg-zinc-800"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="products.length === 0 && !loading" class="py-20 text-center">
                        <p class="text-zinc-400">No matches found.</p>
                        <button
                            v-if="activeFilterCount > 0"
                            @click="clearFilters"
                            class="mt-3 text-sm text-indigo-400 transition hover:text-indigo-300"
                        >
                            Clear filters
                        </button>
                    </div>

                    <template v-else>
                        <div class="overflow-x-auto rounded-lg border border-zinc-800">
                            <table class="w-full text-sm text-left">
                                <thead class="border-b border-zinc-800 bg-zinc-900 text-xs uppercase tracking-wider text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Date</th>
                                        <th class="px-4 py-3 font-medium">Match</th>
                                        <th class="px-4 py-3 font-medium hidden sm:table-cell">Competition</th>
                                        <th class="px-4 py-3 font-medium hidden md:table-cell">Venue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/50">
                                    <tr
                                        v-for="product in products"
                                        :key="product.id"
                                        class="bg-zinc-950 transition hover:bg-zinc-900"
                                    >
                                        <td class="whitespace-nowrap px-4 py-3 text-zinc-400">
                                            {{ formatDate(product.starts_at) }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-zinc-100">
                                            {{ matchName(product) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-zinc-400 hidden sm:table-cell">
                                            {{ product.competition?.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-zinc-400 hidden md:table-cell">
                                            {{ product.venue?.name }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="hasMore" class="mt-8 text-center">
                            <button
                                @click="loadMore"
                                :disabled="loading"
                                class="rounded-lg border border-zinc-700 px-6 py-2.5 text-sm font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-zinc-100 disabled:opacity-50"
                            >
                                <span v-if="loading">Loading...</span>
                                <span v-else>Load more ({{ products.length }} of {{ total }})</span>
                            </button>
                        </div>
                    </template>
                </main>
            </div>
        </div>

        <!-- Mobile drawer overlay -->
        <Teleport to="body">
            <Transition name="drawer">
                <div v-if="drawerOpen" class="fixed inset-0 z-50 lg:hidden">
                    <div class="absolute inset-0 bg-black/60" @click="drawerOpen = false"></div>
                    <div class="absolute inset-y-0 left-0 w-80 max-w-[85vw] overflow-y-auto bg-zinc-900 p-6 shadow-xl">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-zinc-100">Filters</h2>
                            <button @click="drawerOpen = false" class="text-zinc-400 hover:text-zinc-200">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <FilterPanel
                            :competitions="filterOptions.competitions"
                            :teams="filterOptions.teams"
                            :venues="filterOptions.venues"
                            :selected="filters"
                            @update:filters="updateFilters"
                            @clear="clearFilters"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getProducts, getFilters } from '../api/index.js';
import SessionBar from '../components/SessionBar.vue';
import FilterPanel from '../components/FilterPanel.vue';

const route = useRoute();
const router = useRouter();

const products = ref([]);
const total = ref(0);
const page = ref(1);
const loading = ref(false);
const drawerOpen = ref(false);

const filterOptions = reactive({
    competitions: [],
    teams: [],
    venues: [],
});

const filters = reactive({
    competitions: [],
    teams: [],
    venues: [],
    dateFrom: '',
    dateTo: '',
});

const searchInput = ref('');
let searchTimeout = null;

const hasMore = computed(() => products.value.length < total.value);

function matchName(product) {
    if (product.home_team && product.away_team) {
        return `${product.home_team.name} vs ${product.away_team.name}`;
    }
    return product.name;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const activeFilterCount = computed(() => {
    let count = filters.competitions.length + filters.teams.length + filters.venues.length;
    if (filters.dateFrom) count++;
    if (filters.dateTo) count++;
    if (searchInput.value) count++;
    return count;
});

function readFiltersFromQuery() {
    const q = route.query;
    filters.competitions = q.competitions ? q.competitions.split(',').map(Number) : [];
    filters.teams = q.teams ? q.teams.split(',').map(Number) : [];
    filters.venues = q.venues ? q.venues.split(',').map(Number) : [];
    filters.dateFrom = q.date_from || '';
    filters.dateTo = q.date_to || '';
    searchInput.value = q.search || '';
}

function syncFiltersToQuery() {
    const query = {};
    if (filters.competitions.length) query.competitions = filters.competitions.join(',');
    if (filters.teams.length) query.teams = filters.teams.join(',');
    if (filters.venues.length) query.venues = filters.venues.join(',');
    if (filters.dateFrom) query.date_from = filters.dateFrom;
    if (filters.dateTo) query.date_to = filters.dateTo;
    if (searchInput.value) query.search = searchInput.value;
    router.replace({ query });
}

function buildApiParams() {
    const params = { page: page.value };
    if (filters.competitions.length) params.competitions = filters.competitions.join(',');
    if (filters.teams.length) params.teams = filters.teams.join(',');
    if (filters.venues.length) params.venues = filters.venues.join(',');
    if (filters.dateFrom) params.date_from = filters.dateFrom;
    if (filters.dateTo) params.date_to = filters.dateTo;
    if (searchInput.value) params.search = searchInput.value;
    return params;
}

async function fetchProducts(append = false) {
    loading.value = true;
    try {
        const { data } = await getProducts(buildApiParams());
        if (append) {
            products.value.push(...data.data);
        } else {
            products.value = data.data;
        }
        total.value = data.total;
    } catch {
        // silently fail
    } finally {
        loading.value = false;
    }
}

async function fetchFilterOptions() {
    try {
        const { data } = await getFilters();
        filterOptions.competitions = data.competitions;
        filterOptions.teams = data.teams;
        filterOptions.venues = data.venues;
    } catch {
        // silently fail
    }
}

function updateFilters(newFilters) {
    Object.assign(filters, newFilters);
    page.value = 1;
    syncFiltersToQuery();
    fetchProducts();
}

function clearFilters() {
    filters.competitions = [];
    filters.teams = [];
    filters.venues = [];
    filters.dateFrom = '';
    filters.dateTo = '';
    searchInput.value = '';
    page.value = 1;
    syncFiltersToQuery();
    fetchProducts();
}

function loadMore() {
    page.value++;
    fetchProducts(true);
}

// Debounced search
watch(searchInput, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        page.value = 1;
        syncFiltersToQuery();
        fetchProducts();
    }, 300);
});

onMounted(() => {
    readFiltersFromQuery();
    fetchFilterOptions();
    fetchProducts();
});
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.2s ease;
}
.drawer-enter-active > div:last-child,
.drawer-leave-active > div:last-child {
    transition: transform 0.2s ease;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}
.drawer-enter-from > div:last-child,
.drawer-leave-to > div:last-child {
    transform: translateX(-100%);
}
</style>
