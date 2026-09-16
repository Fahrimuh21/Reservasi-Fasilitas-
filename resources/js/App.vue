<script setup>
/**
 * App.vue — Pure App Shell
 *
 * Komponen ini HANYA menjadi entry point untuk GlobalLayout.
 * Tidak ada state lokal, tidak ada konten layar di sini.
 *
 * Struktur final DOM:
 *   <div id="app">
 *     <GlobalLayout>            ← persistent, tidak pernah unmount
 *       <AppSidebar>
 *         <RouterView>          ← hanya area ini yang berubah
 *       </AppSidebar>
 *     </GlobalLayout>
 *   </div>
 */
import GlobalLayout from './layouts/GlobalLayout.vue';
</script>

<template>
    <GlobalLayout />
</template>
