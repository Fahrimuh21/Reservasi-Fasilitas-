<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import { Activity, CalendarDays, Check, CheckCheck, ClipboardList, Clock3, Inbox, LoaderCircle, RefreshCw, Search, Wrench, X } from 'lucide-vue-next';
import WorkflowDialog from '../components/WorkflowDialog.vue';
import { apiError, useLiveCollection } from '../composables/useLiveCollection';
import { formatDate, formatTime, statusLabel } from '../utils/reservations';
import '../../css/workflow.css';

const { items: reservations, loading, refreshing, error, updatedAt, refresh } = useLiveCollection('/api/officer/reservations');
const { items: facilities, error: facilityError, refresh: refreshFacilities } = useLiveCollection('/api/officer/facilities?per_page=100', 15000);
const { items: reports, error: reportError, loading: reportsLoading, refresh: refreshReports } = useLiveCollection('/api/officer/reports');
const section = ref('reservations');
const activeTab = ref('pending');
const search = ref('');
const decision = ref(null);
const note = ref('');
const saving = ref(false);
const actionError = ref('');
const notice = ref('');
const facilityBusy = ref(null);
const tabs = [
    { value: 'pending', label: 'Menunggu' }, { value: 'approved', label: 'Disetujui' },
    { value: 'rejected', label: 'Ditolak' }, { value: 'cancelled', label: 'Dibatalkan' },
];
const count = status => reservations.value.filter(item => item.status === status).length;
const filtered = computed(() => reservations.value.filter(item =>
    item.status === activeTab.value && [item.facility?.name, item.user?.name, item.user?.email, item.purpose, String(item.id)]
        .some(value => value?.toLowerCase().includes(search.value.toLowerCase()))
));
const initials = name => (name || 'Pengguna').split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();
const openReports = computed(() => reports.value.filter(item => ['new', 'in_progress'].includes(item.status)).length);
const dialogTitle = computed(() => decision.value?.kind === 'report'
    ? 'Proses laporan'
    : decision.value?.action === 'approve'
        ? 'Konfirmasi reservasi'
        : decision.value?.action === 'cancel'
            ? 'Batalkan reservasi'
            : 'Tolak reservasi');
const facilityLocation = facility => [
    facility.location?.name,
    facility.location?.building,
    facility.location?.floor ? `Lantai ${facility.location.floor}` : null,
].filter(Boolean).join(' · ');

function openDecision(item, action, kind = 'reservation') {
    decision.value = { item, action, kind };
    note.value = '';
    actionError.value = '';
    notice.value = '';
}

async function submitDecision() {
    if (saving.value || !decision.value) return;
    saving.value = true;
    actionError.value = '';
    const { item, action, kind } = decision.value;
    try {
        const response = kind === 'report'
            ? await axios.put(`/api/officer/reports/${item.id}`, { status: action, resolution_note: note.value })
            : action === 'cancel'
                ? await axios.patch(`/api/officer/reservations/${item.id}/cancel`, { cancellation_reason: note.value })
                : await axios.patch(`/api/officer/reservations/${item.id}/${action}`, { decision_note: note.value });
        notice.value = response.data.message;
        decision.value = null;
        await Promise.all([refresh(), refreshReports(), refreshFacilities()]);
    } catch (failure) {
        actionError.value = apiError(failure);
        await refresh();
    } finally {
        saving.value = false;
    }
}

async function changeFacility(item) {
    if (facilityBusy.value || item.status === 'inactive') return;
    const action = item.status === 'pending' ? 'approve' : item.status === 'active' ? 'set-maintenance' : 'complete-maintenance';
    const label = item.status === 'active' ? 'Tandai dalam perbaikan' : 'Aktifkan fasilitas';
    if (!window.confirm(`${label}: ${item.name}?`)) return;
    facilityBusy.value = item.id;
    notice.value = '';
    actionError.value = '';
    try {
        const response = await axios.patch(`/api/officer/facilities/${item.id}/${action}`);
        notice.value = response.data.message;
        await refreshFacilities();
    } catch (failure) {
        actionError.value = apiError(failure);
    } finally {
        facilityBusy.value = null;
    }
}
</script>

<template>
    <section id="screen-officer" class="content-wrap workflow">
        <header class="wf-header">
            <div><p class="eyebrow">RUANGKITA / PETUGAS</p><h1>Permintaan &amp; operasional</h1></div>
            <div class="wf-actions">
                <span class="wf-live" :class="{ offline: error }"><Activity :size="15" />{{ error ? 'Koneksi bermasalah' : updatedAt ? 'Tersinkron' : 'Menghubungkan...' }}</span>
                <button class="wf-icon-button" title="Perbarui data" aria-label="Perbarui data" :disabled="refreshing" @click="refresh(); refreshReports(); refreshFacilities()"><RefreshCw :size="17" :class="{ 'wf-spin': refreshing }" /></button>
            </div>
        </header>
        <div class="wf-metrics">
            <div class="wf-metric"><ClipboardList :size="26" /><div><strong>{{ count('pending') }}</strong><span>Menunggu konfirmasi</span></div></div>
            <div class="wf-metric"><CheckCheck :size="26" /><div><strong>{{ count('approved') }}</strong><span>Reservasi disetujui</span></div></div>
            <div class="wf-metric"><Wrench :size="26" /><div><strong>{{ openReports }}</strong><span>Laporan terbuka</span></div></div>
        </div>
        <p v-if="notice" class="wf-notice" role="status">{{ notice }}</p>
        <p v-if="error" class="wf-notice wf-error" role="alert">{{ error }}</p>
        <p v-if="actionError && !decision" class="wf-notice wf-error" role="alert">{{ actionError }}</p>
        <nav class="wf-tabs" aria-label="Jenis permintaan">
            <button :aria-pressed="section === 'reservations'" @click="section = 'reservations'"><CalendarDays :size="16" /> Reservasi</button>
            <button :aria-pressed="section === 'reports'" @click="section = 'reports'"><Wrench :size="16" /> Laporan <small>{{ openReports }}</small></button>
        </nav>
        <section v-if="section === 'reservations'" aria-label="Antrean reservasi">
            <div class="wf-toolbar">
                <div class="wf-tabs" aria-label="Status reservasi">
                    <button v-for="tab in tabs" :key="tab.value" :aria-pressed="activeTab === tab.value" @click="activeTab = tab.value">{{ tab.label }} <small>{{ count(tab.value) }}</small></button>
                </div>
                <label class="wf-search"><Search :size="17" /><input v-model="search" placeholder="Cari pemohon atau fasilitas" aria-label="Cari reservasi" /></label>
            </div>
            <div v-if="loading" class="wf-empty" role="status"><LoaderCircle class="wf-spin" :size="24" /> Memuat permintaan...</div>
            <div v-else-if="!filtered.length" class="wf-empty"><Inbox :size="32" /><h3>{{ search ? 'Permintaan tidak ditemukan' : 'Tidak ada permintaan ' + statusLabel(activeTab).toLowerCase() }}</h3></div>
            <div v-else class="wf-list">
                <article v-for="item in filtered" :key="item.id" class="wf-request" :data-reservation-id="item.id">
                    <div class="wf-request-top">
                        <div class="wf-request-title"><span class="wf-avatar">{{ initials(item.user?.name) }}</span><div><h3>{{ item.facility?.name }}</h3><p>{{ item.user?.name }} / {{ item.user?.email }}</p></div></div>
                        <span class="wf-status" :class="item.status">{{ statusLabel(item.status) }}</span>
                    </div>
                    <div class="wf-meta"><span><CalendarDays :size="15" />{{ formatDate(item.start_at) }}</span><span><Clock3 :size="15" />{{ formatTime(item.start_at) }} - {{ formatTime(item.end_at) }} WIB</span></div>
                    <p class="wf-purpose">{{ item.purpose }}</p>
                    <p v-if="item.decision_note">Catatan: {{ item.decision_note }}</p>
                    <p v-if="item.cancellation_reason">Pembatalan: {{ item.cancellation_reason }}</p>
                    <footer class="wf-request-footer">
                        <span class="wf-reference">REQ-{{ String(item.id).padStart(4, '0') }} / Diajukan {{ formatDate(item.created_at) }}</span>
                        <div v-if="['pending', 'approved'].includes(item.status)" class="wf-actions">
                            <button v-if="item.status === 'pending'" class="wf-button wf-danger" :disabled="saving" @click="openDecision(item, 'reject')"><X :size="16" /> Tolak</button>
                            <button v-if="item.status === 'pending'" class="wf-button wf-primary" :disabled="saving" @click="openDecision(item, 'approve')"><Check :size="16" /> Konfirmasi</button>
                            <button v-if="item.status === 'approved'" class="wf-button wf-danger" :disabled="saving" @click="openDecision(item, 'cancel')"><X :size="16" /> Batalkan</button>
                        </div>
                    </footer>
                </article>
            </div>
        </section>
        <section v-else class="wf-lower-section" aria-label="Laporan kerusakan">
            <p v-if="reportError" class="wf-notice wf-error" role="alert">{{ reportError }}</p>
            <div v-if="reportsLoading" class="wf-empty">Memuat laporan...</div>
            <div v-else-if="!reports.length" class="wf-empty"><Inbox :size="32" /><h3>Belum ada laporan</h3></div>
            <div class="wf-list">
                <article v-for="report in reports" :key="report.id" class="wf-request">
                    <div class="wf-request-top"><div><h3>{{ report.facility?.name }}</h3><p>{{ report.user?.name }} / {{ report.category }}</p></div><span class="wf-status" :class="report.status">{{ statusLabel(report.status) }}</span></div>
                    <p class="wf-purpose">{{ report.description }}</p>
                    <div v-if="report.photos?.length" class="wf-photos"><a v-for="photo in report.photos" :key="photo.id" :href="'/storage/' + photo.file_path" target="_blank" rel="noopener noreferrer"><img :src="'/storage/' + photo.file_path" alt="Foto kerusakan fasilitas" loading="lazy" /></a></div>
                    <p v-if="report.resolution_note">Catatan: {{ report.resolution_note }}</p>
                    <footer class="wf-request-footer">
                        <span class="wf-reference">LAP-{{ report.id }} / {{ formatDate(report.created_at) }}</span>
                        <div class="wf-actions">
                            <button v-if="report.status === 'new'" class="wf-button wf-danger" @click="openDecision(report, 'rejected', 'report')"><X :size="16" /> Tolak</button>
                            <button v-if="report.status === 'new'" class="wf-button wf-primary" @click="openDecision(report, 'in_progress', 'report')"><Wrench :size="16" /> Proses</button>
                            <button v-if="report.status === 'in_progress'" class="wf-button wf-primary" @click="openDecision(report, 'resolved', 'report')"><Check :size="16" /> Selesaikan</button>
                        </div>
                    </footer>
                </article>
            </div>
        </section>
        <section class="wf-lower-section" aria-label="Kondisi fasilitas">
            <div class="wf-section-head"><h2>Kondisi fasilitas</h2><span class="wf-reference">{{ facilities.length }} fasilitas</span></div>
            <p v-if="facilityError" class="wf-notice wf-error" role="alert">{{ facilityError }}</p>
            <div class="wf-facilities">
                <div v-for="facility in facilities" :key="facility.id" class="wf-facility">
                    <div><strong>{{ facility.name }}</strong><p>{{ facility.code }} · {{ facility.type?.name || 'Tipe tidak tersedia' }}</p><p>{{ facilityLocation(facility) }}</p><small>{{ facility.capacity }} orang</small></div>
                    <span class="wf-status" :class="facility.status">{{ statusLabel(facility.status) }}</span>
                    <button class="wf-icon-button" :title="facility.status === 'inactive' ? 'Dikelola oleh Admin' : facility.status === 'active' ? 'Tandai dalam perbaikan' : 'Aktifkan fasilitas'" :aria-label="facility.status === 'inactive' ? facility.name + ' dikelola oleh Admin' : facility.status === 'active' ? 'Tandai dalam perbaikan ' + facility.name : 'Aktifkan ' + facility.name" :disabled="!!facilityBusy || facility.status === 'inactive'" @click="changeFacility(facility)">
                        <LoaderCircle v-if="facilityBusy === facility.id" :size="16" class="wf-spin" /><Wrench v-else-if="facility.status === 'active'" :size="16" /><Check v-else :size="16" />
                    </button>
                </div>
            </div>
        </section>
        <WorkflowDialog v-if="decision" :title="dialogTitle" :busy="saving" :submit-label="decision.kind === 'report' ? 'Simpan status' : decision.action === 'approve' ? 'Setujui reservasi' : decision.action === 'cancel' ? 'Batalkan reservasi' : 'Tolak reservasi'" @close="decision = null" @submit="submitDecision">
            <div class="wf-summary"><strong>{{ decision.item.facility?.name }}</strong><span>{{ decision.item.user?.name }}</span><span v-if="decision.kind === 'reservation'">{{ formatDate(decision.item.start_at) }} / {{ formatTime(decision.item.start_at) }} - {{ formatTime(decision.item.end_at) }} WIB</span><p>{{ decision.item.purpose || decision.item.description }}</p></div>
            <label v-if="decision.kind === 'report'">Status<select v-model="decision.action"><option v-if="decision.item.status === 'new'" value="in_progress">Diproses</option><option v-if="decision.item.status === 'new'" value="rejected">Ditolak</option><option v-if="decision.item.status === 'in_progress'" value="resolved">Selesai</option></select></label>
            <label>Catatan {{ ['reject', 'cancel'].includes(decision.action) ? 'alasan' : '(opsional)' }}<textarea v-model="note" rows="3" maxlength="1000" :required="['reject', 'cancel'].includes(decision.action)" /></label>
            <p v-if="actionError" class="wf-notice wf-error" role="alert">{{ actionError }}</p>
        </WorkflowDialog>
    </section>
</template>
