<script setup>
/**
 * AppSidebar — Persistent navigation shell.
 *
 * ISOLATION RULES (per arsitektur):
 *  1. Komponen ini menggunakan <RouterLink> — active state dikelola
 *     sepenuhnya oleh Vue Router, bukan reactive ref lokal.
 *  2. State di sini (collapsed, user info) TIDAK pernah bocor ke konten layar.
 *  3. Komponen ini di-mount SATU KALI dan tidak pernah re-render karena
 *     ia berada di luar <RouterView> di GlobalLayout.
 */
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const collapsed = ref(false);

// Navigation items — mapped 1:1 to router named routes
const navItems = [
    {
        name: 'facilities',
        label: 'Katalog Fasilitas',
        icon: '⌂',
        screenId: '9b4eaf60a859485ea4a2057a9eba4306',
    },
    {
        name: 'reservations',
        label: 'Reservasi Saya',
        icon: '◷',
        screenId: 'afa48fa655154f92b6194d75a21aa27b',
    },
    {
        name: 'officer',
        label: 'Dashboard Petugas',
        icon: '✓',
        screenId: '27f9c22580c44370a6c9f89785168a63',
    },
    {
        name: 'report',
        label: 'Laporan Kerusakan',
        icon: '⚠',
        screenId: '55597836a1f445e4ab3cd358e0c6155b',
    },
    {
        name: 'admin',
        label: 'Dashboard Admin',
        icon: '▦',
        screenId: '9400ab25f7044638a44066516e2c8bfd',
    },
];

// Breadcrumb is read from route.meta — this is read-only, never mutated here
const currentBreadcrumb = computed(() => route.meta?.breadcrumb ?? 'RuangKita');
</script>

<template>
    <!-- ===== SIDEBAR SHELL ===== -->
    <!--
        .sidebar lives OUTSIDE <RouterView> in GlobalLayout.
        It is rendered once at app boot and persists across all route changes.
        Vue Router updates only the <RouterLink> active classes via the router's
        internal state — no DOM teardown happens here on navigation.
    -->
    <aside class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">

        <!-- Brand -->
        <div class="brand">
            <span class="brand-mark">R</span>
            <span class="brand-name">RuangKita</span>
        </div>

        <!-- Section label -->
        <div class="workspace-label" aria-hidden="true">WORKSPACE</div>

        <!-- Primary navigation
             RouterLink automatically applies `.router-link-active` and
             `.router-link-exact-active` — we alias the exact-active class
             to our own `.nav-item--active` for styling control.
        -->
        <nav class="side-nav" aria-label="Primary navigation">
            <RouterLink
                v-for="item in navItems"
                :key="item.name"
                :to="{ name: item.name }"
                class="nav-item"
                active-class="nav-item--active"
                :title="collapsed ? item.label : undefined"
                :data-screen-id="item.screenId"
                :id="`nav-${item.name}`"
            >
                <span class="nav-icon" aria-hidden="true">{{ item.icon }}</span>
                <span class="nav-label">{{ item.label }}</span>
            </RouterLink>
        </nav>

        <!-- Collapse toggle -->
        <button
            class="collapse-toggle"
            :title="collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
            aria-label="Toggle sidebar"
            @click="collapsed = !collapsed"
        >
            <span>{{ collapsed ? '›' : '‹' }}</span>
        </button>

        <!-- Bottom area — user info & settings -->
        <div class="sidebar-bottom">
            <RouterLink :to="{ name: 'admin' }" class="quiet-button" id="nav-settings">
                <span aria-hidden="true">⚙</span>
                <span class="nav-label">Settings</span>
            </RouterLink>
            <div class="user-card">
                <div class="avatar" aria-label="User avatar">FA</div>
                <div class="nav-label">
                    <strong>Fahri Ahmad</strong>
                    <small>Administrator</small>
                </div>
                <span class="dots" aria-hidden="true">•••</span>
            </div>
        </div>
    </aside>

    <!-- ===== TOPBAR (persistent, above RouterView) ===== -->
    <div class="topbar-wrapper">
        <header class="topbar">
            <div class="breadcrumb" aria-label="Current location">
                Workspace <span aria-hidden="true">/</span> {{ currentBreadcrumb }}
            </div>
            <button class="help-button" aria-label="Help" id="btn-help">?</button>
        </header>
        <!-- RouterView slot is injected by GlobalLayout -->
        <slot />
    </div>
</template>
