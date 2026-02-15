<template>
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-sm space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-zinc-100">Enter your access code</h1>
                <p class="mt-2 text-sm text-zinc-400">Paste or type the code you were given.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <input
                        ref="input"
                        v-model="code"
                        type="text"
                        maxlength="20"
                        placeholder="ACCESS CODE"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-3 text-center text-lg font-mono tracking-widest text-zinc-100 uppercase placeholder-zinc-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :disabled="loading"
                    />
                </div>

                <div v-if="error" class="rounded-lg bg-red-950/50 border border-red-800 px-4 py-3 text-sm text-red-300">
                    {{ error }}
                </div>

                <button
                    type="submit"
                    :disabled="loading || !code.trim()"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-950 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="loading">Validating...</span>
                    <span v-else>Continue</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { validateCode, getStatus } from '../api/index.js';

const route = useRoute();
const router = useRouter();
const input = ref(null);
const code = ref('');
const error = ref('');
const loading = ref(false);

async function submit() {
    if (!code.value.trim()) return;

    loading.value = true;
    error.value = '';

    try {
        await validateCode(code.value);
        router.push({ name: 'products' });
    } catch (e) {
        error.value = e.response?.data?.error || 'Something went wrong.';
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    // Check if already authenticated
    try {
        const { data } = await getStatus();
        if (data.authenticated) {
            router.push({ name: 'products' });
            return;
        }
    } catch {
        // not authenticated, continue
    }

    // Auto-validate if code is in URL
    if (route.params.code) {
        code.value = route.params.code;
        await submit();
        return;
    }

    input.value?.focus();
});
</script>
