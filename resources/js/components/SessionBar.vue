<template>
    <div class="sticky top-0 z-50 flex items-center justify-between bg-zinc-900 border-b border-zinc-800 px-4 py-2 text-sm">
        <span class="font-mono" :class="neverExpires ? 'text-zinc-400' : timeColor">
            {{ neverExpires ? 'Session active' : `Session expires in ${formattedTime}` }}
        </span>
        <button
            @click="handleLogout"
            class="text-zinc-400 transition hover:text-zinc-200"
        >
            Log out
        </button>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { getStatus, logout } from '../api/index.js';

const router = useRouter();
const secondsRemaining = ref(0);
const neverExpires = ref(false);
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
    return 'text-zinc-400';
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
        if (data.expires_at) {
            const expiresAt = new Date(data.expires_at);
            secondsRemaining.value = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
            timer = setInterval(tick, 1000);
        } else {
            neverExpires.value = true;
        }
    } catch {
        router.push({ name: 'landing' });
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>
