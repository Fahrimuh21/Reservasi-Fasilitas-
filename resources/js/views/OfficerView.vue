<script setup>
/**
 * OfficerView — Screen 3
 * Screen ID: 27f9c22580c44370a6c9f89785168a63
 * Dashboard Petugas & Antrean Approval
 */
import { ref, computed } from 'vue';

const filterStatus = ref('pending');

const queue = ref([
    { id: 'REQ-001', user: 'Budi Santoso',  avatar: 'BS', facility: 'Ruang Rapat Merapi',  date: 'Hari ini, 13:00 – 15:00', submitted: '08:42',  color: 'coral',  status: 'pending'  },
    { id: 'REQ-002', user: 'Rina Wijaya',   avatar: 'RW', facility: 'Studio Kreatif',       date: 'Besok, 10:00 – 12:00',    submitted: '07:15',  color: 'yellow', status: 'pending'  },
    { id: 'REQ-003', user: 'Andi Saputra',  avatar: 'AS', facility: 'Lapangan Futsal A',    date: 'Kamis, 16:00 – 18:00',    submitted: 'Kemarin', color: 'blue',   status: 'approved' },
    { id: 'REQ-004', user: 'Siti Rahayu',   avatar: 'SR', facility: 'Lab Komputer Rinjani', date: 'Jumat, 09:00 – 11:00',    submitted: 'Kemarin', color: 'blue',   status: 'rejected' },
    { id: 'REQ-005', user: 'Deni Kusuma',   avatar: 'DK', facility: 'Ruang Seminar Bromo',  date: 'Senin, 14:00 – 17:00',    submitted: '2 hr lalu', color: 'green', status: 'pending' },
]);

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

function approve(id) {
    const item = queue.value.find(q => q.id === id);
    if (item) item.status = 'approved';
}

function reject(id) {
    const item = queue.value.find(q => q.id === id);
    if (item) item.status = 'rejected';
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
        <div class="queue-list">
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
                        <button class="action-btn approve" :id="`btn-approve-${item.id}`" @click="approve(item.id)">
                            ✓ Setujui
                        </button>
                        <button class="action-btn reject" :id="`btn-reject-${item.id}`" @click="reject(item.id)">
                            ✕ Tolak
                        </button>
                    </div>
                    <div v-else class="action-placeholder"></div>
                </article>
            </TransitionGroup>
            <div v-if="filteredQueue.length === 0" class="empty-state">
                Tidak ada permintaan dengan status ini.
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
</style>
