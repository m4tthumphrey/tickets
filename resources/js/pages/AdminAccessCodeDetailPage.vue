<template>
    <div class="min-h-screen">
        <AdminNavBar />

        <div class="mx-auto max-w-2xl px-4 py-6">
            <router-link to="/admin" class="text-sm text-zinc-400 hover:text-zinc-200 transition">&larr; Back to list</router-link>

            <div v-if="loadingPage" class="text-center text-zinc-400 py-12">Loading...</div>

            <template v-else-if="accessCode">
                <div class="flex items-center justify-between mt-4 mb-6">
                    <h1 class="text-lg font-semibold text-zinc-100">{{ accessCode.code }}</h1>
                    <span :class="statusClass" class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium">
                        {{ statusLabel }}
                    </span>
                </div>

                <form @submit.prevent="save" class="space-y-6">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2 block">Code</label>
                        <input
                            v-model="form.code"
                            type="text"
                            maxlength="20"
                            class="w-full rounded border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm font-mono uppercase tracking-widest text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2 block">Label</label>
                        <input
                            v-model="form.label"
                            type="text"
                            placeholder="Optional description"
                            class="w-full rounded border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2 block">Expires after (minutes)</label>
                        <div class="flex items-center gap-3">
                            <input
                                v-model.number="form.expires_after"
                                type="number"
                                min="1"
                                :disabled="neverExpires"
                                placeholder="60"
                                class="w-32 rounded border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-50"
                            />
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300">
                                <input
                                    type="checkbox"
                                    v-model="neverExpires"
                                    class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                                />
                                Never
                            </label>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Associated Teams</h3>
                        <p class="text-xs text-zinc-500 mb-2">Teams this access code is linked to (for organizational purposes).</p>
                        <input
                            v-model="searchAssociatedTeams"
                            type="text"
                            placeholder="Search teams..."
                            class="w-full mb-2 rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <label
                                v-for="item in filteredAssociatedTeams"
                                :key="item.id"
                                class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300 hover:text-zinc-100"
                            >
                                <input
                                    type="checkbox"
                                    :value="item.id"
                                    v-model="form.team_ids"
                                    class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                                />
                                {{ item.name }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Default Filter: Teams</h3>
                        <input
                            v-model="searchDefaultTeams"
                            type="text"
                            placeholder="Search teams..."
                            class="w-full mb-2 rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <label
                                v-for="item in filteredDefaultTeams"
                                :key="item.id"
                                class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300 hover:text-zinc-100"
                            >
                                <input
                                    type="checkbox"
                                    :value="item.id"
                                    v-model="form.default_filters.teams"
                                    class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                                />
                                {{ item.name }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Default Filter: Competitions</h3>
                        <input
                            v-model="searchCompetitions"
                            type="text"
                            placeholder="Search competitions..."
                            class="w-full mb-2 rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <label
                                v-for="item in filteredCompetitions"
                                :key="item.id"
                                class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300 hover:text-zinc-100"
                            >
                                <input
                                    type="checkbox"
                                    :value="item.id"
                                    v-model="form.default_filters.competitions"
                                    class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                                />
                                {{ item.name }}
                            </label>
                        </div>
                    </div>

                    <div v-if="error" class="rounded-lg bg-red-950/50 border border-red-800 px-4 py-3 text-sm text-red-300">
                        {{ error }}
                    </div>

                    <div v-if="saved" class="rounded-lg bg-green-950/50 border border-green-800 px-4 py-3 text-sm text-green-300">
                        Saved successfully.
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="saving"
                            class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:opacity-50"
                        >
                            <span v-if="saving">Saving...</span>
                            <span v-else>Save Changes</span>
                        </button>

                        <button
                            v-if="!accessCode.is_revoked"
                            type="button"
                            @click="revoke"
                            class="rounded-lg border border-red-800 px-4 py-2.5 text-sm font-medium text-red-300 transition hover:bg-red-950/50"
                        >
                            Revoke
                        </button>

                        <button
                            type="button"
                            @click="confirmDelete"
                            class="rounded-lg border border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:border-red-800 hover:text-red-300"
                        >
                            Delete
                        </button>
                    </div>
                </form>

                <!-- Activity Log -->
                <div class="mt-10">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">Activity Log</h2>

                    <div v-if="logs.length === 0" class="text-sm text-zinc-500">No activity recorded.</div>

                    <div v-else class="overflow-x-auto rounded-lg border border-zinc-800">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-zinc-900 text-xs uppercase tracking-wider text-zinc-400 border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Action</th>
                                    <th class="px-4 py-2">Details</th>
                                    <th class="px-4 py-2">IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800">
                                <tr v-for="log in logs" :key="log.id" class="bg-zinc-950">
                                    <td class="px-4 py-2 text-zinc-400 whitespace-nowrap">{{ formatDateTime(log.created_at) }}</td>
                                    <td class="px-4 py-2 text-zinc-300">{{ log.action }}</td>
                                    <td class="px-4 py-2 text-zinc-400 font-mono text-xs">{{ formatMetadata(log.metadata) }}</td>
                                    <td class="px-4 py-2 text-zinc-500">{{ log.ip_address || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="logPagination.lastPage > 1" class="flex items-center justify-center gap-2 mt-4">
                        <button
                            v-for="page in logPagination.lastPage"
                            :key="page"
                            @click="loadLogs(page)"
                            :class="page === logPagination.currentPage ? 'bg-indigo-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200'"
                            class="rounded px-3 py-1 text-xs transition"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminNavBar from '../components/AdminNavBar.vue';
import { getAccessCode, updateAccessCode, revokeAccessCode, deleteAccessCode, getAdminFilters } from '../api/admin.js';

const route = useRoute();
const router = useRouter();

const accessCode = ref(null);
const logs = ref([]);
const logPagination = ref({ currentPage: 1, lastPage: 1 });
const filterTeams = ref([]);
const competitions = ref([]);
const loadingPage = ref(true);
const saving = ref(false);
const saved = ref(false);
const error = ref('');
const neverExpires = ref(false);
const searchCompetitions = ref('');
const searchDefaultTeams = ref('');
const searchAssociatedTeams = ref('');

const form = reactive({
    code: '',
    label: '',
    expires_after: null,
    default_filters: { competitions: [], teams: [] },
    team_ids: [],
});

const filteredCompetitions = computed(() => {
    let items = competitions.value;
    if (searchCompetitions.value) {
        const q = searchCompetitions.value.toLowerCase();
        items = items.filter(c => c.name.toLowerCase().includes(q));
    }
    const sel = new Set(form.default_filters.competitions);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

const filteredDefaultTeams = computed(() => {
    let items = filterTeams.value;
    if (searchDefaultTeams.value) {
        const q = searchDefaultTeams.value.toLowerCase();
        items = items.filter(t => t.name.toLowerCase().includes(q));
    }
    const sel = new Set(form.default_filters.teams);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

const filteredAssociatedTeams = computed(() => {
    let items = filterTeams.value;
    if (searchAssociatedTeams.value) {
        const q = searchAssociatedTeams.value.toLowerCase();
        items = items.filter(t => t.name.toLowerCase().includes(q));
    }
    const sel = new Set(form.team_ids);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

const statusLabel = computed(() => {
    const ac = accessCode.value;
    if (!ac) return '';
    if (ac.is_revoked) return 'Revoked';
    if (ac.expires_at && new Date(ac.expires_at) < new Date()) return 'Expired';
    if (!ac.activated_at) return 'Not activated';
    return 'Active';
});

const statusClass = computed(() => {
    const label = statusLabel.value;
    if (label === 'Active') return 'bg-green-900/50 text-green-300';
    if (label === 'Not activated') return 'bg-zinc-800 text-zinc-400';
    return 'bg-red-900/50 text-red-300';
});

watch(neverExpires, (val) => {
    if (val) form.expires_after = null;
    else if (form.expires_after === null) form.expires_after = 5;
});

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function formatMetadata(metadata) {
    if (!metadata) return '-';
    return JSON.stringify(metadata);
}

function populateForm(ac) {
    form.code = ac.code;
    form.label = ac.label || '';
    form.expires_after = ac.expires_after;
    form.default_filters = ac.default_filters || { competitions: [], teams: [] };
    form.team_ids = ac.teams?.map(t => t.id) || [];
    neverExpires.value = ac.expires_after === null;
}

async function save() {
    saving.value = true;
    error.value = '';
    saved.value = false;

    try {
        const payload = { ...form };
        if (neverExpires.value) payload.expires_after = null;
        const { data } = await updateAccessCode(route.params.id, payload);
        accessCode.value = data;
        populateForm(data);
        saved.value = true;
        setTimeout(() => saved.value = false, 3000);
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to save.';
    } finally {
        saving.value = false;
    }
}

async function revoke() {
    if (!confirm('Are you sure you want to revoke this access code?')) return;
    try {
        const { data } = await revokeAccessCode(route.params.id);
        accessCode.value = data;
    } catch {
        error.value = 'Failed to revoke.';
    }
}

async function confirmDelete() {
    if (!confirm('Are you sure you want to delete this access code? This cannot be undone.')) return;
    try {
        await deleteAccessCode(route.params.id);
        router.push('/admin');
    } catch {
        error.value = 'Failed to delete.';
    }
}

async function loadLogs(page) {
    try {
        const { data } = await getAccessCode(route.params.id, { log_page: page });
        logs.value = data.logs.data;
        logPagination.value = { currentPage: data.logs.current_page, lastPage: data.logs.last_page };
    } catch {
        // silently fail
    }
}

onMounted(async () => {
    try {
        const [codeRes, filtersRes] = await Promise.all([
            getAccessCode(route.params.id),
            getAdminFilters(),
        ]);

        accessCode.value = codeRes.data.access_code;
        logs.value = codeRes.data.logs.data;
        logPagination.value = { currentPage: codeRes.data.logs.current_page, lastPage: codeRes.data.logs.last_page };
        filterTeams.value = filtersRes.data.teams;
        competitions.value = filtersRes.data.competitions;

        populateForm(accessCode.value);
    } catch {
        // handle error
    } finally {
        loadingPage.value = false;
    }
});
</script>
