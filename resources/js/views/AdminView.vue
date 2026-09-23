<script setup>
/**
 * AdminView — Screen 5
 * Screen ID: 9400ab25f7044638a44066516e2c8bfd
 * Dashboard Admin & Rekapitulasi Sarana
 */
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const selectedPeriod = ref('month');
const periods = [
    { value: 'week',  label: '7 Hari' },
    { value: 'month', label: '30 Hari' },
    { value: 'year',  label: '1 Tahun' },
];

const dashboardLoading = ref(true);
const dashboardError = ref('');
const dashboardStats = ref({ reservations: 0, usage_rate: 0, reports: 0, active_users: 0 });

const kpis = computed(() => [
    { label: 'Total Reservasi', value: dashboardStats.value.reservations, color: 'coral', icon: '◷' },
    { label: 'Tingkat Pemakaian', value: `${dashboardStats.value.usage_rate}%`, color: 'blue', icon: '▦' },
    { label: 'Laporan Kerusakan', value: dashboardStats.value.reports, color: 'yellow', icon: '⚠' },
    { label: 'Pengguna Aktif', value: dashboardStats.value.active_users, color: 'green', icon: '✓' },
]);

// Facility utilization
const facilities = ref([]);

const sortedFacilities = computed(() =>
    [...facilities.value].sort((a, b) => b.usage - a.usage)
);

const activities = ref([]);

function usageBarColor(usage) {
    if (usage >= 80) return '#f27963';
    if (usage >= 60) return '#4a9a76';
    return '#6aafd4';
}

async function fetchDashboardSummary() {
    dashboardLoading.value = true;
    dashboardError.value = '';
    try {
        const response = await axios.get(`/api/admin/facilities/summary?period=${selectedPeriod.value}`);
        dashboardStats.value = response.data.kpis;
        facilities.value = response.data.facilities || [];
    } catch (error) {
        dashboardError.value = error.response?.data?.message || 'Ringkasan dashboard gagal dimuat.';
    } finally {
        dashboardLoading.value = false;
    }
}

async function changePeriod(period) {
    selectedPeriod.value = period;
    await fetchDashboardSummary();
}

// ==========================================
// Integrasi Modul 2: Facility CRUD (Admin)
// ==========================================
const showModal = ref(false);
const isEditMode = ref(false);
const isSubmitting = ref(false);
const facilityTypes = ref([]);
const locations = ref([]);
const adminFacilities = ref([]);
const isFacilitiesLoading = ref(false);
const facilityError = ref('');

const facilityForm = ref({
    id: null, code: '', name: '', facility_type_id: '', location_id: '', capacity: 10, description: '', status: 'active'
});

async function fetchAdminFacilities() {
    isFacilitiesLoading.value = true;
    facilityError.value = '';
    try {
        const response = await axios.get('/api/admin/facilities?per_page=100');
        adminFacilities.value = response.data.data || [];
    } catch (e) {
        console.error('Gagal mengambil data fasilitas', e);
        facilityError.value = e.response?.data?.message || 'Data fasilitas gagal dimuat.';
    } finally {
        isFacilitiesLoading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([fetchAdminFacilities(), fetchDashboardSummary()]);
    try {
        const [resTypes, resLocs] = await Promise.all([
            axios.get('/api/facility-types'),
            axios.get('/api/locations')
        ]);
        facilityTypes.value = resTypes.data.data;
        locations.value = resLocs.data.data;
    } catch (e) {
        console.error("Gagal mengambil data dropdown", e);
    }
});

function openAddModal() {
    isEditMode.value = false;
    facilityForm.value = { id: null, code: '', name: '', facility_type_id: '', location_id: '', capacity: 10, description: '', status: 'pending' };
    showModal.value = true;
}

function openEditModal(f) {
    isEditMode.value = true;
    facilityForm.value = {
        id: f.id, code: f.code, name: f.name, 
        facility_type_id: f.type?.id || f.facility_type_id || '',
        location_id: f.location?.id || f.location_id || '',
        capacity: f.capacity, description: f.description || '', 
        status: f.status
    };
    showModal.value = true;
}

async function submitFacility() {
    isSubmitting.value = true;
    try {
        if (isEditMode.value) {
            const response = await axios.put(`/api/admin/facilities/${facilityForm.value.id}`, facilityForm.value);
            alert(response.data.message);
            activities.value.unshift({ type: 'new', text: `Fasilitas "${facilityForm.value.name}" diperbarui`, time: 'Baru saja', color: 'blue' });
        } else {
            const response = await axios.post('/api/admin/facilities', facilityForm.value);
            alert(response.data.message);
            activities.value.unshift({ type: 'new', text: `Fasilitas baru "${facilityForm.value.name}" ditambahkan`, time: 'Baru saja', color: 'yellow' });
        }
        
        showModal.value = false;
        await Promise.all([fetchAdminFacilities(), fetchDashboardSummary()]);
    } catch (e) {
        if (e.response && e.response.data.errors) {
            alert("Validasi Error: " + Object.values(e.response.data.errors).flat().join('\n'));
        } else {
            alert("Terjadi kesalahan saat menyimpan fasilitas.");
        }
    } finally {
        isSubmitting.value = false;
    }
}

async function toggleStatus(f) {
    if (!confirm(`Ubah status fasilitas ${f.name}?`)) return;
    try {
        await axios.patch(`/api/admin/facilities/${f.id}/toggle-status`);
        await Promise.all([fetchAdminFacilities(), fetchDashboardSummary()]);
    } catch (e) {
        alert(e.response?.data?.message || 'Gagal mengubah status');
    }
}
</script>

<template>
    <section class="content-wrap" id="screen-admin" data-screen-id="9400ab25f7044638a44066516e2c8bfd">

        <div class="intro-row">
            <div>
                <p class="eyebrow">DASHBOARD ADMIN</p>
                <h1>Rekapitulasi Sarana<span class="sun">✦</span></h1>
                <p class="subheading">Overview penggunaan seluruh fasilitas kampus secara real-time.</p>
            </div>
            <!-- Period selector -->
            <div class="period-selector" role="group" aria-label="Pilih periode">
                <button
                    v-for="p in periods"
                    :key="p.value"
                    :class="['period-btn', { active: selectedPeriod === p.value }]"
                    @click="changePeriod(p.value)"
                >{{ p.label }}</button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="stat-grid" style="margin: 36px 0 48px">
            <article v-for="kpi in kpis" :key="kpi.label" class="stat-card">
                <div :class="['stat-icon', kpi.color + '-bg']">{{ kpi.icon }}</div>
                <div>
                    <span>{{ kpi.label }}</span>
                    <strong>{{ kpi.value }}</strong>
                    <small>Data aktual dari sistem</small>
                </div>
            </article>
        </div>

        <div class="admin-lower-grid">

            <!-- Facility utilization table -->
            <div class="utilization-panel">
                <div class="panel-heading" style="margin-bottom: 20px">
                    <div>
                        <h2>Utilisasi Fasilitas</h2>
                        <p>Diurutkan berdasarkan tingkat pemakaian.</p>
                    </div>
                </div>
                <div v-if="dashboardLoading" class="dashboard-empty-state">
                    <span class="dashboard-empty-icon dashboard-spinner"></span>
                    <strong>Memuat ringkasan</strong>
                    <p>Menyiapkan data terbaru dari sistem.</p>
                </div>
                <div v-else-if="dashboardError" class="dashboard-empty-state dashboard-empty-error">
                    <span class="dashboard-empty-icon">!</span>
                    <strong>Ringkasan belum tersedia</strong>
                    <p>{{ dashboardError }}</p>
                    <button class="dashboard-empty-action" @click="fetchDashboardSummary">Coba lagi</button>
                </div>
                <div v-else-if="sortedFacilities.length === 0" class="dashboard-empty-state">
                    <span class="dashboard-empty-icon">▦</span>
                    <strong>Belum ada data utilisasi</strong>
                    <p>Tambahkan fasilitas dan terima reservasi untuk melihat statistik pemakaian.</p>
                    <button class="dashboard-empty-action" @click="openAddModal">＋ Tambah Fasilitas</button>
                </div>
                <div v-else class="util-list">
                    <div
                        v-for="(f, i) in sortedFacilities"
                        :key="f.name"
                        class="util-row"
                    >
                        <span class="util-rank">#{{ i + 1 }}</span>
                        <div :class="['facility-icon', f.color]" style="width:32px;height:32px;font-size:14px">▦</div>
                        <div class="util-info">
                            <strong>{{ f.name }}</strong>
                            <span>{{ f.type }} · {{ f.reservations }} reservasi</span>
                        </div>
                        <div class="util-bar-wrap">
                            <div class="util-bar-track">
                                <div
                                    class="util-bar-fill"
                                    :style="{ width: f.usage + '%', background: usageBarColor(f.usage) }"
                                ></div>
                            </div>
                            <span class="util-pct">{{ f.usage }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent activity log -->
            <div class="activity-panel">
                <div class="panel-heading" style="margin-bottom: 16px">
                    <div>
                        <h2>Aktivitas Terkini</h2>
                        <p>Log aksi admin real-time.</p>
                    </div>
                </div>
                <div v-if="activities.length" class="activity-list">
                    <div v-for="act in activities" :key="act.text" class="activity-item">
                        <div :class="['act-dot', act.color]"></div>
                        <div class="activity-info">
                            <span>{{ act.text }}</span>
                            <small>{{ act.time }}</small>
                        </div>
                    </div>
                </div>
                <div v-else class="dashboard-empty-state dashboard-empty-compact">
                    <span class="dashboard-empty-icon">✓</span>
                    <strong>Belum ada aktivitas</strong>
                    <p>Aktivitas admin akan muncul di sini.</p>
                </div>

                <!-- Quick actions -->
                <div style="margin-top: 24px; border-top: 1px solid var(--line); padding-top: 20px">
                    <p class="eyebrow" style="margin-bottom: 12px">AKSI CEPAT</p>
                    <div class="quick-actions">
                        <button class="quick-btn" id="btn-export-report">⬇ Export PDF</button>
                        <button class="quick-btn" id="btn-add-facility" @click="openAddModal">＋ Tambah Fasilitas</button>
                        <button class="quick-btn" id="btn-manage-users">👤 Kelola User</button>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Manajemen Fasilitas (Modul 2) -->
        <section class="facility-management-panel">
            <div class="facility-management-header">
                <div>
                    <p class="eyebrow">DATA MASTER</p>
                    <h2>Manajemen Fasilitas</h2>
                    <p>Tambah, edit, dan atur status fasilitas kampus.</p>
                </div>
                <button class="facility-primary-btn" @click="openAddModal">＋ Tambah Fasilitas</button>
            </div>

            <div v-if="facilityError" class="facility-alert facility-alert-error">
                {{ facilityError }}
                <button type="button" @click="fetchAdminFacilities">Coba lagi</button>
            </div>

            <div v-if="isFacilitiesLoading" class="facility-empty-state">
                <span class="facility-spinner"></span>
                <p>Memuat data fasilitas...</p>
            </div>

            <div v-else-if="adminFacilities.length === 0" class="facility-empty-state">
                <div class="facility-empty-icon">▦</div>
                <h3>Belum ada fasilitas</h3>
                <p>Tambahkan fasilitas pertama untuk mulai mengelola katalog.</p>
                <button class="facility-outline-btn" @click="openAddModal">Tambah Fasilitas</button>
            </div>

            <div v-else class="facility-table-wrap">
                <div class="facility-table-head">
                    <span>Fasilitas</span>
                    <span>Detail</span>
                    <span>Status</span>
                    <span>Aksi</span>
                </div>
                <div v-for="f in adminFacilities" :key="f.id" class="facility-table-row">
                    <div class="facility-table-name">
                        <div :class="['facility-icon', f.status === 'active' ? 'blue' : (f.status === 'inactive' ? 'coral' : 'yellow')]"><span>▦</span></div>
                        <div>
                            <strong>{{ f.name }}</strong>
                            <small>{{ f.code }}</small>
                        </div>
                    </div>
                    <div class="facility-table-detail">
                        <strong>{{ typeof f.type === 'string' ? f.type : f.type?.name }}</strong>
                        <small>{{ typeof f.location === 'string' ? f.location : (f.location?.building || f.location?.name) }} · {{ f.capacity }} orang</small>
                    </div>
                    <span :class="['facility-status', `facility-status-${f.status}`]">
                        {{ f.status === 'active' ? 'Aktif' : (f.status === 'inactive' ? 'Nonaktif' : (f.status === 'pending' ? 'Menunggu Approval' : 'Maintenance')) }}
                    </span>
                    <div class="facility-actions">
                        <button class="facility-action-btn" @click="openEditModal(f)">Edit</button>
                        <button v-if="f.status === 'active' || f.status === 'inactive'" class="facility-action-btn facility-action-secondary" @click="toggleStatus(f)">
                            {{ f.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <span v-else class="facility-maintenance-note">Diatur petugas</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tambah/Edit Fasilitas Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
            <div class="modal-content">
                <h2>{{ isEditMode ? 'Edit Fasilitas' : 'Tambah Fasilitas Baru' }}</h2>
                <form @submit.prevent="submitFacility">
                    <div class="form-group">
                        <label>Kode Fasilitas</label>
                        <input v-model="facilityForm.code" required placeholder="Contoh: LAB-01">
                    </div>
                    <div class="form-group">
                        <label>Nama Fasilitas</label>
                        <input v-model="facilityForm.name" required placeholder="Contoh: Lab Komputer">
                    </div>
                    <div class="form-group">
                        <label>Tipe</label>
                        <select v-model="facilityForm.facility_type_id" required>
                            <option value="" disabled>Pilih Tipe</option>
                            <option v-for="t in facilityTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lokasi</label>
                        <select v-model="facilityForm.location_id" required>
                            <option value="" disabled>Pilih Lokasi</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.building || l.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas (Orang)</label>
                        <input type="number" v-model="facilityForm.capacity" min="1" required>
                    </div>
                    
                    <div v-if="!isEditMode" class="facility-pending-notice">
                        <strong>Menunggu persetujuan petugas</strong>
                        <span>Fasilitas baru akan masuk katalog setelah disetujui oleh Petugas.</span>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea v-model="facilityForm.description" rows="2" placeholder="Fasilitas AC, Proyektor..."></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="showModal = false" :disabled="isSubmitting">Batal</button>
                        <button type="submit" class="btn-submit" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </section>
</template>

<style scoped>
/* Facility CRUD */
.facility-management-panel {
    margin-top: 40px;
    padding: 28px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
}
.facility-management-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 24px;
}
.facility-management-header h2 { margin: 0 0 6px; font-size: 22px; }
.facility-management-header p:not(.eyebrow) { margin: 0; color: var(--muted); font-size: 13px; }
.facility-primary-btn, .facility-outline-btn, .facility-action-btn {
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .15s ease;
}
.facility-primary-btn {
    padding: 11px 16px;
    background: var(--primary);
    color: #fff;
}
.facility-primary-btn:hover { background: var(--primary-hover); }
.facility-outline-btn {
    padding: 9px 14px;
    color: var(--primary);
    background: #fff;
    border: 1px solid #bfdbfe;
}
.facility-alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    margin-bottom: 16px;
    border-radius: 10px;
    font-size: 12px;
}
.facility-alert-error { color: #991b1b; background: #fef2f2; }
.facility-alert button { color: #991b1b; background: transparent; text-decoration: underline; font-size: 12px; }
.facility-table-wrap { overflow-x: auto; }
.facility-table-head, .facility-table-row {
    display: grid;
    grid-template-columns: minmax(220px, 1.4fr) minmax(180px, 1fr) 110px minmax(180px, .9fr);
    gap: 16px;
    align-items: center;
    min-width: 760px;
}
.facility-table-head {
    padding: 0 12px 10px;
    color: #94a3b8;
    border-bottom: 1px solid var(--line);
    font: 10px 'DM Mono', monospace;
    letter-spacing: 1px;
    text-transform: uppercase;
}
.facility-table-row {
    padding: 16px 12px;
    border-bottom: 1px solid #eef2f7;
}
.facility-table-row:last-child { border-bottom: 0; }
.facility-table-name { display: flex; align-items: center; gap: 11px; }
.facility-table-name strong, .facility-table-detail strong { display: block; font-size: 13px; }
.facility-table-name small, .facility-table-detail small { display: block; margin-top: 4px; color: var(--muted); font-size: 11px; }
.facility-table-detail { min-width: 0; }
.facility-icon {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 10px;
    font-size: 16px;
}
.facility-icon.blue { color: #2563eb; background: #eff6ff; }
.facility-icon.coral { color: #dc2626; background: #fef2f2; }
.facility-icon.yellow { color: #a16207; background: #fef9c3; }
.facility-status {
    width: fit-content;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
}
.facility-status-active { color: #166534; background: #dcfce7; }
.facility-status-inactive { color: #991b1b; background: #fee2e2; }
.facility-status-pending { color: #854d0e; background: #fef3c7; }
.facility-status-maintenance { color: #854d0e; background: #fef3c7; }
.facility-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; }
.facility-action-btn { padding: 7px 10px; color: var(--primary); background: #eff6ff; }
.facility-action-btn:hover { background: #dbeafe; }
.facility-action-secondary { color: #475569; background: #f1f5f9; }
.facility-maintenance-note { color: #a16207; font-size: 10px; }
.facility-pending-notice {
    display: grid;
    gap: 4px;
    margin-bottom: 12px;
    padding: 10px 12px;
    border: 1px solid #fde68a;
    border-radius: 9px;
    color: #854d0e;
    background: #fffbeb;
    font-size: 11px;
}
.facility-pending-notice span { color: #a16207; }
.facility-empty-state { padding: 44px 20px; text-align: center; color: var(--muted); }
.facility-empty-state h3 { margin: 12px 0 6px; color: var(--ink); font-size: 16px; }
.facility-empty-state p { margin: 0 0 18px; font-size: 12px; }
.facility-empty-icon { margin: 0 auto; width: 48px; height: 48px; display: grid; place-items: center; border-radius: 14px; color: var(--primary); background: var(--primary-soft); font-size: 22px; }
.facility-spinner { display: inline-block; width: 24px; height: 24px; border: 3px solid #dbeafe; border-top-color: var(--primary); border-radius: 50%; animation: facility-spin .8s linear infinite; }
@keyframes facility-spin { to { transform: rotate(360deg); } }

/* Period selector */
.period-selector {
    display: flex;
    gap: 4px;
    background: var(--line);
    border-radius: 8px;
    padding: 3px;
}
.period-btn {
    padding: 7px 16px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: #8a9892;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.15s;
}
.period-btn.active { background: #fff; color: var(--ink); box-shadow: 0 2px 6px #b6c8bd33; }

/* Admin lower grid */
.admin-lower-grid {
    display: grid;
    grid-template-columns: 1.4fr 0.6fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) { .admin-lower-grid { grid-template-columns: 1fr; } }

.utilization-panel, .activity-panel {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 24px;
}

/* Util rows */
.util-list { display: grid; gap: 14px; }
.util-row { display: flex; align-items: center; gap: 12px; }
.util-rank { width: 24px; font: 700 12px 'DM Mono', monospace; color: #c3ccc7; text-align: right; }
.util-info { flex: 1; }
.util-info strong { display: block; font-size: 12px; }
.util-info span   { display: block; font-size: 10px; color: #9aa6a1; margin-top: 2px; }
.util-bar-wrap { display: flex; align-items: center; gap: 8px; width: 160px; }
.util-bar-track {
    flex: 1;
    height: 8px;
    background: var(--line);
    border-radius: 4px;
    overflow: hidden;
}
.util-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}
.util-pct { font: 700 11px 'DM Mono', monospace; color: var(--ink); width: 36px; text-align: right; }

/* Activity log */
.activity-list { display: grid; gap: 14px; }
.activity-item { display: flex; gap: 12px; align-items: flex-start; }
.act-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 3px;
}
.act-dot.green  { background: #4a9a76; }
.act-dot.coral  { background: var(--coral); }
.act-dot.blue   { background: #6aafd4; }
.act-dot.yellow { background: #c9a32e; }
.activity-info span { display: block; font-size: 12px; }
.activity-info small { display: block; font-size: 10px; color: #9aa6a1; margin-top: 2px; }
.dashboard-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 34px 20px;
    color: var(--muted);
    text-align: center;
    font-size: 12px;
}
.dashboard-empty-state strong { color: var(--ink); font-size: 13px; }
.dashboard-empty-state p { max-width: 300px; margin: 7px 0 16px; line-height: 1.5; }
.dashboard-empty-error { color: #b91c1c; }
.dashboard-empty-error strong { color: #991b1b; }
.dashboard-empty-icon {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    margin-bottom: 12px;
    border-radius: 12px;
    color: var(--primary);
    background: var(--primary-soft);
    font-size: 19px;
    font-weight: 800;
}
.dashboard-empty-error .dashboard-empty-icon { color: #b91c1c; background: #fef2f2; }
.dashboard-empty-action {
    padding: 8px 12px;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    color: var(--primary);
    background: #fff;
    font-size: 11px;
    font-weight: 700;
}
.dashboard-empty-action:hover { background: var(--primary-soft); }
.dashboard-empty-compact { min-height: 140px; padding: 20px 12px; }
.dashboard-spinner { border: 3px solid #dbeafe; border-top-color: var(--primary); animation: dashboard-spin .8s linear infinite; }
@keyframes dashboard-spin { to { transform: rotate(360deg); } }

/* Quick action buttons */
.quick-actions { display: grid; gap: 8px; }
.quick-btn {
    width: 100%;
    padding: 10px 14px;
    text-align: left;
    border: 1px solid var(--line);
    border-radius: 7px;
    background: #fafcfb;
    font-size: 12px;
    color: var(--ink);
    transition: all 0.15s;
}
.quick-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

/* KPI deltas */
.delta-up   { color: #4a9a76 !important; }
.delta-down { color: var(--coral) !important; }

/* Modal styles */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    z-index: 100;
}
.modal-content {
    background: #fff; border-radius: 12px; padding: 24px;
    width: 400px; max-width: 90%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.modal-content h2 { margin-bottom: 16px; font-size: 18px; }
.form-group { margin-bottom: 12px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
.form-group input, .form-group select, .form-group textarea {
    width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 6px; font-size: 13px;
}
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.btn-cancel { padding: 8px 16px; background: #f1f5f9; border: none; border-radius: 6px; cursor: pointer; }
.btn-submit { padding: 8px 16px; background: var(--primary); color: #fff; border: none; border-radius: 6px; cursor: pointer; }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

@media (max-width: 700px) {
    .facility-management-panel { padding: 20px 16px; }
    .facility-management-header { flex-direction: column; }
    .facility-primary-btn { width: 100%; }
}
</style>
