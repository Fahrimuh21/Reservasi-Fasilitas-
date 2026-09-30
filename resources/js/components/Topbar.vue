<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { LogIn, LogOut } from 'lucide-vue-next';
import axios from 'axios';
import { authUser, clearAuthSession, getToken } from '../auth';
import BrandLogo from './BrandLogo.vue';

const route = useRoute();
const router = useRouter();
const signingOut = ref(false);
const title = computed(() => route.meta.title || 'RuangKita');

async function logout() {
    if (signingOut.value) return;
    signingOut.value = true;
    const token = getToken();

    try {
        if (token) {
            await axios.post('/api/logout', {}, {
                headers: { Authorization: `Bearer ${token}` },
                timeout: 2500,
            });
        }
    } catch (error) {
        console.warn('Logout API failed', error);
    } finally {
        clearAuthSession();
        window.location.replace(router.resolve({ name: 'landing' }).href);
    }
}
</script>

<template>
    <header class="topbar app-topbar">
        <RouterLink :to="{ name: 'landing' }" class="topbar-brand" aria-label="RuangKita, halaman utama"><BrandLogo :size="38" /></RouterLink>
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
.topbar-brand { display: none; min-width: 0; text-decoration: none; }
.topbar-location, .topbar-account { display: flex; align-items: center; gap: 12px; min-width: 0; font-size: 13px; }
.topbar-location span, .account-name { color: var(--muted); }
.topbar-location strong { overflow-wrap: anywhere; }
.account-name { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.topbar-account button { width: 44px; height: 44px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 8px; background: white; }
.topbar-account a { display: flex; align-items: center; gap: 6px; color: var(--primary); }
@media (max-width: 900px) { .app-topbar { min-height: 68px; padding: 10px 68px 10px 16px; } .topbar-brand { display: inline-flex; } .topbar-location, .topbar-account { display: none; } }
</style>
