<script setup>
import { nextTick, ref, watch } from 'vue';
import { CircleAlert, LoaderCircle, X } from 'lucide-vue-next';
import { notification, notificationState } from './notificationService';

const dialog = ref(null);

watch(() => notificationState.confirmation?.id, async id => {
    await nextTick();
    if (id && dialog.value && !dialog.value.open) dialog.value.showModal();
});
</script>

<template>
    <dialog v-if="notificationState.confirmation" ref="dialog" class="confirm-dialog" aria-labelledby="confirm-dialog-title" @cancel.prevent="notification.cancelConfirmation()" @click="($event.target === dialog) && notification.cancelConfirmation()">
        <div class="confirm-dialog__head">
            <span class="confirm-dialog__icon" :class="`is-${notificationState.confirmation.tone}`"><CircleAlert :size="22" /></span>
            <button type="button" aria-label="Tutup konfirmasi" :disabled="notificationState.confirmation.busy" @click="notification.cancelConfirmation()"><X :size="19" /></button>
        </div>
        <h2 id="confirm-dialog-title">{{ notificationState.confirmation.title }}</h2>
        <p>{{ notificationState.confirmation.message }}</p>
        <p v-if="notificationState.confirmation.error" class="confirm-dialog__error" role="alert">{{ notificationState.confirmation.error }}</p>
        <div class="confirm-dialog__actions">
            <button type="button" class="confirm-cancel" :disabled="notificationState.confirmation.busy" @click="notification.cancelConfirmation()">{{ notificationState.confirmation.cancelLabel }}</button>
            <button type="button" class="confirm-submit" :class="`is-${notificationState.confirmation.tone}`" :disabled="notificationState.confirmation.busy" @click="notification.acceptConfirmation()"><LoaderCircle v-if="notificationState.confirmation.busy" :size="17" class="confirm-spin" />{{ notificationState.confirmation.busy ? 'Memproses...' : notificationState.confirmation.confirmLabel }}</button>
        </div>
    </dialog>
</template>

<style scoped>
.confirm-dialog { width: min(440px, calc(100vw - 32px)); padding: 24px; border: 0; border-radius: 14px; background: #fff; color: var(--slate-900); box-shadow: 0 28px 80px rgba(15,23,42,.24); }.confirm-dialog::backdrop { background: rgba(15,23,42,.48); backdrop-filter: blur(2px); }
.confirm-dialog__head { display: flex; align-items: center; justify-content: space-between; }.confirm-dialog__icon { display: grid; width: 44px; height: 44px; place-items: center; border-radius: 12px; background: var(--blue-50); color: var(--blue-700); }.confirm-dialog__icon.is-danger { background: #fef2f2; color: var(--danger); }
.confirm-dialog__head > button { display: grid; width: 40px; height: 40px; place-items: center; border: 1px solid var(--slate-200); border-radius: 8px; background: #fff; color: var(--slate-600); }.confirm-dialog h2 { margin: 18px 0 8px; font-size: 21px; }.confirm-dialog > p { margin: 0; color: var(--slate-600); line-height: 1.6; }.confirm-dialog__error { margin-top: 14px !important; padding: 10px 12px; border-radius: 8px; background: #fef2f2; color: #b91c1c !important; font-size: 13px; }
.confirm-dialog__actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }.confirm-dialog__actions button { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; border-radius: 8px; font: inherit; font-weight: 600; }.confirm-cancel { border: 1px solid var(--slate-300); background: #fff; color: var(--slate-700); }.confirm-submit { border: 1px solid var(--blue-600); background: var(--blue-600); color: #fff; }.confirm-submit.is-danger { border-color: var(--danger); background: var(--danger); }.confirm-dialog__actions button:disabled { cursor: wait; opacity: .7; }
.confirm-spin { animation: confirm-spin .8s linear infinite; } @keyframes confirm-spin { to { transform: rotate(360deg); } }
@media (max-width: 420px) { .confirm-dialog { padding: 20px; }.confirm-dialog__actions { display: grid; grid-template-columns: 1fr 1fr; }.confirm-dialog__actions button { padding-inline: 10px; } }
@media (prefers-reduced-motion: reduce) { .confirm-spin { animation: none; } }
</style>
