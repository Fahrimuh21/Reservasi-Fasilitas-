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
import AppSidebar from '../components/AppSidebar.vue';
</script>

<template>
    <div class="app-shell">
        <!--
            AppSidebar berada DI LUAR slot RouterView.
            Vue Router hanya mengganti konten <RouterView> — bukan seluruh tree ini.
            Oleh karena itu sidebar tidak pernah di-remount, tidak ada flicker,
            dan state sidebar (collapsed, hover, dsb) tetap utuh.
        -->
        <AppSidebar>
            <!--
                Slot ini diteruskan ke AppSidebar yang menampilkan topbar + konten.
                RouterView berada di dalam slot AppSidebar agar layout tetap terstruktur.
            -->
            <main class="main-content" id="main-content" role="main">
                <!--
                    <RouterView> dengan transition bawaan Vue.
                    `v-slot` digunakan agar kita bisa mengontrol transition
                    per-komponen tanpa menyentuh struktur luar.
                -->
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
