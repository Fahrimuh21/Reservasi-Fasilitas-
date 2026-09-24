<script setup>
import { onMounted, ref } from 'vue';
import { X, LoaderCircle } from 'lucide-vue-next';

const props = defineProps({ title: String, busy: Boolean, submitLabel: String, disabled: Boolean });
const emit = defineEmits(['close', 'submit']);
const dialog = ref(null);
const close = () => { if (!props.busy) emit('close'); };
onMounted(() => dialog.value.showModal());
</script>

<template>
    <dialog ref="dialog" class="workflow-dialog" aria-labelledby="workflow-dialog-title"
        @cancel.prevent="close" @click="($event.target === dialog) && close()">
        <form @submit.prevent="emit('submit')">
            <header class="wf-section-head">
                <h2 id="workflow-dialog-title">{{ title }}</h2>
                <button type="button" class="wf-icon-button" title="Tutup" aria-label="Tutup" :disabled="busy" @click="close"><X :size="20" /></button>
            </header>
            <fieldset :disabled="busy" class="wf-fields"><slot /></fieldset>
            <footer class="wf-dialog-footer">
                <button type="button" class="wf-button" :disabled="busy" @click="close">Batal</button>
                <button type="submit" class="wf-button wf-primary" :disabled="busy || disabled">
                    <LoaderCircle v-if="busy" :size="16" class="wf-spin" />
                    {{ busy ? 'Menyimpan...' : submitLabel }}
                </button>
            </footer>
        </form>
    </dialog>
</template>
