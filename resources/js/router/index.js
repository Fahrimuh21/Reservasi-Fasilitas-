import { createRouter, createWebHistory } from 'vue-router';
import { isAuthenticated, hasRole } from '../auth';

const LoginView = () => import('../views/LoginView.vue');
const RegisterView = () => import('../views/RegisterView.vue');
const LandingView = () => import('../views/LandingView.vue');
const FacilitiesView = () => import('../views/FacilitiesView.vue');
const ReservationsView = () => import('../views/ReservationsView.vue');
const OfficerView = () => import('../views/OfficerView.vue');
const ReportView = () => import('../views/ReportView.vue');
const AdminView = () => import('../views/AdminView.vue');

const routes = [
    { path: '/', name: 'landing', component: LandingView, meta: { title: 'Selamat Datang' } },
    { path: '/login', name: 'login', component: LoginView, meta: { title: 'Masuk' } },
    { path: '/register', name: 'register', component: RegisterView, meta: { title: 'Daftar' } },
    {
        path: '/facilities',
        name: 'facilities',
        component: FacilitiesView,
        meta: { title: 'Katalog Fasilitas', public: true, roles: ['guest', 'user', 'officer', 'admin'] },
    },
    {
        path: '/reservations',
        name: 'reservations',
        component: ReservationsView,
        meta: { title: 'Reservasi Saya', roles: ['user'] },
    },
    {
        path: '/officer',
        name: 'officer',
        component: OfficerView,
        meta: { title: 'Dashboard Petugas', roles: ['officer'] },
    },
    {
        path: '/report',
        name: 'report',
        component: ReportView,
        meta: { title: 'Laporan Kerusakan', roles: ['user'] },
    },
    {
        path: '/admin',
        name: 'admin',
        component: AdminView,
        meta: { title: 'Dashboard Admin', roles: ['admin'] },
    },
    { path: '/:pathMatch(.*)*', redirect: '/facilities' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach((to, from, next) => {
    const publicPages = ['login', 'register', 'landing'];

    // Landing tetap dapat dibuka setelah login agar pengguna bisa kembali ke halaman utama.
    if (publicPages.includes(to.name) && isAuthenticated() && to.name !== 'landing') {
        return next({ name: 'facilities' });
    }

    // Jika halaman tidak publik dan user belum login, arahkan ke login
    if (!publicPages.includes(to.name) && !to.meta?.public && !isAuthenticated()) {
        return next({ name: 'login' });
    }

    // Role based protection
    if (isAuthenticated() && to.meta?.roles && !hasRole(...to.meta.roles)) {
        return next({ name: 'facilities' });
    }

    next();
});

router.afterEach((to) => {
    document.title = to.meta?.title ? `${to.meta.title} | RuangKita` : 'RuangKita';
});

export default router;
