<script setup>
/**
 * FacilitiesView — Screen 1
 * Screen ID: 9b4eaf60a859485ea4a2057a9eba4306
 * Katalog & Grid Ketersediaan Fasilitas 30 Menit
 */
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const searchQuery  = ref('');
const selectedType = ref('all');
const currentTime  = ref(new Date());

const facilities = ref([]);
const isLoading = ref(true);
const loadError = ref('');

// 26 slots dari jam 07:00 sampai 19:30 sesuai backend API
const timeSlots = ref([]);
for (let h = 7; h < 20; h++) {
    timeSlots.value.push(`${String(h).padStart(2, '0')}:00`);
    timeSlots.value.push(`${String(h).padStart(2, '0')}:30`);
}

async function loadFacilities() {
    isLoading.value = true;
    loadError.value = '';
    try {
        const response = await axios.get('/api/facilities?per_page=100');
        facilities.value = (response.data.data || []).map(f => {
            
            const slotsObj = {};
            if (f.availability_today) {
                f.availability_today.forEach(s => {
                    slotsObj[s.start] = s.status === 'tersedia' ? 'available' : 'booked';
                });
            }

            // Map tipe dari backend ke filter UI
            let uiType = 'creative';
            const typeName = (typeof f.type === 'string' ? f.type : f.type?.name || '').toLowerCase();
            if (typeName.includes('lab')) uiType = 'lab';
            if (typeName.includes('kelas') || typeName.includes('rapat')) uiType = 'meeting';
            if (typeName.includes('aula')) uiType = 'creative';

            // Extract string for location (Tampilkan nama gedung saja)
            const locName = typeof f.location === 'string'
                ? f.location
                : (f.location?.building || f.location?.name || 'Lokasi belum ditentukan');

            return {
                id: f.id,
                name: f.name,
                type: uiType,
                location: locName,
                capacity: f.capacity,
                color: uiType === 'lab' ? 'blue' : (uiType === 'meeting' ? 'green' : 'coral'),
                icon: '▦',
                slots: slotsObj
            };
        });
    } catch (e) {
        console.error('Error fetching facilities:', e);
        loadError.value = e.response?.data?.message || 'Katalog fasilitas gagal dimuat.';
    } finally {
        isLoading.value = false;
    }
}

onMounted(loadFacilities);

const typeOptions = [
    { value: 'all', label: 'Semua Fasilitas' },
    { value: 'meeting', label: 'Ruang Kelas / Rapat' },
    { value: 'sports', label: 'Olahraga' },
    { value: 'creative', label: 'Aula / Studio' },
    { value: 'lab', label: 'Laboratorium' },
];

const filteredFacilities = computed(() => {
    return facilities.value.filter(f => {
        const matchType  = selectedType.value === 'all' || f.type === selectedType.value;
        const matchSearch = f.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            f.location.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchType && matchSearch;
    });
});

function availableCount(facility) {
    if (!facility.slots) return 0;
    return Object.values(facility.slots).filter(s => s === 'available').length;
}

import { isAuthenticated } from '../auth';

function bookFacility(facilityId) {
    if (!isAuthenticated()) {
        alert('Silakan login terlebih dahulu untuk melakukan reservasi fasilitas.');
        router.push({ name: 'login' });
        return;
    }
    router.push({ name: 'reservations' });
}
</script>

<template>
    <section class="content-wrap" id="screen-facilities" data-screen-id="9b4eaf60a859485ea4a2057a9eba4306">

        <!-- Header -->
        <div class="intro-row">
            <div>
                <p class="eyebrow">KATALOG FASILITAS</p>
                <h1>Grid Ketersediaan<span class="sun">✦</span></h1>
                <p class="subheading">Slot 30 menit. Klik slot hijau untuk reservasi langsung.</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar">
            <label class="search" for="search-facilities">
                <span>⌕</span>
                <input id="search-facilities" v-model="searchQuery" placeholder="Cari fasilitas atau lokasi…">
            </label>
            <div class="type-filters">
                <button
                    v-for="opt in typeOptions"
                    :key="opt.value"
                    :class="['filter-chip', { active: selectedType === opt.value }]"
                    @click="selectedType = opt.value"
                >{{ opt.label }}</button>
            </div>
        </div>

        <!-- Summary stats -->
        <div class="stat-grid" style="margin: 28px 0 36px">
            <article class="stat-card">
                <div class="stat-icon coral-bg">⌂</div>
                <div>
                    <span>Total Fasilitas</span>
                    <strong>{{ facilities.length }}</strong>
                    <small>{{ isLoading ? 'Memuat data...' : 'Aktif dan disetujui petugas' }}</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon blue-bg">◷</div>
                <div>
                    <span>Slot Tersedia</span>
                    <strong>{{ facilities.reduce((a, f) => a + availableCount(f), 0) }}</strong>
                    <small>Dari {{ facilities.length * timeSlots.length }} total slot aktif</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon yellow-bg">✓</div>
                <div>
                    <span>Slot Terpakai</span>
                    <strong>{{ facilities.reduce((a, f) => a + (timeSlots.length - availableCount(f)), 0) }}</strong>
                    <small>Diperbarui tiap 30 mnt</small>
                </div>
            </article>
        </div>

        <!-- Time slot legend -->
        <div v-if="facilities.length" class="legend-row">
            <div class="legend-item"><span class="legend-dot available"></span> Tersedia</div>
            <div class="legend-item"><span class="legend-dot booked"></span> Terpakai</div>
        </div>

        <!-- Availability grid -->
        <div v-if="isLoading" class="catalog-state-card">
            <div class="catalog-state-icon catalog-spinner"></div>
            <strong>Memuat katalog fasilitas</strong>
            <p>Mengambil fasilitas aktif dan jadwal ketersediaannya.</p>
        </div>

        <div v-else-if="loadError" class="catalog-state-card catalog-state-error">
            <div class="catalog-state-icon">!</div>
            <strong>Katalog belum tersedia</strong>
            <p>{{ loadError }}</p>
            <button class="catalog-state-action" @click="loadFacilities">Coba lagi</button>
        </div>

        <div v-else-if="facilities.length === 0" class="catalog-state-card">
            <div class="catalog-state-icon">▦</div>
            <strong>Belum ada fasilitas aktif</strong>
            <p>Fasilitas akan muncul di katalog setelah dibuat Admin dan disetujui Petugas.</p>
            <span class="catalog-state-note">Silakan cek kembali nanti.</span>
        </div>

        <div v-else class="availability-grid">
            <!-- Column headers: time slots -->
            <div class="grid-header">
                <div class="grid-label-col"></div>
                <div
                    v-for="slot in timeSlots"
                    :key="slot"
                    class="grid-time-label"
                >{{ slot }}</div>
            </div>

            <!-- Rows: one per facility -->
            <div
                v-for="facility in filteredFacilities"
                :key="facility.id"
                class="grid-row"
            >
                <!-- Facility info label -->
                <div class="grid-facility-label">
                    <div :class="['facility-icon', facility.color]">{{ facility.icon }}</div>
                    <div>
                        <strong>{{ facility.name }}</strong>
                        <span>{{ facility.location }}</span>
                    </div>
                </div>

                <!-- Slot cells -->
                <button
                    v-for="slot in timeSlots"
                    :key="slot"
                    :class="['slot-cell', facility.slots[slot]]"
                    :title="`${facility.name} — ${slot} (${facility.slots[slot] === 'available' ? 'Tersedia' : 'Terpakai'})`"
                    :aria-label="`${facility.name} slot ${slot} ${facility.slots[slot]}`"
                    :disabled="facility.slots[slot] === 'booked'"
                    @click="bookFacility(facility.id)"
                ></button>
            </div>

            <div v-if="filteredFacilities.length === 0" class="catalog-filter-empty">
                <strong>Tidak ada fasilitas yang cocok</strong>
                <span>Coba ubah kata kunci atau pilih kategori lain.</span>
            </div>
        </div>

    </section>
</template>

<style scoped>
.filter-bar {
    display: flex;
    gap: 14px;
    align-items: center;
    flex-wrap: wrap;
    margin: 32px 0 0;
}
.type-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.filter-chip {
    padding: 7px 14px;
    border-radius: 20px;
    border: 1px solid var(--line);
    background: #fff;
    color: #8a9892;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.15s;
}
.filter-chip.active,
.filter-chip:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
.legend-row {
    display: flex;
    gap: 20px;
    margin-bottom: 16px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #8a9892;
}
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}
.legend-dot.available { background: #dbeafe; border: 1.5px solid #93c5fd; }
.legend-dot.booked    { background: #f1f5f9; border: 1.5px solid #cbd5e1; }

/* Availability grid layout */
.availability-grid {
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #fff;
    overflow-x: auto;
    overflow-y: hidden;
}
.grid-header,
.grid-row {
    display: grid;
    grid-template-columns: 220px repeat(26, minmax(44px, 1fr));
    align-items: center;
}
.grid-header {
    border-bottom: 1px solid var(--line);
    background: #f7faf7;
    position: sticky;
    top: 0;
    z-index: 1;
}
.grid-label-col   { width: 220px; }
.grid-time-label  {
    padding: 10px 4px;
    font: 500 9px 'DM Mono', monospace;
    color: #99a5a0;
    text-align: center;
    letter-spacing: .5px;
}
.grid-row {
    border-bottom: 1px solid var(--line);
    transition: background 0.15s;
}
.grid-row:last-child { border-bottom: 0; }
.grid-row:hover { background: #f9fcfa; }

.grid-facility-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-right: 1px solid var(--line);
}
.grid-facility-label strong { display: block; font-size: 12px; }
.grid-facility-label span   { display: block; font-size: 10px; color: #9aa6a1; margin-top: 2px; }

/* Slot cells */
.slot-cell {
    height: 36px;
    margin: 6px 3px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: transform 0.1s, box-shadow 0.1s;
}
.slot-cell.available {
    background: #dbeafe;
    border: 1.5px solid #93c5fd;
}
.slot-cell.available:hover {
    background: var(--primary);
    transform: scaleY(1.08);
    box-shadow: 0 2px 8px #2563eb40;
}
.slot-cell.booked {
    background: #f1f5f9;
    border: 1.5px solid #cbd5e1;
    cursor: not-allowed;
    opacity: 0.65;
}
.catalog-state-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 270px;
    padding: 36px 24px;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    background: rgba(255, 255, 255, .75);
    text-align: center;
}
.catalog-state-card strong { color: var(--ink); font-size: 15px; }
.catalog-state-card p { max-width: 390px; margin: 9px 0 6px; color: var(--muted); font-size: 12px; line-height: 1.55; }
.catalog-state-icon { display: grid; place-items: center; width: 48px; height: 48px; margin-bottom: 14px; border-radius: 14px; color: var(--primary); background: var(--primary-soft); font-size: 21px; font-weight: 800; }
.catalog-state-error { color: #991b1b; background: #fffafa; }
.catalog-state-error strong { color: #991b1b; }
.catalog-state-error .catalog-state-icon { color: #b91c1c; background: #fef2f2; }
.catalog-state-action { margin-top: 14px; padding: 9px 14px; border: 1px solid #bfdbfe; border-radius: 8px; color: var(--primary); background: #fff; font-size: 11px; font-weight: 700; }
.catalog-state-note { color: #94a3b8; font-size: 11px; }
.catalog-spinner { border: 3px solid #dbeafe; border-top-color: var(--primary); animation: catalog-spin .8s linear infinite; }
.catalog-filter-empty { grid-column: 1 / -1; display: grid; gap: 6px; padding: 42px 20px; text-align: center; }
.catalog-filter-empty strong { color: var(--ink); font-size: 13px; }
.catalog-filter-empty span { color: var(--muted); font-size: 11px; }
@keyframes catalog-spin { to { transform: rotate(360deg); } }
</style>
