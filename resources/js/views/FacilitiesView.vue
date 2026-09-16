<script setup>
/**
 * FacilitiesView — Screen 1
 * Screen ID: 9b4eaf60a859485ea4a2057a9eba4306
 * Katalog & Grid Ketersediaan Fasilitas 30 Menit
 */
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const searchQuery  = ref('');
const selectedType = ref('all');
const currentTime  = ref(new Date());

// Simulate 30-minute availability slots
const timeSlots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
                   '11:00', '11:30', '13:00', '13:30', '14:00', '14:30',
                   '15:00', '15:30', '16:00', '16:30'];

const facilities = ref([
    {
        id: 'F001', name: 'Ruang Rapat Merapi', type: 'meeting',
        location: 'Gedung A, Lt. 2', capacity: 20, color: 'coral',
        icon: '▦',
        slots: { '08:00': 'available', '08:30': 'booked', '09:00': 'booked', '09:30': 'available',
                 '10:00': 'available', '10:30': 'available', '11:00': 'booked', '11:30': 'available',
                 '13:00': 'available', '13:30': 'booked', '14:00': 'booked', '14:30': 'available',
                 '15:00': 'available', '15:30': 'available', '16:00': 'available', '16:30': 'available' },
    },
    {
        id: 'F002', name: 'Lapangan Futsal A', type: 'sports',
        location: 'Kompleks Olahraga', capacity: 10, color: 'blue',
        icon: '✚',
        slots: { '08:00': 'booked', '08:30': 'booked', '09:00': 'available', '09:30': 'available',
                 '10:00': 'booked', '10:30': 'available', '11:00': 'available', '11:30': 'available',
                 '13:00': 'booked', '13:30': 'booked', '14:00': 'available', '14:30': 'available',
                 '15:00': 'booked', '15:30': 'available', '16:00': 'available', '16:30': 'booked' },
    },
    {
        id: 'F003', name: 'Studio Kreatif', type: 'creative',
        location: 'Gedung B, Lt. 1', capacity: 15, color: 'yellow',
        icon: '✦',
        slots: { '08:00': 'available', '08:30': 'available', '09:00': 'available', '09:30': 'booked',
                 '10:00': 'booked', '10:30': 'booked', '11:00': 'available', '11:30': 'available',
                 '13:00': 'available', '13:30': 'available', '14:00': 'booked', '14:30': 'booked',
                 '15:00': 'available', '15:30': 'available', '16:00': 'booked', '16:30': 'available' },
    },
    {
        id: 'F004', name: 'Ruang Seminar Bromo', type: 'meeting',
        location: 'Gedung C, Lt. 3', capacity: 80, color: 'green',
        icon: '▦',
        slots: { '08:00': 'available', '08:30': 'available', '09:00': 'booked', '09:30': 'booked',
                 '10:00': 'booked', '10:30': 'booked', '11:00': 'booked', '11:30': 'available',
                 '13:00': 'available', '13:30': 'available', '14:00': 'available', '14:30': 'booked',
                 '15:00': 'booked', '15:30': 'booked', '16:00': 'available', '16:30': 'available' },
    },
    {
        id: 'F005', name: 'Lapangan Basket', type: 'sports',
        location: 'Kompleks Olahraga', capacity: 12, color: 'coral',
        icon: '✚',
        slots: { '08:00': 'available', '08:30': 'available', '09:00': 'available', '09:30': 'available',
                 '10:00': 'available', '10:30': 'booked', '11:00': 'booked', '11:30': 'booked',
                 '13:00': 'booked', '13:30': 'available', '14:00': 'available', '14:30': 'available',
                 '15:00': 'available', '15:30': 'booked', '16:00': 'booked', '16:30': 'available' },
    },
    {
        id: 'F006', name: 'Lab Komputer Rinjani', type: 'lab',
        location: 'Gedung D, Lt. 1', capacity: 40, color: 'blue',
        icon: '⌘',
        slots: { '08:00': 'booked', '08:30': 'booked', '09:00': 'booked', '09:30': 'available',
                 '10:00': 'available', '10:30': 'available', '11:00': 'booked', '11:30': 'booked',
                 '13:00': 'available', '13:30': 'booked', '14:00': 'booked', '14:30': 'available',
                 '15:00': 'available', '15:30': 'available', '16:00': 'booked', '16:30': 'booked' },
    },
]);

const typeOptions = [
    { value: 'all', label: 'Semua Fasilitas' },
    { value: 'meeting', label: 'Ruang Rapat' },
    { value: 'sports', label: 'Olahraga' },
    { value: 'creative', label: 'Studio Kreatif' },
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
    return Object.values(facility.slots).filter(s => s === 'available').length;
}

function bookFacility(facilityId) {
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
                    <small>Tersedia hari ini</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon blue-bg">◷</div>
                <div>
                    <span>Slot Tersedia</span>
                    <strong>{{ facilities.reduce((a, f) => a + availableCount(f), 0) }}</strong>
                    <small>Dari {{ facilities.length * timeSlots.length }} total slot</small>
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
        <div class="legend-row">
            <div class="legend-item"><span class="legend-dot available"></span> Tersedia</div>
            <div class="legend-item"><span class="legend-dot booked"></span> Terpakai</div>
        </div>

        <!-- Availability grid -->
        <div class="availability-grid">
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

            <div v-if="filteredFacilities.length === 0" class="empty-state" style="grid-column: 1/-1; padding: 40px">
                Tidak ada fasilitas yang cocok dengan filter.
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
    grid-template-columns: 220px repeat(16, minmax(44px, 1fr));
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
</style>
