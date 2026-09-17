<script setup>
/**
 * GlobalLayout — Persistent App Shell
 *
 * Struktur DOM yang dijamin tidak berubah saat navigasi:
 *
 *  <div class="app-shell">
 *    <AppSidebar />          ← TIDAK pernah di-unmount/re-render
 *    <main>
 *      <RouterView />        ← HANYA area ini yang diganti saat route berubah
 *    </main>
 *  </div>
 *
 * Dengan memisahkan AppSidebar dari RouterView, aksi MCP yang memanipulasi
 * state konten (data, form, fetch) tidak akan pernah menyentuh struktur DOM
 * navigasi utama.
 */
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppSidebar from '../components/AppSidebar.vue';

const route = useRoute();
const isAuthPage = computed(() => ['login', 'register'].includes(route.name));
</script>

<template>
    <div class="app-shell">
        <template v-if="!isAuthPage">
            <AppSidebar>
                <main class="main-content" id="main-content" role="main">
                    <RouterView v-slot="{ Component, route }">
                        <Transition name="fade-slide" mode="out-in">
                            <component
                                :is="Component"
                                :key="route.name"
                            />
                        </Transition>
                    </RouterView>
                </main>
            </AppSidebar>
        </template>

        <template v-else>
            <main class="auth-page" id="auth-page" role="main">
                <RouterView v-slot="{ Component, route }">
                    <Transition name="fade-slide" mode="out-in">
                        <component
                            :is="Component"
                            :key="route.name"
                        />
                    </Transition>
                </RouterView>
            </main>
        </template>
    </div>
</template>

<style scoped>
/*
 * Transisi fade-slide antar halaman.
 * Hanya menganimasikan area konten — sidebar sama sekali tidak tersentuh.
 */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
