<template>
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-sm space-y-6 text-center">
            <div>
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-950/50 border border-emerald-800">
                    <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold tracking-tight text-zinc-100">You're in</h1>
                <p class="mt-2 text-sm text-zinc-400">Your access code has been validated.</p>
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-900 px-6 py-4">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Time remaining</p>
                <p class="mt-1 text-3xl font-mono font-bold" :class="timeColor">
                    {{ formattedTime }}
                </p>
            </div>

            <button
                @click="handleLogout"
                class="text-sm text-zinc-500 transition hover:text-zinc-300"
            >
                Log out
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { getStatus, logout } from '../api/index.js';

const router = useRouter();
const secondsRemaining = ref(0);
let timer = null;

const formattedTime = computed(() => {
    const total = Math.max(0, secondsRemaining.value);
    const hours = Math.floor(total / 3600);
    const mins = Math.floor((total % 3600) / 60);
    const secs = total % 60;

    if (hours > 0) {
        return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    return `${mins}:${String(secs).padStart(2, '0')}`;
});

const timeColor = computed(() => {
    if (secondsRemaining.value <= 60) return 'text-red-400';
    if (secondsRemaining.value <= 300) return 'text-amber-400';
    return 'text-emerald-400';
});

function tick() {
    secondsRemaining.value--;
    if (secondsRemaining.value <= 0) {
        clearInterval(timer);
        router.push({ name: 'landing' });
    }
}

async function handleLogout() {
    try {
        await logout();
    } catch {
        // proceed anyway
    }
    router.push({ name: 'landing' });
}

onMounted(async () => {
    try {
        const { data } = await getStatus();
        if (!data.authenticated) {
            router.push({ name: 'landing' });
            return;
        }
        const expiresAt = new Date(data.expires_at);
        secondsRemaining.value = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        timer = setInterval(tick, 1000);
    } catch {
        router.push({ name: 'landing' });
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>
