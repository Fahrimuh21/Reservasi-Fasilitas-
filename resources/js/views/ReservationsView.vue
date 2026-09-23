<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { restoreAuthSession } from '../auth';

import {
    Clock3,
    Building2,
    CheckCircle2,
    Dumbbell,
    Palette,
    DoorOpen,
    Plus,
    Search,
    MoreHorizontal,
    ArrowRight,
    ChevronLeft,
    ChevronRight,
    Loader2,
    XCircle,
    Info
} from 'lucide-vue-next';

const router = useRouter();

// State
const reservations = ref([]);
const facilities = ref([]);
const isLoading = ref(true);
const search = ref('');
const showModal = ref(false);
const submitting = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

// Form state
const form = ref({
    facility_id: '',
    start_at: '',
    end_at: '',
    purpose: ''
});

onMounted(() => {
    restoreAuthSession();
    fetchReservations();
    fetchFacilities();
});

async function fetchReservations() {
    try {
        isLoading.value = true;
        const response = await axios.get('/api/reservations');
        reservations.value = response.data.data;
    } catch (error) {
        console.error('Error fetching reservations:', error);
    } finally {
        isLoading.value = false;
    }
}

async function fetchFacilities() {
    try {
        // Fetch facilities for the dropdown
        const response = await axios.get('/api/facilities?per_page=100');
        // If it's paginated, access data.data or data depending on API structure
        facilities.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching facilities:', error);
    }
}

const filteredReservations = computed(() => {
    if (!search.value) return reservations.value;
    return reservations.value.filter(item =>
        item.facility?.name.toLowerCase().includes(search.value.toLowerCase()) ||
        item.purpose.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Stats
const upcomingCount = computed(() => {
    return reservations.value.filter(r => r.status === 'pending' || r.status === 'approved').length;
});
const totalHours = computed(() => {
    let hours = 0;
    reservations.value.forEach(r => {
        if (r.status === 'approved') {
            const start = new Date(r.start_at);
            const end = new Date(r.end_at);
            hours += (end - start) / (1000 * 60 * 60);
        }
    });
    return hours.toFixed(1);
});

async function addReservation() {
    errorMsg.value = '';
    submitting.value = true;
    try {
        await axios.post('/api/reservations', form.value);
        successMsg.value = 'Reservasi berhasil dibuat!';
        showModal.value = false;
        form.value = { facility_id: '', start_at: '', end_at: '', purpose: '' };
        await fetchReservations();
        setTimeout(() => successMsg.value = '', 3000);
    } catch (error) {
        if (error.response?.data?.errors) {
            const errs = error.response.data.errors;
            errorMsg.value = Object.values(errs).flat().join(' ');
        } else {
            errorMsg.value = error.response?.data?.message || 'Gagal membuat reservasi.';
        }
    } finally {
        submitting.value = false;
    }
}

async function cancelReservation(reservationId) {
    if (!confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')) return;
    
    try {
        await axios.post(`/api/reservations/${reservationId}/cancel`, {
            reason: 'Dibatalkan oleh pengguna.'
        });
        successMsg.value = 'Reservasi berhasil dibatalkan.';
        await fetchReservations();
        setTimeout(() => successMsg.value = '', 3000);
    } catch (error) {
        alert('Gagal membatalkan reservasi: ' + (error.response?.data?.message || 'Error'));
    }
}

function goToFacilities() {
    router.push({ name: 'facilities' });
}

function formatDate(dateString) {
    const d = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', { 
        weekday: 'short', day: 'numeric', month: 'short', 
        hour: '2-digit', minute: '2-digit'
    }).format(d);
}

function getStatusClass(status) {
    switch (status) {
        case 'approved': return 'bg-success text-white';
        case 'pending': return 'bg-warning text-dark';
        case 'cancelled': return 'bg-danger text-white';
        case 'rejected': return 'bg-dark text-white';
        default: return 'bg-secondary text-white';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'approved': return 'Disetujui';
        case 'pending': return 'Menunggu';
        case 'cancelled': return 'Dibatalkan';
        case 'rejected': return 'Ditolak';
        default: return status;
    }
}
</script>

<template>
<section class="reservation-page content-wrap" id="screen-reservations">

    <!-- HEADER -->
    <div class="reservation-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted text-uppercase small fw-bold mb-1">
                {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
            </p>
            <h1 class="fw-bolder mb-1">Reservasi Saya</h1>
            <p class="text-secondary">Kelola dan pantau seluruh reservasi fasilitas Anda.</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 fw-semibold shadow-sm rounded-pill" @click="showModal = true">
            <Plus size="18"/> Reservasi Baru
        </button>
    </div>

    <!-- ALERTS -->
    <div v-if="successMsg" class="alert alert-success shadow-sm rounded-3 d-flex align-items-center gap-2 border-0" role="alert">
        <CheckCircle2 size="20" />
        {{ successMsg }}
    </div>

    <!-- STATISTICS -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <Clock3 size="28" />
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Mendatang</span>
                        <h2 class="fw-bold mb-0">{{ upcomingCount }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <CheckCircle2 size="28" />
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Jam Disetujui</span>
                        <h2 class="fw-bold mb-0">{{ totalHours }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-primary text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
                <div class="card-body p-4 position-relative z-1">
                    <h5 class="fw-bold mb-2">Rencanakan Kegiatanmu</h5>
                    <p class="small mb-3 text-white-50">Jelajahi fasilitas yang tersedia di kampus untuk kegiatan akademik maupun non-akademik.</p>
                    <button class="btn btn-light btn-sm fw-semibold rounded-pill px-3" @click="goToFacilities">
                        Jelajahi <ArrowRight size="14" class="ms-1"/>
                    </button>
                </div>
                <div class="position-absolute opacity-25" style="right: -20px; bottom: -20px;">
                    <Building2 size="120" />
                </div>
            </div>
        </div>
    </div>

    <!-- LIST HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Reservasi</h3>
            <p class="text-muted small">Daftar semua pengajuan reservasi Anda.</p>
        </div>
        <div class="position-relative" style="width: 300px;">
            <Search size="18" class="position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"/>
            <input v-model="search" type="text" class="form-control rounded-pill ps-5 bg-light border-0 shadow-sm" placeholder="Cari fasilitas atau tujuan...">
        </div>
    </div>

    <!-- RESERVATION LIST -->
    <div v-if="isLoading" class="text-center py-5">
        <Loader2 size="48" class="text-primary spin-animation" style="animation: spin 1s linear infinite;" />
        <p class="text-muted mt-3 fw-medium">Memuat data reservasi...</p>
    </div>
    
    <div v-else-if="filteredReservations.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm border-0">
        <Info size="48" class="text-muted mb-3 opacity-50" />
        <h5 class="fw-bold text-dark mb-1">Belum Ada Reservasi</h5>
        <p class="text-secondary mb-4">Anda belum memiliki riwayat reservasi yang cocok dengan pencarian.</p>
        <button class="btn btn-outline-primary rounded-pill px-4" @click="showModal = true">Buat Reservasi Sekarang</button>
    </div>

    <div v-else class="row g-3">
        <div v-for="res in filteredReservations" :key="res.id" class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 transition-hover">
                <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center gap-4">
                    
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="bg-light rounded-circle p-3 text-secondary d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                            <DoorOpen size="28" />
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">{{ res.facility?.name || 'Fasilitas Terhapus' }}</h5>
                            <p class="text-muted small mb-0">{{ res.facility?.facility_type?.name || 'Tipe Tidak Diketahui' }} &bull; Tujuan: {{ res.purpose }}</p>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-column" style="min-width: 200px;">
                        <span class="text-muted small fw-semibold text-uppercase mb-1">Waktu</span>
                        <strong class="text-dark">{{ formatDate(res.start_at) }}</strong>
                        <span class="text-muted small">s/d {{ formatDate(res.end_at) }}</span>
                    </div>

                    <div class="d-flex flex-column align-items-md-end justify-content-center gap-2" style="min-width: 150px;">
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" :class="getStatusClass(res.status)">
                            {{ getStatusLabel(res.status) }}
                        </span>
                        
                        <button v-if="res.status === 'pending' || res.status === 'approved'" 
                                @click="cancelReservation(res.id)" 
                                class="btn btn-sm btn-link text-danger text-decoration-none p-0 d-flex align-items-center gap-1 mt-1 small">
                            <XCircle size="14" /> Batalkan
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL RESERVASI BARU -->
    <Teleport to="body">
        <div v-if="showModal" class="modal-backdrop-custom d-flex justify-content-center align-items-center" @click.self="showModal = false">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="width: 100%; max-width: 500px; animation: modalIn 0.3s ease;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">Reservasi Baru</h4>
                    <button class="btn-close" @click="showModal = false"></button>
                </div>
                <div class="card-body p-4">
                    
                    <div v-if="errorMsg" class="alert alert-danger small py-2 rounded-3 mb-3">
                        {{ errorMsg }}
                    </div>

                    <form @submit.prevent="addReservation">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Fasilitas</label>
                            <select v-model="form.facility_id" class="form-select bg-light border-0 rounded-3 shadow-sm py-2" required>
                                <option value="" disabled>Pilih Fasilitas...</option>
                                <option v-for="fac in facilities" :key="fac.id" :value="fac.id">
                                    {{ fac.name }} (Kapasitas: {{ fac.capacity }})
                                </option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">Waktu Mulai</label>
                                <input v-model="form.start_at" type="datetime-local" class="form-control bg-light border-0 rounded-3 shadow-sm py-2" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">Waktu Selesai</label>
                                <input v-model="form.end_at" type="datetime-local" class="form-control bg-light border-0 rounded-3 shadow-sm py-2" required>
                            </div>
                            <div class="col-12 mt-2">
                                <small class="text-secondary d-flex align-items-center gap-1">
                                    <Info size="14"/> Operasional: 07:00 - 20:00. Interval: 30 menit.
                                </small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted">Tujuan Penggunaan</label>
                            <textarea v-model="form.purpose" class="form-control bg-light border-0 rounded-3 shadow-sm" rows="3" placeholder="Jelaskan untuk kegiatan apa..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" :disabled="submitting">
                            <Loader2 v-if="submitting" size="18" style="animation: spin 1s linear infinite;" />
                            {{ submitting ? 'Memproses...' : 'Ajukan Reservasi' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </Teleport>

</section>
</template>

<style scoped>
/* Local layout utilities for this view. The app does not load Bootstrap. */
.reservation-page {
    padding: 34px 50px 70px;
}
.reservation-header {
    margin-bottom: 28px;
}
.reservation-header h1 {
    font-size: clamp(2rem, 4vw, 3rem);
    letter-spacing: -1.5px;
}
.d-flex { display: flex; }
.flex-column { flex-direction: column; }
.flex-grow-1 { flex: 1 1 auto; }
.align-items-center { align-items: center; }
.align-items-md-center { align-items: center; }
.align-items-md-end { align-items: flex-end; }
.justify-content-between { justify-content: space-between; }
.justify-content-center { justify-content: center; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}
.col-12 { min-width: 0; }
.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
}
.card-body { padding: 24px; }
.card-header { padding: 20px 24px 0; }
.h-100 { height: 100%; }
.overflow-hidden { overflow: hidden; }
.position-relative { position: relative; }
.position-absolute { position: absolute; }
.text-center { text-align: center; }
.text-end { text-align: right; }
.text-white { color: #fff; }
.text-dark { color: #0f172a; }
.text-muted, .text-secondary { color: #64748b; }
.small { font-size: 12px; }
.fw-bold, .fw-bolder { font-weight: 700; }
.fw-semibold { font-weight: 600; }
.mb-0 { margin-bottom: 0; }
.mb-1 { margin-bottom: 4px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 12px; }
.mb-4 { margin-bottom: 24px; }
.mb-5 { margin-bottom: 32px; }
.mt-1 { margin-top: 4px; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
.ms-1 { margin-left: 4px; }
.p-0 { padding: 0; }
.p-4 { padding: 24px; }
.pt-4 { padding-top: 24px; }
.pb-0 { padding-bottom: 0; }
.px-3 { padding-left: 12px; padding-right: 12px; }
.px-4 { padding-left: 24px; padding-right: 24px; }
.py-2 { padding-top: 8px; padding-bottom: 8px; }
.py-5 { padding-top: 48px; padding-bottom: 48px; }
.w-100 { width: 100%; }
.rounded-3 { border-radius: 12px; }
.rounded-4 { border-radius: 16px; }
.rounded-pill { border-radius: 999px; }
.border-0 { border: 0; }
.shadow-sm { box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    border: 1px solid transparent;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 600;
}
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-outline-primary { background: transparent; border-color: #2563eb; color: #2563eb; }
.btn-light { background: #fff; color: #1d4ed8; }
.btn-link { background: transparent; border: 0; }
.form-control, .form-select {
    width: 100%;
    min-height: 42px;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    color: #0f172a;
}
.form-control:focus, .form-select:focus {
    outline: 2px solid rgba(37, 99, 235, .2);
    border-color: #2563eb;
}
textarea.form-control { resize: vertical; }
.bg-white { background: #fff; }
.bg-light { background: #f8fafc; }
.bg-primary { background: #2563eb; }
.bg-success { background: #16a34a; }
.bg-warning { background: #f59e0b; }
.bg-danger { background: #dc2626; }
.text-primary { color: #2563eb; }
.text-success { color: #16a34a; }
.text-danger { color: #dc2626; }
.text-dark { color: #0f172a; }
.bg-opacity-10 { background: rgba(37, 99, 235, .1); }
.alert { padding: 12px 16px; }
.alert-success { background: #ecfdf5; color: #166534; }
.alert-danger { background: #fef2f2; color: #991b1b; }
.badge { display: inline-block; }
.modal-backdrop-custom .card { width: min(100% - 32px, 500px); }

.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
}
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    z-index: 1050;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

@media (max-width: 900px) {
    .reservation-page { padding: 28px 24px 56px; }
    .row { grid-template-columns: 1fr; }
    .reservation-header { align-items: flex-start; gap: 18px; }
}

@media (max-width: 640px) {
    .reservation-page { padding: 24px 16px 44px; }
    .reservation-header { flex-direction: column; }
    .reservation-header > button { width: 100%; }
    .reservation-page > .d-flex.justify-content-between { flex-direction: column; align-items: stretch; gap: 14px; }
    .reservation-page > .d-flex.justify-content-between > .position-relative { width: 100% !important; }
    .card-body { padding: 18px; }
}
</style>