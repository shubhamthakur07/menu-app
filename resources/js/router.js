import { createRouter, createWebHistory } from 'vue-router';
import HomeView from './views/HomeView.vue';
import MenuView from './views/MenuView.vue';
import OrderTrackView from './views/OrderTrackView.vue';
import LoginView from './views/LoginView.vue';
import RegisterView from './views/RegisterView.vue';
import OwnerDashboardView from './views/OwnerDashboardView.vue';
import AdminPlansView from './views/AdminPlansView.vue';
import AboutView from './views/AboutView.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomeView,
    },
    {
        path: '/about',
        name: 'about',
        component: AboutView,
    },
    {
        path: '/about-us',
        redirect: '/about',
    },
    {
        path: '/r/:slug',
        name: 'menu',
        component: MenuView,
        props: true,
    },
    {
        path: '/order-track/:orderCode',
        name: 'order-track',
        component: OrderTrackView,
        props: true,
    },
    {
        path: '/owner/login',
        name: 'login',
        component: LoginView,
    },
    {
        path: '/owner/register',
        name: 'register',
        component: RegisterView,
    },
    {
        path: '/owner/dashboard',
        name: 'dashboard',
        component: OwnerDashboardView,
    },
    {
        path: '/admin/plans',
        name: 'admin-plans',
        component: AdminPlansView,
    },
    {
        path: '/admin/subscribers',
        redirect: '/admin/plans',
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
