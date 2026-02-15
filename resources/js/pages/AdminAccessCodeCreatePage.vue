<template>
    <div class="min-h-screen">
        <AdminNavBar />

        <div class="mx-auto max-w-2xl px-4 py-6">
            <router-link to="/admin" class="text-sm text-zinc-400 hover:text-zinc-200 transition">&larr; Back to list</router-link>

            <h1 class="text-lg font-semibold text-zinc-100 mt-4 mb-6">Create Access Code</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2 block">Code</label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.code"
                            type="text"
                            maxlength="20"
                            placeholder="ACCESS CODE"
                            class="flex-1 rounded border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm font-mono uppercase tracking-widest text-zinc-100 placeholder-zinc-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <button
                            type="button"
                            @click="generateCode"
                            class="rounded border border-zinc-700 px-3 py-2 text-sm text-zinc-400 transition hover:border-zinc-500 hover:text-zinc-200"
                        >
                            Generate
                        </button>
                    </div>
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
                    <label class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2 block">Margin (%)</label>
                    <input
                        v-model.number="form.margin"
                        type="number"
                        min="0"
                        max="100"
                        class="w-32 rounded border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p class="text-xs text-zinc-500 mt-1">Applied to cost price, rounded up to nearest 5.</p>
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

                <button
                    type="submit"
                    :disabled="saving || !form.code.trim()"
                    class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="saving">Creating...</span>
                    <span v-else>Create Access Code</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminNavBar from '../components/AdminNavBar.vue';
import { createAccessCode, getAdminFilters } from '../api/admin.js';

const router = useRouter();
const teams = ref([]);
const competitions = ref([]);
const saving = ref(false);
const error = ref('');
const neverExpires = ref(false);
const searchCompetitions = ref('');
const searchDefaultTeams = ref('');
const searchAssociatedTeams = ref('');

const form = reactive({
    code: '',
    label: '',
    expires_after: 5,
    margin: 15,
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
    let items = teams.value;
    if (searchDefaultTeams.value) {
        const q = searchDefaultTeams.value.toLowerCase();
        items = items.filter(t => t.name.toLowerCase().includes(q));
    }
    const sel = new Set(form.default_filters.teams);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

const filteredAssociatedTeams = computed(() => {
    let items = teams.value;
    if (searchAssociatedTeams.value) {
        const q = searchAssociatedTeams.value.toLowerCase();
        items = items.filter(t => t.name.toLowerCase().includes(q));
    }
    const sel = new Set(form.team_ids);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

watch(neverExpires, (val) => {
    if (val) form.expires_after = null;
    else form.expires_after = 5;
});

function generateCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.code = code;
}

async function submit() {
    saving.value = true;
    error.value = '';

    try {
        const payload = { ...form };
        if (neverExpires.value) payload.expires_after = null;
        const { data } = await createAccessCode(payload);
        router.push(`/admin/access-codes/${data.id}`);
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to create access code.';
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await getAdminFilters();
        teams.value = data.teams;
        competitions.value = data.competitions;
    } catch {
        // silently fail
    }
});
</script>
