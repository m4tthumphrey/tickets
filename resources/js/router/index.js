import { createRouter, createWebHistory } from 'vue-router';
import { getStatus } from '../api/index.js';
import LandingPage from '../pages/LandingPage.vue';
import ProductsPage from '../pages/ProductsPage.vue';
import ProductDetailPage from '../pages/ProductDetailPage.vue';

const routes = [
    {
        path: '/',
        name: 'landing',
        component: LandingPage,
    },
    {
        path: '/matches',
        name: 'products',
        component: ProductsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/matches/:id',
        name: 'product',
        component: ProductDetailPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/:code',
        name: 'code',
        component: LandingPage,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    if (!to.meta.requiresAuth) return true;

    try {
        const { data } = await getStatus();
        if (data.authenticated) return true;
    } catch {
        // fall through to redirect
    }

    return { name: 'landing' };
});

export default router;
