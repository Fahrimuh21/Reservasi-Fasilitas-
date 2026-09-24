<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { LogIn, LogOut } from 'lucide-vue-next';
import axios from 'axios';
import { authUser, clearAuthSession } from '../auth';

const route = useRoute();
const router = useRouter();
const signingOut = ref(false);
const title = computed(() => route.meta.title || 'RuangKita');

async function logout() {
    if (signingOut.value) return;
    signingOut.value = true;
    try {
        await axios.post('/api/logout');
    } finally {
        clearAuthSession();
        signingOut.value = false;
        router.push({ name: 'login' });
    }
}
</script>

<template>
    <header class="topbar app-topbar">
        <div class="topbar-location"><span>Workspace</span><span aria-hidden="true">/</span><strong>{{ title }}</strong></div>
        <div class="topbar-account">
            <span v-if="authUser" class="account-name">{{ authUser.name }}</span>
            <button v-if="authUser" type="button" title="Keluar" aria-label="Keluar" :disabled="signingOut" @click="logout().catch(() => {})"><LogOut :size="18" /></button>
            <RouterLink v-else :to="{ name: 'login' }"><LogIn :size="17" /> Login</RouterLink>
        </div>
    </header>
</template>

<style scoped>
.app-topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 66px; flex-shrink: 0; padding: 16px 28px; }
.topbar-location, .topbar-account { display: flex; align-items: center; gap: 12px; min-width: 0; font-size: 13px; }
.topbar-location span, .account-name { color: var(--muted); }
.topbar-location strong { overflow-wrap: anywhere; }
.account-name { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.topbar-account button { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; background: white; }
.topbar-account a { display: flex; align-items: center; gap: 6px; color: var(--primary); }
@media (max-width: 680px) { .app-topbar { padding: 12px 16px 12px 70px; min-height: 70px; } .topbar-location span, .account-name { display: none; } }
</style>
