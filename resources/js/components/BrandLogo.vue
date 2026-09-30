<script setup>
import { computed, ref, watch } from 'vue';
import { brandConfig } from '../config/brand';

const props = defineProps({
    source: { type: String, default: '' },
    mode: { type: String, default: 'full', validator: value => ['full', 'icon'].includes(value) },
    size: { type: [String, Number], default: 40 },
    inverse: { type: Boolean, default: false },
});

const imageFailed = ref(false);
const imageSource = computed(() => props.source || brandConfig.logoSource);
const dimension = computed(() => typeof props.size === 'number' ? `${props.size}px` : props.size);

watch(imageSource, () => { imageFailed.value = false; });
</script>

<template>
    <span
        class="brand-logo-component"
        :class="{ 'is-inverse': inverse, 'is-icon-only': mode === 'icon' }"
        :style="{ '--brand-logo-size': dimension }"
        :role="mode === 'icon' ? 'img' : undefined"
        :aria-label="mode === 'icon' ? brandConfig.logoAlt : undefined"
    >
        <span class="brand-logo-component__mark" :aria-hidden="mode === 'icon' ? 'true' : undefined">
            <img
                v-if="imageSource && !imageFailed"
                :src="imageSource"
                :alt="mode === 'icon' ? '' : brandConfig.logoAlt"
                @error="imageFailed = true"
            />
            <span v-else aria-hidden="true">R</span>
        </span>
        <span v-if="mode === 'full'" class="brand-logo-component__name">{{ brandConfig.name }}</span>
    </span>
</template>

<style scoped>
.brand-logo-component {
    display: inline-flex;
    min-width: 0;
    align-items: center;
    gap: 10px;
    color: var(--blue-700, #1d4ed8);
    font-size: clamp(14px, calc(var(--brand-logo-size) * .42), 18px);
    font-weight: 700;
    line-height: 1;
}
.brand-logo-component__mark {
    width: var(--brand-logo-size);
    height: var(--brand-logo-size);
    flex: 0 0 var(--brand-logo-size);
    display: grid;
    place-items: center;
    overflow: hidden;
    border: 1px solid var(--blue-200, #bfdbfe);
    border-radius: calc(var(--brand-logo-size) * .28);
    background: var(--blue-600, #2563eb);
    color: #fff;
    font-size: calc(var(--brand-logo-size) * .5);
    font-weight: 700;
}
.brand-logo-component__mark img { width: 100%; height: 100%; display: block; object-fit: contain; }
.brand-logo-component__name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.brand-logo-component.is-inverse { color: #fff; }
.brand-logo-component.is-inverse .brand-logo-component__mark { border-color: rgba(255,255,255,.45); background: #fff; color: var(--blue-700, #1d4ed8); }
</style>
