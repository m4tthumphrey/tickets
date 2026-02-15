<template>
    <div class="min-h-screen">
        <AdminNavBar />

        <div class="mx-auto max-w-5xl px-4 py-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-lg font-semibold text-zinc-100">Access Codes</h1>
                <router-link
                    to="/admin/access-codes/create"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500"
                >
                    Create new
                </router-link>
            </div>

            <div v-if="loading" class="text-center text-zinc-400 py-12">Loading...</div>

            <div v-else-if="accessCodes.length === 0" class="text-center text-zinc-500 py-12">
                No access codes yet.
            </div>

            <div v-else class="overflow-x-auto rounded-lg border border-zinc-800">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-900 text-xs uppercase tracking-wider text-zinc-400 border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Label</th>
                            <th class="px-4 py-3">Teams</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3">Expires</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        <tr
                            v-for="ac in accessCodes"
                            :key="ac.id"
                            @click="$router.push(`/admin/access-codes/${ac.id}`)"
                            class="cursor-pointer bg-zinc-950 hover:bg-zinc-900 transition"
                        >
                            <td class="px-4 py-3 font-mono text-zinc-100">{{ ac.code }}</td>
                            <td class="px-4 py-3 text-zinc-300">{{ ac.label || '-' }}</td>
                            <td class="px-4 py-3 text-zinc-400">
                                {{ ac.teams?.map(t => t.name).join(', ') || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusClass(ac)" class="inline-block rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ statusLabel(ac) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-400">{{ formatDate(ac.created_at) }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ expiresDisplay(ac) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="pagination.lastPage > 1" class="flex items-center justify-center gap-2 mt-6">
                <button
                    v-for="page in pagination.lastPage"
                    :key="page"
                    @click="loadPage(page)"
                    :class="page === pagination.currentPage ? 'bg-indigo-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200'"
                    class="rounded px-3 py-1 text-sm transition"
                >
                    {{ page }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminNavBar from '../components/AdminNavBar.vue';
import { getAccessCodes } from '../api/admin.js';

const accessCodes = ref([]);
const loading = ref(true);
const pagination = ref({ currentPage: 1, lastPage: 1 });

function statusLabel(ac) {
    if (ac.is_revoked) return 'Revoked';
    if (ac.expires_at && new Date(ac.expires_at) < new Date()) return 'Expired';
    if (!ac.activated_at) return 'Not activated';
    return 'Active';
}

function statusClass(ac) {
    const label = statusLabel(ac);
    if (label === 'Active') return 'bg-green-900/50 text-green-300';
    if (label === 'Not activated') return 'bg-zinc-800 text-zinc-400';
    return 'bg-red-900/50 text-red-300';
}

function expiresDisplay(ac) {
    if (!ac.expires_after) return 'Never';
    if (ac.expires_at) return `${formatDateTime(ac.expires_at)} (${ac.expires_after} min)`;
    return `Not activated (${ac.expires_after} min)`;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

async function loadPage(page) {
    loading.value = true;
    try {
        const { data } = await getAccessCodes({ page });
        accessCodes.value = data.data;
        pagination.value = { currentPage: data.current_page, lastPage: data.last_page };
    } catch {
        // handle silently
    } finally {
        loading.value = false;
    }
}

onMounted(() => loadPage(1));
</script>
