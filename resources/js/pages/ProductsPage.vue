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
                <label class="hidden sm:flex items-center gap-2 whitespace-nowrap text-sm text-zinc-400 cursor-pointer select-none">
                    <input
                        v-model="showUnavailable"
                        type="checkbox"
                        class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                    />
                    Show unavailable
                </label>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-6">
            <div class="flex gap-8">
                <!-- Desktop sidebar -->
                <aside class="hidden w-64 shrink-0 lg:block">
                    <FilterPanel
                        :competitions="filterOptions.competitions"
                        :teams="filterOptions.teams"
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
                                        <th class="px-4 py-3 font-medium hidden sm:table-cell">ID</th>
                                        <th class="px-4 py-3 font-medium hidden sm:table-cell">Date</th>
                                        <th class="px-4 py-3 font-medium">Match</th>
                                        <th class="px-4 py-3 font-medium hidden sm:table-cell">Competition</th>
                                        <th class="px-4 py-3 font-medium text-right">Price</th>
                                        <th class="px-4 py-3 font-medium text-right">Tickets</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/50">
                                    <tr
                                        v-for="product in products"
                                        :key="product.id"
                                        @click="router.push({ name: 'product', params: { id: product.id }, query: route.query })"
                                        class="cursor-pointer bg-zinc-950 transition hover:bg-zinc-900"
                                    >
                                        <td class="whitespace-nowrap px-4 py-3 text-zinc-500 hidden sm:table-cell">
                                            {{ product.id }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-zinc-400 hidden sm:table-cell">
                                            {{ formatDate(product.starts_at) }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-zinc-100">
                                            {{ matchName(product) }}
                                            <div class="mt-0.5 text-xs font-normal text-zinc-500 sm:hidden">
                                                {{ formatDateShort(product.starts_at) }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-zinc-400 hidden sm:table-cell">
                                            {{ product.competition?.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-zinc-400">
                                            {{ priceRange(product) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-zinc-400">
                                            {{ product.ticket_options_count }}
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
                            :selected="filters"
                            @update:filters="updateFilters"
                            @clear="clearFilters"
                        />
                        <label class="mt-4 flex items-center gap-2 text-sm text-zinc-400 cursor-pointer select-none">
                            <input
                                v-model="showUnavailable"
                                type="checkbox"
                                class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                            />
                            Show unavailable
                        </label>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getProducts, getFilters, getStatus } from '../api/index.js';
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
});

const filters = reactive({
    competitions: [],
    teams: [],
    dateFrom: '',
    dateTo: '',
});

const defaultFilters = ref(null);
const searchInput = ref('');
const showUnavailable = ref(false);
let searchTimeout = null;

const hasMore = computed(() => products.value.length < total.value);

function formatCurrency(value) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP', maximumFractionDigits: 0 }).format(value);
}

function priceRange(product) {
    const min = product.ticket_options_min_price;
    const max = product.ticket_options_max_price;
    if (min == null) return '-';
    if (min === max) return formatCurrency(min);
    return `${formatCurrency(min)} - ${formatCurrency(max)}`;
}

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

function formatDateShort(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const activeFilterCount = computed(() => {
    let count = filters.competitions.length + filters.teams.length;
    if (filters.dateFrom) count++;
    if (filters.dateTo) count++;
    if (searchInput.value) count++;
    return count;
});

function hasFilterQueryParams() {
    const q = route.query;
    return !!(q.competitions || q.teams || q.date_from || q.date_to || q.search || q.show_unavailable);
}

function readFiltersFromQuery() {
    const q = route.query;

    if (!hasFilterQueryParams() && defaultFilters.value) {
        const df = defaultFilters.value;
        filters.competitions = df.competitions || [];
        filters.teams = df.teams || [];
        filters.dateFrom = '';
        filters.dateTo = '';
        searchInput.value = '';
        syncFiltersToQuery();
        return;
    }

    filters.competitions = q.competitions ? q.competitions.split(',').map(Number) : [];
    filters.teams = q.teams ? q.teams.split(',').map(Number) : [];
    filters.dateFrom = q.date_from || '';
    filters.dateTo = q.date_to || '';
    searchInput.value = q.search || '';
    showUnavailable.value = q.show_unavailable === '1';
}

function syncFiltersToQuery() {
    const query = {};
    if (filters.competitions.length) query.competitions = filters.competitions.join(',');
    if (filters.teams.length) query.teams = filters.teams.join(',');
    if (filters.dateFrom) query.date_from = filters.dateFrom;
    if (filters.dateTo) query.date_to = filters.dateTo;
    if (searchInput.value) query.search = searchInput.value;
    if (showUnavailable.value) query.show_unavailable = '1';
    router.replace({ query });
}

function buildApiParams() {
    const params = { page: page.value };
    if (filters.competitions.length) params.competitions = filters.competitions.join(',');
    if (filters.teams.length) params.teams = filters.teams.join(',');
    if (filters.dateFrom) params.date_from = filters.dateFrom;
    if (filters.dateTo) params.date_to = filters.dateTo;
    if (searchInput.value) params.search = searchInput.value;
    if (showUnavailable.value) params.show_unavailable = 1;
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
    filters.dateFrom = '';
    filters.dateTo = '';
    searchInput.value = '';
    showUnavailable.value = false;
    page.value = 1;
    syncFiltersToQuery();
    fetchProducts();
}

function loadMore() {
    page.value++;
    fetchProducts(true);
}

watch(showUnavailable, () => {
    page.value = 1;
    syncFiltersToQuery();
    fetchProducts();
});

// Debounced search
watch(searchInput, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        page.value = 1;
        syncFiltersToQuery();
        fetchProducts();
    }, 300);
});

onMounted(async () => {
    try {
        const { data } = await getStatus();
        if (data.default_filters) {
            defaultFilters.value = data.default_filters;
        }
    } catch {
        // ignore - defaults just won't apply
    }

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
