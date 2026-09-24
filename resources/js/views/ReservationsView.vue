<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { Activity, Building2, CalendarDays, CheckCheck, Clock3, Inbox, Plus, RefreshCw, Search, X } from 'lucide-vue-next';
import WorkflowDialog from '../components/WorkflowDialog.vue';
import { apiError, useLiveCollection } from '../composables/useLiveCollection';
import { campusToday, formatDate, formatTime, statusLabel } from '../utils/reservations';
import '../../css/workflow.css';

const route = useRoute();
const { items: reservations, loading, refreshing, error, updatedAt, refresh } = useLiveCollection('/api/reservations');
const { items: facilities, error: facilityError } = useLiveCollection('/api/facilities?per_page=100', 15000);
const search = ref('');
const filter = ref('all');
const showModal = ref(false);
const saving = ref(false);
const formError = ref('');
const notice = ref('');
const selectedDate = ref(campusToday());
const cancelItem = ref(null);
const reason = ref('');
const slots = ref([]);
const slotError = ref('');
const slotsLoading = ref(false);
const form = ref({ facility_id: route.query.facility_id || '', start_time: '09:00', end_time: '10:00', purpose: '' });
const selectedFacility = computed(() => facilities.value.find(item => String(item.id) === String(form.value.facility_id)));
const filtered = computed(() => reservations.value.filter(item =>
    (filter.value === 'all' || item.status === filter.value) &&
    [item.facility?.name, item.purpose, String(item.id)].some(value => value?.toLowerCase().includes(search.value.toLowerCase()))
));
const pendingCount = computed(() => reservations.value.filter(item => item.status === 'pending').length);
const approvedCount = computed(() => reservations.value.filter(item => item.status === 'approved').length);
const bookedHours = computed(() => reservations.value.filter(item => item.status === 'approved')
    .reduce((sum, item) => sum + (new Date(item.end_at) - new Date(item.start_at)) / 3600000, 0).toLocaleString('id-ID', { maximumFractionDigits: 1 }));
const canCancel = item => ['pending', 'approved'].includes(item.status) && new Date(item.start_at) > new Date();
const filters = [{ value: 'all', label: 'Semua' }, { value: 'pending', label: 'Menunggu' }, { value: 'approved', label: 'Disetujui' }, { value: 'rejected', label: 'Ditolak' }, { value: 'cancelled', label: 'Dibatalkan' }];
let slotController;
let timer;

function selectSlot(start) {
    form.value.start_time = start;
    const [hour, minute] = start.split(':').map(Number);
    const end = Math.min(hour * 60 + minute + 60, 1200);
    form.value.end_time = `${String(Math.floor(end / 60)).padStart(2, '0')}:${String(end % 60).padStart(2, '0')}`;
}

function openBooking() {
    formError.value = '';
    notice.value = '';
    if (!form.value.facility_id && facilities.value.length) form.value.facility_id = facilities.value[0].id;
    showModal.value = true;
}

async function loadSlots() {
    slotController?.abort();
    if (!showModal.value || !form.value.facility_id || !selectedDate.value) {
        slots.value = [];
        slotsLoading.value = false;
        return;
    }
    const controller = new AbortController();
    slotController = controller;
    slotsLoading.value = true;
    try {
        const response = await axios.get(`/api/facilities/${form.value.facility_id}/availability`, {
            params: { date: selectedDate.value }, signal: controller.signal, timeout: 15000,
        });
        if (!controller.signal.aborted) {
            slots.value = response.data.data.slots;
            slotError.value = '';
        }
    } catch (failure) {
        if (!controller.signal.aborted) {
            slots.value = [];
            slotError.value = apiError(failure);
        }
    } finally {
        if (slotController === controller) slotsLoading.value = false;
    }
}

watch([showModal, selectedDate, () => form.value.facility_id], () => {
    slots.value = [];
    slotError.value = '';
    loadSlots();
});
watch(facilities, items => {
    if (!form.value.facility_id && items.length) form.value.facility_id = items[0].id;
});
onMounted(() => {
    if (typeof route.query.slot === 'string' && /^([01]\d):[03]0$/.test(route.query.slot)) selectSlot(route.query.slot);
    if (route.query.facility_id) openBooking();
    timer = window.setInterval(() => {
        if (showModal.value && !document.hidden && !slotsLoading.value) loadSlots();
    }, 3000);
});
onUnmounted(() => { window.clearInterval(timer); slotController?.abort(); });

async function createReservation() {
    if (saving.value) return;
    formError.value = '';
    const { start_time, end_time } = form.value;
    if (!selectedFacility.value) { formError.value = 'Pilih fasilitas yang aktif.'; return; }
    if (end_time <= start_time) { formError.value = 'Jam selesai harus setelah jam mulai.'; return; }
    const selectedSlots = slots.value.filter(slot => slot.start < end_time && slot.end > start_time);
    if (!selectedSlots.length || selectedSlots.some(slot => slot.status !== 'tersedia')) {
        formError.value = 'Jadwal tidak tersedia. Pilih waktu lain.';
        return;
    }
    saving.value = true;
    try {
        const response = await axios.post('/api/reservations', {
            facility_id: form.value.facility_id, purpose: form.value.purpose.trim(),
            start_at: `${selectedDate.value} ${start_time}:00`,
            end_at: `${selectedDate.value} ${end_time}:00`,
        });
        showModal.value = false;
        form.value.purpose = '';
        filter.value = 'pending';
        search.value = '';
        notice.value = `Reservasi REQ-${String(response.data.data.id).padStart(4, '0')} tersimpan. Menunggu konfirmasi petugas.`;
        await refresh();
    } catch (failure) {
        formError.value = apiError(failure);
        await loadSlots();
    } finally {
        saving.value = false;
    }
}

function openCancel(item) {
    cancelItem.value = item;
    reason.value = '';
    formError.value = '';
}
async function cancelReservation() {
    if (saving.value || !cancelItem.value) return;
    saving.value = true;
    formError.value = '';
    try {
        await axios.post(`/api/reservations/${cancelItem.value.id}/cancel`, { reason: reason.value.trim() });
        cancelItem.value = null;
        notice.value = 'Reservasi berhasil dibatalkan.';
        await refresh();
    } catch (failure) {
        formError.value = apiError(failure);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <section id="screen-reservations" class="content-wrap workflow">
        <header class="wf-header">
            <div><p class="eyebrow">RUANGKITA / PENGGUNA</p><h1>Reservasi saya</h1></div>
            <div class="wf-actions">
                <span class="wf-live" :class="{ offline: error }"><Activity :size="15" />{{ error ? 'Koneksi bermasalah' : updatedAt ? 'Tersinkron' : 'Menghubungkan...' }}</span>
                <button class="wf-icon-button" title="Perbarui reservasi" aria-label="Perbarui reservasi" :disabled="refreshing" @click="refresh"><RefreshCw :size="17" :class="{ 'wf-spin': refreshing }" /></button>
                <button class="wf-button wf-primary" @click="openBooking"><Plus :size="17" /> Reservasi baru</button>
            </div>
        </header>
        <div class="wf-metrics">
            <div class="wf-metric"><Clock3 :size="26" /><div><strong>{{ pendingCount }}</strong><span>Menunggu konfirmasi</span></div></div>
            <div class="wf-metric"><CheckCheck :size="26" /><div><strong>{{ approvedCount }}</strong><span>Disetujui</span></div></div>
            <div class="wf-metric"><CalendarDays :size="26" /><div><strong>{{ bookedHours }}</strong><span>Total jam disetujui</span></div></div>
        </div>
        <p v-if="notice" class="wf-notice" role="status">{{ notice }}</p>
        <p v-if="error" class="wf-notice wf-error" role="alert">{{ error }}</p>
        <div class="wf-section-head"><h2>Riwayat reservasi</h2><span class="wf-reference">{{ reservations.length }} reservasi</span></div>
        <div class="wf-toolbar">
            <nav class="wf-tabs" aria-label="Filter reservasi"><button v-for="tab in filters" :key="tab.value" :aria-pressed="filter === tab.value" @click="filter = tab.value">{{ tab.label }}</button></nav>
            <label class="wf-search"><Search :size="17" /><input v-model="search" placeholder="Cari fasilitas atau keperluan" aria-label="Cari reservasi" /></label>
        </div>
        <div v-if="loading" class="wf-empty" role="status">Memuat reservasi...</div>
        <div v-else-if="!filtered.length" class="wf-empty"><Inbox :size="32" /><h3>{{ search || filter !== 'all' ? 'Tidak ada reservasi yang sesuai' : 'Belum ada reservasi' }}</h3></div>
        <div v-else class="wf-list">
            <article v-for="item in filtered" :key="item.id" class="wf-request" :data-reservation-id="item.id">
                <div class="wf-request-top">
                    <div class="wf-request-title"><span class="wf-avatar"><Building2 :size="21" /></span><div><h3>{{ item.facility?.name }}</h3><p>{{ item.facility?.facility_type?.name }}</p></div></div>
                    <span class="wf-status" :class="item.status">{{ statusLabel(item.status) }}</span>
                </div>
                <div class="wf-meta"><span><CalendarDays :size="15" />{{ formatDate(item.start_at) }}</span><span><Clock3 :size="15" />{{ formatTime(item.start_at) }} - {{ formatTime(item.end_at) }} WIB</span></div>
                <p class="wf-purpose">{{ item.purpose }}</p>
                <p v-if="item.decision_note">Catatan petugas: {{ item.decision_note }}</p>
                <p v-if="item.cancellation_reason">Pembatalan: {{ item.cancellation_reason }}</p>
                <footer class="wf-request-footer">
                    <span class="wf-reference">REQ-{{ String(item.id).padStart(4, '0') }} / Diajukan {{ formatDate(item.created_at) }}</span>
                    <button v-if="canCancel(item)" class="wf-button wf-danger" @click="openCancel(item)"><X :size="16" /> Batalkan</button>
                </footer>
            </article>
        </div>
        <WorkflowDialog v-if="showModal" title="Reservasi baru" :busy="saving" :disabled="!selectedFacility || !!slotError || !slots.length" submit-label="Ajukan reservasi" @close="showModal = false" @submit="createReservation">
            <p v-if="facilityError" class="wf-notice wf-error" role="alert">{{ facilityError }}</p>
            <label>Fasilitas<select v-model="form.facility_id" aria-label="Fasilitas" required autofocus><option value="" disabled>Pilih fasilitas</option><option v-for="facility in facilities" :key="facility.id" :value="facility.id">{{ facility.name }}</option></select></label>
            <label>Tanggal<input v-model="selectedDate" type="date" :min="campusToday()" required /></label>
            <div class="wf-field-pair">
                <label>Mulai (WIB)<input v-model="form.start_time" type="time" min="07:00" max="19:30" step="1800" required /></label>
                <label>Selesai (WIB)<input v-model="form.end_time" type="time" min="07:30" max="20:00" step="1800" required /></label>
            </div>
            <div>
                <h3 class="wf-reference">Ketersediaan jadwal</h3>
                <p v-if="slotError" class="wf-notice wf-error" role="alert">{{ slotError }}</p>
                <p v-else-if="slotsLoading && !slots.length" class="wf-reference" role="status">Memuat jadwal...</p>
                <div class="wf-slots" aria-label="Slot jadwal"><button v-for="slot in slots" :key="slot.start" type="button" :disabled="slot.status !== 'tersedia'" :aria-pressed="form.start_time === slot.start" :title="slot.status === 'tersedia' ? 'Tersedia' : 'Tidak tersedia'" @click="selectSlot(slot.start)">{{ slot.start }}</button></div>
            </div>
            <label>Keperluan<textarea v-model="form.purpose" rows="3" maxlength="1000" required /></label>
            <p v-if="formError" class="wf-notice wf-error" role="alert">{{ formError }}</p>
        </WorkflowDialog>
        <WorkflowDialog v-if="cancelItem" title="Batalkan reservasi" :busy="saving" submit-label="Batalkan reservasi" @close="cancelItem = null" @submit="cancelReservation">
            <div class="wf-summary"><strong>{{ cancelItem.facility?.name }}</strong><span>{{ formatDate(cancelItem.start_at) }} / {{ formatTime(cancelItem.start_at) }} - {{ formatTime(cancelItem.end_at) }} WIB</span></div>
            <label>Alasan pembatalan<textarea v-model="reason" rows="3" maxlength="1000" required autofocus /></label>
            <p v-if="formError" class="wf-notice wf-error" role="alert">{{ formError }}</p>
        </WorkflowDialog>
    </section>
</template>
