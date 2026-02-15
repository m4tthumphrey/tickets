<template>
    <div class="sticky top-0 z-50 flex items-center justify-between bg-zinc-900 border-b border-zinc-800 px-4 py-2 text-sm">
        <router-link to="/admin" class="font-semibold text-zinc-100 hover:text-white">Admin</router-link>
        <button
            @click="handleLogout"
            class="text-zinc-400 transition hover:text-zinc-200"
        >
            Log out
        </button>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { adminLogout } from '../api/admin.js';

const router = useRouter();

async function handleLogout() {
    try {
        await adminLogout();
    } catch {
        // proceed anyway
    }
    router.push({ name: 'admin-login' });
}
</script>
