<script setup>

import {
    computed,
    ref,
    watch,
    nextTick,
    onMounted,
    onUnmounted
} from "vue";

import {
    useRoute,
    useRouter
} from "vue-router";

import axios from "axios";
import { Building2, CalendarDays, ClipboardCheck, Wrench, Settings, ChevronLeft, ChevronRight, LogOut, Menu, X as CloseIcon } from 'lucide-vue-next';
import BrandLogo from './common/BrandLogo.vue';
import notification from './notification/notificationService';

import {
    clearAuthSession,
    authUser,
    getToken
} from "../auth";



const router = useRouter();
const route = useRoute();


const collapsed = ref(false);
const mobileOpen = ref(false);
const isMobile = ref(false);
const mobileToggle = ref(null);
const sidebarPanel = ref(null);
let mobileQuery;
let previousBodyOverflow = '';


const user = authUser;
const menuIcons = { facilities: Building2, reservations: CalendarDays, officer: ClipboardCheck, report: Wrench, admin: Settings };



const menus = [

    {
        name: "facilities",
        label: "Fasilitas",
        description: "Katalog fasilitas kampus",
        icon: "▦",
        roles: [
            "guest",
            "user",
            "officer",
            "admin"
        ]
    },


    {
        name: "reservations",
        label: "Reservasi Saya",
        description: "Riwayat peminjaman",
        icon: "◷",
        roles: [
            "user"
        ]
    },


    {
        name: "officer",
        label: "Petugas",
        description: "Approval reservasi",
        icon: "✓",
        roles: [
            "officer"
        ]
    },


    {
        name: "report",
        label: "Laporan Kerusakan",
        description: "Kerusakan fasilitas",
        icon: "⚠",
        roles: [
            "user"
        ]
    },


    {
        name: "admin",
        label: "Admin Panel",
        description: "Manajemen sistem",
        icon: "◫",
        roles: [
            "admin"
        ]
    }

];



const visibleMenus = computed(() => {


    const role = user.value?.role || "guest";


    return menus.filter(menu =>

        menu.roles.includes(role)

    );


});




const roleLabel = computed(() => {


    return user.value?.role
        ?
        user.value.role.toUpperCase()
        :
        "GUEST";


});





function isActive(name) {


    return route.name === name;


}

function closeMobile(restoreFocus = true) {
    if (!mobileOpen.value) return;
    mobileOpen.value = false;
    document.body.style.overflow = previousBodyOverflow;
    if (restoreFocus && isMobile.value) nextTick(() => mobileToggle.value?.focus());
}

function toggleMobile() {
    if (mobileOpen.value) {
        closeMobile();
        return;
    }

    collapsed.value = false;
    previousBodyOverflow = document.body.style.overflow;
    mobileOpen.value = true;
    document.body.style.overflow = 'hidden';
    nextTick(() => sidebarPanel.value?.querySelector('a[href], button:not(.collapse)')?.focus());
}

function handleKeydown(event) {
    if (event.key === 'Escape' && mobileOpen.value) {
        event.preventDefault();
        closeMobile();
        return;
    }
    if (event.key !== 'Tab' || !mobileOpen.value || !sidebarPanel.value) return;
    const focusable = [...sidebarPanel.value.querySelectorAll('a[href], button:not([disabled])')];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
}

function handleMobileChange(event) {
    isMobile.value = event.matches;
    if (!event.matches) closeMobile(false);
}

watch(() => route.fullPath, () => closeMobile(false));
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    mobileQuery = window.matchMedia('(max-width: 900px)');
    isMobile.value = mobileQuery.matches;
    mobileQuery.addEventListener('change', handleMobileChange);
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    mobileQuery?.removeEventListener('change', handleMobileChange);
    document.body.style.overflow = previousBodyOverflow;
});






async function performLogout() {
    const token = getToken();

    try {

        if (token) {
            await axios.post("/api/logout", {}, {
                headers: {
                    Authorization: `Bearer ${token}`
                },
                timeout: 2500
            });
        }

    }

    catch (error) {

        console.warn(
            "Logout API failed",
            error
        );

    }

    finally {
        clearAuthSession();
        window.location.replace(router.resolve({ name: "landing" }).href);
    }


}

function logout() {
    notification.confirm({
        title: 'Keluar dari akun?',
        message: 'Sesi Anda akan diakhiri dan halaman akan kembali ke beranda.',
        confirmLabel: 'Ya, keluar',
        tone: 'danger',
        onConfirm: performLogout
    });
}
</script>
<template>
    <button ref="mobileToggle" class="mobile-sidebar-toggle" :class="{ 'is-open': mobileOpen }" type="button"
        :title="mobileOpen ? 'Tutup menu' : 'Buka menu'" :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'"
        :aria-expanded="mobileOpen" aria-controls="app-sidebar" @click="toggleMobile">
        <Menu v-if="!mobileOpen" :size="21" />
        <CloseIcon v-else :size="21" />
    </button>


    <div v-if="mobileOpen" class="mobile-sidebar-backdrop" aria-hidden="true" @click="closeMobile"></div>


    <aside id="app-sidebar" ref="sidebarPanel" class="sidebar" aria-label="Navigasi utama"
        :aria-hidden="isMobile && !mobileOpen ? 'true' : undefined" :inert="isMobile && !mobileOpen" :class="{
            'is-collapsed': collapsed,
            'mobile-open': mobileOpen
        }">




        <!-- BRAND -->
        <div class="brand">
            <BrandLogo mode="icon" :size="42" inverse />
            <div v-if="!collapsed" class="brand-info">
                <strong>
                    RuangKita
                </strong>
                <span>
                    Campus Facility
                </span>
            </div>
        </div>
        <div v-if="!collapsed" class="section-title">
            WORKSPACE
        </div>
        <nav>
            <RouterLink v-for="item in visibleMenus" :key="item.name" :to="{
                name: item.name
            }" class="nav-link" :title="item.label" :aria-label="item.label" :class="{
                active: isActive(item.name)
            }" @click="closeMobile">
                <div class="nav-icon">
                    <component :is="menuIcons[item.name]" :size="20" />
                </div>
                <div v-if="!collapsed" class="nav-content">
                    <strong>
                        {{ item.label }}
                    </strong>
                    <small>
                        {{ item.description }}
                    </small>
                </div>
            </RouterLink>
        </nav>
        <button class="collapse" :title="collapsed ? 'Perluas menu' : 'Ciutkan menu'"
            :aria-label="collapsed ? 'Perluas menu' : 'Ciutkan menu'" @click="collapsed = !collapsed">
            <ChevronRight v-if="collapsed" :size="18" />
            <ChevronLeft v-else :size="18" />
        </button>
        <div class="sidebar-bottom">
            <button class="logout" v-if="user" title="Keluar" aria-label="Keluar" @click="logout">
                <span>
                    <LogOut :size="18" />
                </span>
                <span v-if="!collapsed">
                    Logout
                </span>
            </button>
            <div class="user-box">
                <div class="avatar">
                    {{
                        (user?.name || "U")
                    .charAt(0)
                    .toUpperCase()
                    }}
                </div>
                <div v-if="!collapsed" class="user-detail">
                    <strong>
                        {{ user?.name }}
                    </strong>
                    <span>
                        {{ roleLabel }}
                    </span>
                </div>
            </div>
        </div>
    </aside>
</template>
