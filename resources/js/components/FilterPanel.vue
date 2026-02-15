<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Date range</h3>
            <div class="space-y-2">
                <input
                    type="date"
                    :value="selected.dateFrom"
                    @input="$emit('update:filters', { ...selected, dateFrom: $event.target.value })"
                    class="w-full rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <input
                    type="date"
                    :value="selected.dateTo"
                    @input="$emit('update:filters', { ...selected, dateTo: $event.target.value })"
                    class="w-full rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
            </div>
        </div>

        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Teams</h3>
            <input
                v-if="teams.length > 10"
                v-model="teamSearch"
                type="text"
                placeholder="Search teams..."
                class="w-full mb-2 rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <label
                    v-for="item in filteredTeams"
                    :key="item.id"
                    class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300 hover:text-zinc-100"
                >
                    <input
                        type="checkbox"
                        :value="item.id"
                        :checked="selected.teams.includes(item.id)"
                        @change="toggle('teams', item.id)"
                        class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                    />
                    {{ item.name }}
                </label>
            </div>
        </div>

        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Competitions</h3>
            <input
                v-if="competitions.length > 10"
                v-model="competitionSearch"
                type="text"
                placeholder="Search competitions..."
                class="w-full mb-2 rounded border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-200 placeholder-zinc-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <label
                    v-for="item in filteredCompetitions"
                    :key="item.id"
                    class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300 hover:text-zinc-100"
                >
                    <input
                        type="checkbox"
                        :value="item.id"
                        :checked="selected.competitions.includes(item.id)"
                        @change="toggle('competitions', item.id)"
                        class="rounded border-zinc-600 bg-zinc-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0"
                    />
                    {{ item.name }}
                </label>
            </div>
        </div>

        <button
            v-if="hasActiveFilters"
            @click="$emit('clear')"
            class="w-full rounded border border-zinc-700 px-3 py-2 text-sm text-zinc-400 transition hover:border-zinc-500 hover:text-zinc-200"
        >
            Clear all filters
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    competitions: { type: Array, default: () => [] },
    teams: { type: Array, default: () => [] },
    selected: {
        type: Object,
        default: () => ({ competitions: [], teams: [], dateFrom: '', dateTo: '' }),
    },
});

const emit = defineEmits(['update:filters', 'clear']);

const competitionSearch = ref('');
const teamSearch = ref('');

const filteredCompetitions = computed(() => {
    let items = props.competitions;
    if (competitionSearch.value) {
        const q = competitionSearch.value.toLowerCase();
        items = items.filter(c => c.name.toLowerCase().includes(q));
    }
    const sel = new Set(props.selected.competitions);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

const filteredTeams = computed(() => {
    let items = props.teams;
    if (teamSearch.value) {
        const q = teamSearch.value.toLowerCase();
        items = items.filter(t => t.name.toLowerCase().includes(q));
    }
    const sel = new Set(props.selected.teams);
    return [...items].sort((a, b) => (sel.has(b.id) ? 1 : 0) - (sel.has(a.id) ? 1 : 0));
});

const hasActiveFilters = computed(() => {
    return props.selected.competitions.length > 0
        || props.selected.teams.length > 0
        || props.selected.dateFrom
        || props.selected.dateTo;
});

function toggle(group, id) {
    const current = [...props.selected[group]];
    const index = current.indexOf(id);
    if (index === -1) {
        current.push(id);
    } else {
        current.splice(index, 1);
    }
    emit('update:filters', { ...props.selected, [group]: current });
}
</script>
