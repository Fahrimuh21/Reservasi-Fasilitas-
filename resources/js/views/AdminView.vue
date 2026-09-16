<script setup>
/**
 * AdminView — Screen 5
 * Screen ID: 9400ab25f7044638a44066516e2c8bfd
 * Dashboard Admin & Rekapitulasi Sarana
 */
import { ref, computed } from 'vue';

const selectedPeriod = ref('month');
const periods = [
    { value: 'week',  label: '7 Hari' },
    { value: 'month', label: '30 Hari' },
    { value: 'year',  label: '1 Tahun' },
];

// KPI stats
const kpis = computed(() => {
    const multiplier = selectedPeriod.value === 'week' ? 0.25 : selectedPeriod.value === 'year' ? 12 : 1;
    return [
        { label: 'Total Reservasi',   value: Math.round(148 * multiplier), delta: '+12%', color: 'coral',  icon: '◷' },
        { label: 'Tingkat Pemakaian', value: '78%',                        delta: '+5%',  color: 'blue',   icon: '▦' },
        { label: 'Laporan Kerusakan', value: Math.round(7  * multiplier),  delta: '-3%',  color: 'yellow', icon: '⚠' },
        { label: 'Pengguna Aktif',    value: Math.round(64 * multiplier),  delta: '+18%', color: 'green',  icon: '✓' },
    ];
});

// Facility utilization
const facilities = ref([
    { name: 'Ruang Rapat Merapi',  type: 'Ruang Rapat',   usage: 91, reservations: 42, color: 'coral'  },
    { name: 'Lapangan Futsal A',   type: 'Olahraga',      usage: 78, reservations: 35, color: 'blue'   },
    { name: 'Studio Kreatif',      type: 'Kreatif',       usage: 65, reservations: 28, color: 'yellow' },
    { name: 'Ruang Seminar Bromo', type: 'Ruang Rapat',   usage: 54, reservations: 19, color: 'green'  },
    { name: 'Lab Komputer Rinjani',type: 'Laboratorium',  usage: 82, reservations: 38, color: 'blue'   },
    { name: 'Lapangan Basket',     type: 'Olahraga',      usage: 47, reservations: 16, color: 'coral'  },
]);

const sortedFacilities = computed(() =>
    [...facilities.value].sort((a, b) => b.usage - a.usage)
);

// Recent admin activity
const activities = ref([
    { type: 'approve', text: 'Reservasi REQ-003 (Andi Saputra) disetujui', time: '10 mnt lalu', color: 'green' },
    { type: 'reject',  text: 'Reservasi REQ-004 (Siti Rahayu) ditolak',    time: '32 mnt lalu', color: 'coral' },
    { type: 'ticket',  text: 'Tiket TKT-2025-087 (AC Merapi) diselesaikan',time: '1 jam lalu',  color: 'blue'  },
    { type: 'new',     text: 'Fasilitas baru "Lab Bahasa" ditambahkan',     time: '3 jam lalu',  color: 'yellow'},
]);

function usageBarColor(usage) {
    if (usage >= 80) return '#f27963';
    if (usage >= 60) return '#4a9a76';
    return '#6aafd4';
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
                    @click="selectedPeriod = p.value"
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
                    <small :class="kpi.delta.startsWith('+') ? 'delta-up' : 'delta-down'">
                        {{ kpi.delta }} vs periode lalu
                    </small>
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
                <div class="util-list">
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
                <div class="activity-list">
                    <div v-for="act in activities" :key="act.text" class="activity-item">
                        <div :class="['act-dot', act.color]"></div>
                        <div class="activity-info">
                            <span>{{ act.text }}</span>
                            <small>{{ act.time }}</small>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div style="margin-top: 24px; border-top: 1px solid var(--line); padding-top: 20px">
                    <p class="eyebrow" style="margin-bottom: 12px">AKSI CEPAT</p>
                    <div class="quick-actions">
                        <button class="quick-btn" id="btn-export-report">⬇ Export PDF</button>
                        <button class="quick-btn" id="btn-add-facility">＋ Tambah Fasilitas</button>
                        <button class="quick-btn" id="btn-manage-users">👤 Kelola User</button>
                    </div>
                </div>
            </div>

        </div>
    </section>
</template>

<style scoped>
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
</style>
