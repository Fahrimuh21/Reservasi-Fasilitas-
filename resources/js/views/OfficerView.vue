<script setup>
/**
 * OfficerView — Screen 3
 * Screen ID: 27f9c22580c44370a6c9f89785168a63
 * Dashboard Petugas & Antrean Approval
 */
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const filterStatus = ref('pending');

const queue = ref([]);
const isLoadingQueue = ref(true);
const queueError = ref('');

const statusTabs = [
    { value: 'pending',  label: 'Menunggu' },
    { value: 'approved', label: 'Disetujui' },
    { value: 'rejected', label: 'Ditolak' },
];

const filteredQueue = computed(() =>
    queue.value.filter(q => q.status === filterStatus.value)
);

const pendingCount  = computed(() => queue.value.filter(q => q.status === 'pending').length);
const approvedCount = computed(() => queue.value.filter(q => q.status === 'approved').length);
const rejectedCount = computed(() => queue.value.filter(q => q.status === 'rejected').length);

async function fetchReservations() {
    isLoadingQueue.value = true;
    queueError.value = '';
    try {
        const response = await axios.get('/api/officer/reservations');
        queue.value = (response.data.data || []).map(reservation => ({
            id: reservation.id,
            user: reservation.user?.name || 'Pengguna',
            avatar: (reservation.user?.name || 'P').slice(0, 2).toUpperCase(),
            facility: reservation.facility?.name || 'Fasilitas',
            date: `${formatDate(reservation.start_at)} - ${formatTime(reservation.end_at)}`,
            submitted: formatDate(reservation.created_at),
            color: reservation.status === 'pending' ? 'yellow' : 'blue',
            status: reservation.status,
        }));
    } catch (error) {
        queueError.value = error.response?.data?.message || 'Antrean reservasi gagal dimuat.';
    } finally {
        isLoadingQueue.value = false;
    }
}

function formatDate(value) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
    }).format(new Date(value));
}

function formatTime(value) {
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit', minute: '2-digit'
    }).format(new Date(value));
}

async function approveReservation(id) {
    isLoadingAction.value = true;
    try {
        await axios.patch(`/api/officer/reservations/${id}/approve`);
        await fetchReservations();
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menyetujui reservasi.');
    } finally {
        isLoadingAction.value = false;
    }
}

async function rejectReservation(id) {
    if (!confirm('Tolak reservasi ini?')) return;
    isLoadingAction.value = true;
    try {
        await axios.patch(`/api/officer/reservations/${id}/reject`);
        await fetchReservations();
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menolak reservasi.');
    } finally {
        isLoadingAction.value = false;
    }
}

// ==========================================
// Integrasi Modul 2: Facility Management
// ==========================================
const officerFacilities = ref([]);
const isLoadingAction = ref(false);
const isLoadingFacilities = ref(true);
const facilityError = ref('');

async function fetchOfficerFacilities() {
    isLoadingFacilities.value = true;
    facilityError.value = '';
    try {
        const response = await axios.get('/api/officer/facilities?per_page=100');
        officerFacilities.value = response.data.data || [];
    } catch (e) {
        console.error('Gagal mengambil fasilitas', e);
        facilityError.value = e.response?.data?.message || 'Data fasilitas gagal dimuat.';
    } finally {
        isLoadingFacilities.value = false;
    }
}

onMounted(() => {
    fetchReservations();
    fetchOfficerFacilities();
});

async function setMaintenance(facility) {
    if (!confirm(`Tandai ${facility.name} dalam perbaikan?`)) return;
    isLoadingAction.value = true;
    try {
        await axios.patch(`/api/officer/facilities/${facility.id}/set-maintenance`);
        alert('Berhasil diset ke maintenance.');
        await fetchOfficerFacilities();
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal mengubah status');
    } finally {
        isLoadingAction.value = false;
    }
}

async function approveFacility(facility) {
    if (!confirm(`Setujui fasilitas ${facility.name} agar dapat digunakan user?`)) return;
    isLoadingAction.value = true;
    try {
        await axios.patch(`/api/officer/facilities/${facility.id}/approve`);
        alert('Fasilitas berhasil disetujui dan sekarang aktif.');
        await fetchOfficerFacilities();
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal menyetujui fasilitas');
    } finally {
        isLoadingAction.value = false;
    }
}

async function completeMaintenance(facility) {
    if (!confirm(`Tandai perbaikan ${facility.name} sudah selesai?`)) return;
    isLoadingAction.value = true;
    try {
        await axios.patch(`/api/officer/facilities/${facility.id}/complete-maintenance`);
        alert('Berhasil dikembalikan ke aktif.');
        await fetchOfficerFacilities();
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal mengubah status');
    } finally {
        isLoadingAction.value = false;
    }
}
</script>

<template>
    <section class="content-wrap" id="screen-officer" data-screen-id="27f9c22580c44370a6c9f89785168a63">

        <div class="intro-row">
            <div>
                <p class="eyebrow">DASHBOARD PETUGAS</p>
                <h1>Antrean Approval<span class="sun">✦</span></h1>
                <p class="subheading">Tinjau dan kelola permintaan reservasi dari pengguna.</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stat-grid" style="margin: 32px 0 40px">
            <article class="stat-card">
                <div class="stat-icon yellow-bg">◷</div>
                <div>
                    <span>Menunggu Review</span>
                    <strong>{{ pendingCount.toString().padStart(2,'0') }}</strong>
                    <small>Perlu tindakan</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon blue-bg">✓</div>
                <div>
                    <span>Disetujui Hari Ini</span>
                    <strong>{{ approvedCount.toString().padStart(2,'0') }}</strong>
                    <small>Total approved</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon coral-bg">✕</div>
                <div>
                    <span>Ditolak</span>
                    <strong>{{ rejectedCount.toString().padStart(2,'0') }}</strong>
                    <small>Total ditolak</small>
                </div>
            </article>
        </div>

        <!-- Status tabs -->
        <div class="officer-tabs" role="tablist" aria-label="Status filter">
            <button
                v-for="tab in statusTabs"
                :key="tab.value"
                role="tab"
                :aria-selected="filterStatus === tab.value"
                :class="['officer-tab', { active: filterStatus === tab.value }]"
                @click="filterStatus = tab.value"
            >{{ tab.label }}</button>
        </div>

        <!-- Queue list -->
        <div v-if="isLoadingQueue" class="officer-empty-card">
            <div class="officer-empty-icon officer-spinner"></div>
            <strong>Memuat antrean reservasi</strong>
            <p>Mengambil permintaan terbaru dari sistem.</p>
        </div>
        <div v-else-if="queueError" class="officer-empty-card officer-error-card">
            <div class="officer-empty-icon">!</div>
            <strong>Antrean belum tersedia</strong>
            <p>{{ queueError }}</p>
            <button class="action-btn approve" @click="fetchReservations">Coba lagi</button>
        </div>
        <div v-else class="queue-list">
            <TransitionGroup name="queue" tag="div">
                <article
                    v-for="item in filteredQueue"
                    :key="item.id"
                    class="queue-card"
                >
                    <div :class="['facility-icon', item.color]">▦</div>
                    <div class="queue-info">
                        <strong>{{ item.facility }}</strong>
                        <span>{{ item.date }}</span>
                    </div>
                    <div class="queue-user">
                        <div class="avatar">{{ item.avatar }}</div>
                        <div>
                            <strong>{{ item.user }}</strong>
                            <small>Dikirim: {{ item.submitted }}</small>
                        </div>
                    </div>
                    <span :class="['status', item.status]">
                        {{ item.status === 'pending' ? 'Pending' : item.status === 'approved' ? 'Approved' : 'Rejected' }}
                    </span>
                    <div v-if="item.status === 'pending'" class="action-buttons">
                        <button class="action-btn approve" :id="`btn-approve-${item.id}`" @click="approveReservation(item.id)" :disabled="isLoadingAction">
                            ✓ Setujui
                        </button>
                        <button class="action-btn reject" :id="`btn-reject-${item.id}`" @click="rejectReservation(item.id)" :disabled="isLoadingAction">
                            ✕ Tolak
                        </button>
                    </div>
                    <div v-else class="action-placeholder"></div>
                </article>
            </TransitionGroup>
            <div v-if="filteredQueue.length === 0" class="officer-empty-card">
                <div class="officer-empty-icon">✓</div>
                <strong>{{ filterStatus === 'pending' ? 'Belum ada antrean approval' : 'Belum ada riwayat ' + (filterStatus === 'approved' ? 'persetujuan' : 'penolakan') }}</strong>
                <p>{{ filterStatus === 'pending' ? 'Permintaan reservasi baru akan muncul di sini setelah pengguna mengajukan reservasi.' : 'Data akan tampil setelah ada keputusan pada permintaan reservasi.' }}</p>
            </div>
        </div>
        <!-- Facility Management (Modul 2) -->
        <div class="facility-panel" style="margin-top: 40px;">
            <div class="intro-row" style="margin-bottom: 20px;">
                <div>
                    <h2>Kelola Status Fasilitas</h2>
                    <p class="subheading">Ubah status fasilitas menjadi "Dalam Perbaikan" atau sebaliknya.</p>
                </div>
            </div>
            
            <div v-if="facilityError" class="empty-state officer-error">
                {{ facilityError }}
                <button class="action-btn approve" @click="fetchOfficerFacilities">Coba lagi</button>
            </div>
            <div v-else-if="isLoadingFacilities" class="empty-state">Memuat data fasilitas...</div>
            <div v-else-if="officerFacilities.length === 0" class="officer-empty-card">
                <div class="officer-empty-icon">▦</div>
                <strong>Belum ada fasilitas untuk dipantau</strong>
                <p>Fasilitas aktif atau maintenance yang perlu ditangani akan muncul di sini.</p>
            </div>
            <div v-else class="queue-list">
                <article v-for="f in officerFacilities" :key="f.id" class="queue-card">
                    <div :class="['facility-icon', f.status === 'pending' || f.status === 'maintenance' ? 'yellow' : 'blue']">▦</div>
                    <div class="queue-info">
                        <strong>{{ f.name }}</strong>
                        <span>{{ typeof f.location === 'string' ? f.location : f.location.name }}</span>
                    </div>
                    <div class="queue-user">
                        <div>
                            <strong>Status Saat Ini:</strong>
                            <small :class="f.status === 'pending' || f.status === 'maintenance' ? 'text-warning' : 'text-success'">
                                {{ f.status === 'pending' ? 'Menunggu Approval' : (f.status === 'maintenance' ? 'Dalam Perbaikan' : 'Aktif') }}
                            </small>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button v-if="f.status === 'pending'"
                                class="action-btn approve"
                                @click="approveFacility(f)"
                                :disabled="isLoadingAction">
                            ✓ Setujui Fasilitas
                        </button>
                        <button v-if="f.status === 'active'" 
                                class="action-btn reject" 
                                @click="setMaintenance(f)" 
                                :disabled="isLoadingAction">
                            🔧 Set Perbaikan
                        </button>
                        <button v-if="f.status === 'maintenance'" 
                                class="action-btn approve" 
                                @click="completeMaintenance(f)" 
                                :disabled="isLoadingAction">
                            ✓ Selesai Diperbaiki
                        </button>
                    </div>
                </article>
            </div>
        </div>

    </section>
</template>

<style scoped>
.officer-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid var(--line);
    margin-bottom: 20px;
}
.officer-tab {
    padding: 10px 20px;
    border-radius: 6px 6px 0 0;
    background: transparent;
    color: #8a9892;
    font-size: 13px;
    font-weight: 600;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.15s;
}
.officer-tab.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: var(--primary-soft);
}
.queue-list { display: grid; gap: 10px; }
.queue-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: #fff;
    transition: box-shadow 0.15s;
}
.queue-card:hover { box-shadow: 0 4px 16px #b6c8bd22; }
.queue-info { flex: 1; min-width: 0; }
.queue-info strong { display: block; font-size: 13px; }
.queue-info span   { display: block; margin-top: 3px; font-size: 11px; color: #9aa6a1; }
.queue-user { display: flex; align-items: center; gap: 10px; width: 200px; }
.queue-user strong { display: block; font-size: 12px; }
.queue-user small  { display: block; font-size: 10px; color: #9aa6a1; margin-top: 2px; }
.action-buttons { display: flex; gap: 7px; }
.action-placeholder { width: 144px; }
.action-btn {
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    transition: all 0.15s;
}
.action-btn.approve {
    background: #dbeafe;
    color: #1d4ed8;
}
.action-btn.approve:hover { background: var(--primary); color: #fff; }
.action-btn.reject {
    background: #fee2e2;
    color: #b91c1c;
}
.action-btn.reject:hover { background: #b91c1c; color: #fff; }

/* TransitionGroup for queue items */
.queue-enter-active, .queue-leave-active { transition: all 0.25s ease; }
.queue-enter-from { opacity: 0; transform: translateX(-10px); }
.queue-leave-to   { opacity: 0; transform: translateX(10px); }
.text-warning { color: #d97706; }
.text-success { color: #16a34a; }
.officer-error { color: #b91c1c; display: flex; align-items: center; justify-content: center; gap: 12px; }
.officer-empty-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 42px 24px;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    background: rgba(248, 250, 252, .7);
    text-align: center;
}
.officer-empty-icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    margin-bottom: 13px;
    border-radius: 14px;
    color: var(--primary);
    background: var(--primary-soft);
    font-size: 20px;
    font-weight: 800;
}
.officer-empty-card strong { color: var(--ink); font-size: 14px; }
.officer-empty-card p { max-width: 390px; margin: 8px 0 0; color: var(--muted); font-size: 12px; line-height: 1.55; }
.officer-spinner { border: 3px solid #dbeafe; border-top-color: var(--primary); animation: officer-spin .8s linear infinite; }
.error-card, .officer-error-card { color: #b91c1c; }
.officer-error-card strong { color: #991b1b; }
@keyframes officer-spin { to { transform: rotate(360deg); } }
</style>
