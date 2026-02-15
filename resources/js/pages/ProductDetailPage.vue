<template>
    <div class="min-h-screen bg-zinc-950">
        <SessionBar />

        <div class="mx-auto max-w-4xl px-4 py-6">
            <!-- Back link -->
            <router-link
                :to="{ name: 'products', query: route.query }"
                class="mb-6 inline-flex items-center gap-1.5 text-sm text-zinc-400 transition hover:text-zinc-200"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Back to matches
            </router-link>

            <!-- Loading skeleton -->
            <div v-if="loading" class="space-y-6">
                <div class="animate-pulse space-y-3">
                    <div class="h-8 w-96 rounded bg-zinc-800"></div>
                    <div class="h-5 w-64 rounded bg-zinc-800"></div>
                    <div class="h-5 w-48 rounded bg-zinc-800"></div>
                </div>
                <div class="animate-pulse space-y-3">
                    <div class="h-6 w-40 rounded bg-zinc-800"></div>
                    <div v-for="i in 3" :key="i" class="h-24 rounded-lg bg-zinc-800"></div>
                </div>
            </div>

            <!-- Error state -->
            <div v-else-if="error" class="py-20 text-center">
                <p class="text-zinc-400">{{ error }}</p>
                <router-link
                    :to="{ name: 'products', query: route.query }"
                    class="mt-3 inline-block text-sm text-indigo-400 transition hover:text-indigo-300"
                >
                    Back to matches
                </router-link>
            </div>

            <!-- Product detail -->
            <template v-else-if="product">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-zinc-100">
                        {{ matchName }}
                    </h1>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-400">
                        <span v-if="product.starts_at">{{ formatDate(product.starts_at) }}</span>
                        <span v-if="product.venue" class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                            {{ product.venue.name }}
                        </span>
                        <span v-if="product.competition">{{ product.competition.name }}</span>
                    </div>
                    <div v-if="!product.time_confirmed && product.starts_at" class="mt-2">
                        <span class="rounded-full bg-amber-900/50 px-2.5 py-0.5 text-xs font-medium text-amber-300">
                            Time TBC
                        </span>
                    </div>
                </div>

                <!-- Ticket options -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-zinc-100">
                        Tickets
                        <span class="ml-2 text-sm font-normal text-zinc-500">
                            ({{ product.ticket_options.length }})
                        </span>
                    </h2>

                    <div v-if="product.ticket_options.length === 0" class="rounded-lg border border-zinc-800 bg-zinc-900 px-4 py-8 text-center">
                        <p class="text-zinc-400">No tickets currently available for this match.</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="option in product.ticket_options"
                            :key="option.id"
                            class="rounded-lg border border-zinc-800 bg-zinc-900 p-4"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-medium text-zinc-100">{{ option.name }}</h3>

                                    <p v-if="option.ticket_category?.human_description" class="mt-1 text-sm text-zinc-400">
                                        {{ option.ticket_category.human_description }}
                                    </p>

                                    <!-- Category details -->
                                    <div v-if="option.ticket_category" class="mt-2 flex flex-wrap gap-2">
                                        <span v-if="option.ticket_category.is_hospitality" class="rounded-full bg-purple-900/50 px-2 py-0.5 text-xs text-purple-300">
                                            Hospitality
                                        </span>
                                        <span v-if="option.ticket_category.has_food_included" class="rounded-full bg-emerald-900/50 px-2 py-0.5 text-xs text-emerald-300">
                                            Food included
                                        </span>
                                        <span v-if="option.ticket_category.has_drinks_included" class="rounded-full bg-blue-900/50 px-2 py-0.5 text-xs text-blue-300">
                                            Drinks included
                                        </span>
                                        <span v-if="option.ticket_category.has_lounge_access" class="rounded-full bg-amber-900/50 px-2 py-0.5 text-xs text-amber-300">
                                            Lounge access
                                        </span>
                                        <span v-if="option.ticket_category.has_padded_seats" class="rounded-full bg-zinc-700/50 px-2 py-0.5 text-xs text-zinc-300">
                                            Padded seats
                                        </span>
                                        <span v-if="option.ticket_category.seat_location" class="rounded-full bg-zinc-700/50 px-2 py-0.5 text-xs text-zinc-300">
                                            {{ option.ticket_category.seat_location }}
                                        </span>
                                    </div>
                                </div>

                                <div class="shrink-0 text-right sm:ml-4">
                                    <div class="text-lg font-semibold text-zinc-100">
                                        {{ formatPrice(option.price) }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        Max {{ option.max_purchase_qty }} tickets
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional info -->
                <div v-if="product.information || product.notes" class="mt-8 space-y-4">
                    <div v-if="product.information" class="rounded-lg border border-zinc-800 bg-zinc-900 p-4">
                        <h3 class="mb-2 text-sm font-medium text-zinc-300">Information</h3>
                        <p class="text-sm text-zinc-400 whitespace-pre-line">{{ product.information }}</p>
                    </div>
                    <div v-if="product.notes" class="rounded-lg border border-zinc-800 bg-zinc-900 p-4">
                        <h3 class="mb-2 text-sm font-medium text-zinc-300">Notes</h3>
                        <p class="text-sm text-zinc-400 whitespace-pre-line">{{ product.notes }}</p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { getProduct } from '../api/index.js';
import SessionBar from '../components/SessionBar.vue';

const route = useRoute();

const product = ref(null);
const loading = ref(true);
const error = ref(null);

const matchName = computed(() => {
    if (!product.value) return '';
    if (product.value.home_team && product.value.away_team) {
        return `${product.value.home_team.name} vs ${product.value.away_team.name}`;
    }
    return product.value.name;
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: product.value?.currency || 'GBP',
    }).format(price);
}

onMounted(async () => {
    try {
        const { data } = await getProduct(route.params.id);
        product.value = data;
    } catch {
        error.value = 'Failed to load product details.';
    } finally {
        loading.value = false;
    }
});
</script>
