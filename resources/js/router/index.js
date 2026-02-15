import { createRouter, createWebHistory } from 'vue-router';
import { getStatus } from '../api/index.js';
import { adminStatus } from '../api/admin.js';
import LandingPage from '../pages/LandingPage.vue';
import ProductsPage from '../pages/ProductsPage.vue';
import ProductDetailPage from '../pages/ProductDetailPage.vue';
import AdminLoginPage from '../pages/AdminLoginPage.vue';
import AdminAccessCodesPage from '../pages/AdminAccessCodesPage.vue';
import AdminAccessCodeCreatePage from '../pages/AdminAccessCodeCreatePage.vue';
import AdminAccessCodeDetailPage from '../pages/AdminAccessCodeDetailPage.vue';

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
        path: '/admin/login',
        name: 'admin-login',
        component: AdminLoginPage,
    },
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: AdminAccessCodesPage,
        meta: { requiresAdmin: true },
    },
    {
        path: '/admin/access-codes/create',
        name: 'admin-create',
        component: AdminAccessCodeCreatePage,
        meta: { requiresAdmin: true },
    },
    {
        path: '/admin/access-codes/:id',
        name: 'admin-detail',
        component: AdminAccessCodeDetailPage,
        meta: { requiresAdmin: true },
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
    if (to.meta.requiresAdmin) {
        try {
            const { data } = await adminStatus();
            if (data.authenticated) return true;
        } catch {
            // fall through to redirect
        }
        return { name: 'admin-login' };
    }

    if (to.meta.requiresAuth) {
        try {
            const { data } = await getStatus();
            if (data.authenticated) return true;
        } catch {
            // fall through to redirect
        }
        return { name: 'landing' };
    }

    return true;
});

export default router;
