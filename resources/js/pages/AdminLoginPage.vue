<template>
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-sm space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-zinc-100">Admin Login</h1>
                <p class="mt-2 text-sm text-zinc-400">Sign in to manage access codes.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <input
                        v-model="email"
                        type="email"
                        placeholder="Email"
                        autocomplete="email"
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :disabled="loading"
                    />
                </div>

                <div>
                    <input
                        v-model="password"
                        type="password"
                        placeholder="Password"
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm text-zinc-100 placeholder-zinc-600 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :disabled="loading"
                    />
                </div>

                <div v-if="error" class="rounded-lg bg-red-950/50 border border-red-800 px-4 py-3 text-sm text-red-300">
                    {{ error }}
                </div>

                <button
                    type="submit"
                    :disabled="loading || !email.trim() || !password"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-zinc-950 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="loading">Signing in...</span>
                    <span v-else>Sign in</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { adminLogin } from '../api/admin.js';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

async function submit() {
    if (!email.value.trim() || !password.value) return;

    loading.value = true;
    error.value = '';

    try {
        await adminLogin(email.value, password.value);
        router.push({ name: 'admin-dashboard' });
    } catch (e) {
        error.value = e.response?.data?.error || 'Invalid credentials.';
    } finally {
        loading.value = false;
    }
}
</script>
