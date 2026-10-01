<script setup>
import { CheckCircle2, CircleAlert, Info, TriangleAlert, X } from 'lucide-vue-next';
import { notification, notificationState } from './notificationService';

const icons = { success: CheckCircle2, error: CircleAlert, warning: TriangleAlert, info: Info };
const titles = { success: 'Berhasil', error: 'Terjadi kesalahan', warning: 'Perhatian', info: 'Informasi' };
</script>

<template>
    <div class="toast-viewport" aria-live="polite" aria-atomic="false">
        <TransitionGroup name="toast" tag="div" class="toast-list">
            <article v-for="toast in notificationState.toasts" :key="toast.id" class="toast-item" :class="`is-${toast.type}`" :role="toast.type === 'error' ? 'alert' : 'status'">
                <component :is="icons[toast.type]" :size="20" class="toast-icon" aria-hidden="true" />
                <div><strong>{{ toast.title || titles[toast.type] }}</strong><p>{{ toast.message }}</p></div>
                <button type="button" aria-label="Tutup notifikasi" @click="notification.dismiss(toast.id)"><X :size="17" /></button>
            </article>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-viewport { position: fixed; z-index: 2000; top: 20px; right: 20px; width: min(380px, calc(100vw - 32px)); pointer-events: none; }
.toast-list { display: grid; gap: 10px; }
.toast-item { display: grid; grid-template-columns: auto minmax(0,1fr) auto; gap: 12px; align-items: start; padding: 14px; border: 1px solid var(--slate-200); border-left: 4px solid var(--blue-600); border-radius: 12px; background: #fff; color: var(--slate-900); box-shadow: 0 18px 45px rgba(15,23,42,.16); pointer-events: auto; }
.toast-item.is-success { border-left-color: var(--success); }.toast-item.is-error { border-left-color: var(--danger); }.toast-item.is-warning { border-left-color: var(--warning); }.toast-item.is-info { border-left-color: var(--info, #0284c7); }
.toast-icon { margin-top: 1px; color: var(--blue-600); }.is-success .toast-icon { color: var(--success); }.is-error .toast-icon { color: var(--danger); }.is-warning .toast-icon { color: var(--warning); }.is-info .toast-icon { color: var(--info, #0284c7); }
.toast-item strong { display: block; margin-bottom: 3px; font-size: 13px; }.toast-item p { margin: 0; color: var(--slate-600); font-size: 12px; line-height: 1.5; overflow-wrap: anywhere; }
.toast-item button { display: grid; width: 32px; height: 32px; place-items: center; margin: -6px -6px 0 0; border: 0; border-radius: 8px; background: transparent; color: var(--slate-500); }.toast-item button:hover { background: var(--slate-100); color: var(--slate-900); }
.toast-enter-active, .toast-leave-active { transition: opacity .22s ease, transform .22s ease; }.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(18px); }
@media (max-width: 520px) { .toast-viewport { inset: 12px 16px auto; width: auto; }.toast-item { padding: 12px; } }
@media (prefers-reduced-motion: reduce) { .toast-enter-active, .toast-leave-active { transition: none; } }
</style>
