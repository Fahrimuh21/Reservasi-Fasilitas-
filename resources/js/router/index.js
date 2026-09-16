/**
 * Campus Facility Management System — Client-Side Router
 *
 * Route → Screen mapping:
 *  /facilities   → Katalog & Grid Ketersediaan   (9b4eaf60a859485ea4a2057a9eba4306)
 *  /reservations → Portal Reservasi & Riwayat    (afa48fa655154f92b6194d75a21aa27b)
 *  /officer      → Dashboard Petugas & Approval  (27f9c22580c44370a6c9f89785168a63)
 *  /report       → Form Pelaporan Kerusakan       (55597836a1f445e4ab3cd358e0c6155b)
 *  /admin        → Dashboard Admin & Rekapitulasi (9400ab25f7044638a44066516e2c8bfd)
 */

import { createRouter, createWebHistory } from 'vue-router';

// Lazy-load each view → code-split per route, sidebar is NEVER re-mounted
const FacilitiesView   = () => import('../views/FacilitiesView.vue');
const ReservationsView = () => import('../views/ReservationsView.vue');
const OfficerView      = () => import('../views/OfficerView.vue');
const ReportView       = () => import('../views/ReportView.vue');
const AdminView        = () => import('../views/AdminView.vue');

const routes = [
    // Default redirect to the facilities catalogue
    {
        path: '/',
        redirect: '/facilities',
    },

    // Screen 1 — Katalog & Grid Ketersediaan Fasilitas 30 Menit
    {
        path: '/facilities',
        name: 'facilities',
        component: FacilitiesView,
        meta: {
            screenId: '9b4eaf60a859485ea4a2057a9eba4306',
            title: 'Katalog Fasilitas',
            breadcrumb: 'Facilities',
        },
    },

    // Screen 2 — Portal Reservasi & Riwayat Pengguna
    {
        path: '/reservations',
        name: 'reservations',
        component: ReservationsView,
        meta: {
            screenId: 'afa48fa655154f92b6194d75a21aa27b',
            title: 'Reservasi Saya',
            breadcrumb: 'My Reservations',
        },
    },

    // Screen 3 — Dashboard Petugas & Antrean Approval
    {
        path: '/officer',
        name: 'officer',
        component: OfficerView,
        meta: {
            screenId: '27f9c22580c44370a6c9f89785168a63',
            title: 'Dashboard Petugas',
            breadcrumb: 'Officer Dashboard',
        },
    },

    // Screen 4 — Form Pelaporan Kerusakan Berfoto & Tiket
    {
        path: '/report',
        name: 'report',
        component: ReportView,
        meta: {
            screenId: '55597836a1f445e4ab3cd358e0c6155b',
            title: 'Laporan Kerusakan',
            breadcrumb: 'Damage Report',
        },
    },

    // Screen 5 — Dashboard Admin & Rekapitulasi Sarana
    {
        path: '/admin',
        name: 'admin',
        component: AdminView,
        meta: {
            screenId: '9400ab25f7044638a44066516e2c8bfd',
            title: 'Dashboard Admin',
            breadcrumb: 'Admin Dashboard',
        },
    },

    // 404 fallback — redirect unmatched paths to facilities
    {
        path: '/:pathMatch(.*)*',
        redirect: '/facilities',
    },
];

const router = createRouter({
    // createWebHistory → clean URLs, no hash (#). Requires Laravel catch-all route.
    history: createWebHistory(),
    routes,
    // Scroll to top on every navigation
    scrollBehavior(_to, _from, savedPosition) {
        if (savedPosition) return savedPosition;
        return { top: 0, behavior: 'smooth' };
    },
});

/**
 * Global navigation guard — update document <title> per route.
 * This runs ONLY in the router layer; it NEVER touches the sidebar DOM.
 */
router.afterEach((to) => {
    const baseTitle = 'RuangKita';
    document.title = to.meta?.title ? `${to.meta.title} — ${baseTitle}` : baseTitle;
});

export default router;
