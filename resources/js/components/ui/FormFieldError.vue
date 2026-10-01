<script setup>
import { CircleAlert } from 'lucide-vue-next';

defineProps({ message: { type: String, default: '' } });
</script>

<template>
    <Transition name="field-error">
        <span v-if="message" class="field-error" role="alert"><CircleAlert :size="14" />{{ message }}</span>
    </Transition>
</template>

<style scoped>
.field-error { display: flex; align-items: flex-start; gap: 6px; color: #b91c1c; font-size: 11px; font-weight: 500; line-height: 1.45; }.field-error svg { flex: 0 0 auto; margin-top: 1px; }
.field-error-enter-active, .field-error-leave-active { transition: opacity .18s ease, transform .18s ease; }.field-error-enter-from, .field-error-leave-to { opacity: 0; transform: translateY(-4px); }
@media (prefers-reduced-motion: reduce) { .field-error-enter-active, .field-error-leave-active { transition: none; } }
</style>
