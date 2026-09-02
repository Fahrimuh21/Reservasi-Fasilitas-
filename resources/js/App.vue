<script setup>
import { computed, ref } from 'vue';

const activeTab = ref('Overview');
const selectedDate = ref('12 Jun');
const showModal = ref(false);
const search = ref('');

const tabs = ['Overview', 'My reservations', 'Facilities'];
const reservations = ref([
    { name: 'Ruang Rapat Merapi', type: 'Meeting room', date: 'Today, 10:00 - 12:00', color: 'coral', status: 'Confirmed' },
    { name: 'Lapangan Futsal A', type: 'Sports facility', date: 'Thu, 13 Jun, 16:00 - 18:00', color: 'blue', status: 'Confirmed' },
    { name: 'Studio Kreatif', type: 'Creative space', date: 'Sat, 15 Jun, 09:00 - 11:00', color: 'yellow', status: 'Pending' },
]);

const filteredReservations = computed(() => reservations.value.filter((item) =>
    item.name.toLowerCase().includes(search.value.toLowerCase())
));

function addReservation() {
    reservations.value.unshift({
        name: 'Ruang Diskusi Bromo',
        type: 'Meeting room',
        date: `${selectedDate.value}, 14:00 - 16:00`,
        color: 'green',
        status: 'Confirmed',
    });
    showModal.value = false;
}
</script>

<template>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand"><span class="brand-mark">R</span><span>RuangKita</span></div>
            <div class="workspace-label">Workspace</div>
            <nav class="side-nav">
                <button v-for="tab in tabs" :key="tab" :class="{ active: activeTab === tab }" @click="activeTab = tab">
                    <span class="nav-icon">{{ tab === 'Overview' ? '▦' : tab === 'My reservations' ? '◷' : '⌂' }}</span>{{ tab }}
                </button>
            </nav>
            <div class="sidebar-bottom">
                <button class="quiet-button"><span>⚙</span> Settings</button>
                <div class="user-card"><div class="avatar">FA</div><div><strong>Fahri Ahmad</strong><small>Administrator</small></div><span class="dots">•••</span></div>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar"><div class="breadcrumb">Workspace <span>/</span> {{ activeTab }}</div><button class="help-button">?</button></header>
            <section class="content-wrap">
                <div class="intro-row"><div><p class="eyebrow">THURSDAY, 12 JUNE 2025</p><h1>Good morning, Fahri<span class="sun">✦</span></h1><p class="subheading">Keep your day moving. Here's what is happening around your workspace.</p></div><button class="primary-button" @click="showModal = true"><span>＋</span> New reservation</button></div>

                <div class="stat-grid">
                    <article class="stat-card"><div class="stat-icon coral-bg">◷</div><div><span>Upcoming reservations</span><strong>03</strong><small>+1 from last week</small></div></article>
                    <article class="stat-card"><div class="stat-icon blue-bg">⌂</div><div><span>Available facilities</span><strong>12</strong><small>Across 4 locations</small></div></article>
                    <article class="stat-card"><div class="stat-icon yellow-bg">✓</div><div><span>Hours reserved</span><strong>18.5</strong><small>This month</small></div></article>
                </div>

                <div class="section-heading"><div><h2>Upcoming reservations</h2><p>Your confirmed and pending bookings.</p></div><label class="search"><span>⌕</span><input v-model="search" placeholder="Search reservations"></label></div>
                <div class="reservation-list"><article v-for="reservation in filteredReservations" :key="reservation.name" class="reservation-row"><div :class="['facility-icon', reservation.color]">{{ reservation.type === 'Sports facility' ? '✚' : reservation.type === 'Creative space' ? '✦' : '▦' }}</div><div class="reservation-info"><strong>{{ reservation.name }}</strong><span>{{ reservation.type }}</span></div><div class="reservation-date"><span>DATE & TIME</span><strong>{{ reservation.date }}</strong></div><span :class="['status', reservation.status.toLowerCase()]">{{ reservation.status }}</span><button class="more-button" title="More actions">•••</button></article><div v-if="filteredReservations.length === 0" class="empty-state">No reservations found.</div></div>

                <div class="lower-grid"><section class="calendar-panel"><div class="panel-heading"><div><h2>June 2025</h2><p>Choose a date to see facility availability.</p></div><div><button class="calendar-arrow">‹</button><button class="calendar-arrow">›</button></div></div><div class="weekdays"><span v-for="day in ['MON','TUE','WED','THU','FRI','SAT','SUN']" :key="day">{{ day }}</span></div><div class="dates"><button v-for="day in 30" :key="day" :class="{ selected: day === 12, muted: day < 5 }" @click="selectedDate = `${day} Jun`">{{ day }}</button></div></section><section class="tip-panel"><div class="tip-art">✦</div><div><p class="eyebrow">QUICK TIP</p><h2>Plan ahead, stay productive.</h2><p>Reserve your favorite space early to make sure it is ready when you need it.</p><button class="text-button" @click="activeTab = 'Facilities'">Explore facilities <span>→</span></button></div></section></div>
            </section>
        </main>

        <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false"><div class="modal"><button class="modal-close" @click="showModal = false">×</button><p class="eyebrow">NEW BOOKING</p><h2>Reserve a space</h2><p>Selecting a space for <strong>{{ selectedDate }}</strong>.</p><button class="primary-button full" @click="addReservation">Confirm reservation <span>→</span></button></div></div>
    </div>
</template>
