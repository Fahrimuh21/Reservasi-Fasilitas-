<script setup>
/**
 * ReservationsView — Screen 2
 * Screen ID: afa48fa655154f92b6194d75a21aa27b
 *
 * Konten dimigrasikan dari App.vue (lama) ke view terpisah.
 * State di sini bersifat LOKAL — tidak mempengaruhi navigasi global.
 */
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

const router  = useRouter();
const selectedDate = ref('12 Jun');
const showModal    = ref(false);
const search       = ref('');

const reservations = ref([
    { name: 'Ruang Rapat Merapi',  type: 'Meeting room',    date: 'Today, 10:00 - 12:00',          color: 'coral',  status: 'Confirmed' },
    { name: 'Lapangan Futsal A',   type: 'Sports facility', date: 'Thu, 13 Jun, 16:00 - 18:00',    color: 'blue',   status: 'Confirmed' },
    { name: 'Studio Kreatif',      type: 'Creative space',  date: 'Sat, 15 Jun, 09:00 - 11:00',    color: 'yellow', status: 'Pending'   },
]);

const filteredReservations = computed(() =>
    reservations.value.filter(item =>
        item.name.toLowerCase().includes(search.value.toLowerCase())
    )
);

function addReservation() {
    reservations.value.unshift({
        name:   'Ruang Diskusi Bromo',
        type:   'Meeting room',
        date:   `${selectedDate.value}, 14:00 - 16:00`,
        color:  'green',
        status: 'Confirmed',
    });
    showModal.value = false;
}

function goToFacilities() {
    router.push({ name: 'facilities' });
}

const iconMap = {
    'Sports facility': '✚',
    'Creative space':  '✦',
    'Meeting room':    '▦',
};
</script>

<template>
    <section class="content-wrap" id="screen-reservations" data-screen-id="afa48fa655154f92b6194d75a21aa27b">

        <!-- Intro row -->
        <div class="intro-row">
            <div>
                <p class="eyebrow">THURSDAY, 12 JUNE 2025</p>
                <h1>Good morning, Fahri<span class="sun">✦</span></h1>
                <p class="subheading">Keep your day moving. Here's what is happening around your workspace.</p>
            </div>
            <button class="primary-button" id="btn-new-reservation" @click="showModal = true">
                <span>＋</span> New reservation
            </button>
        </div>

        <!-- Stats -->
        <div class="stat-grid">
            <article class="stat-card">
                <div class="stat-icon coral-bg">◷</div>
                <div>
                    <span>Upcoming reservations</span>
                    <strong>{{ reservations.length.toString().padStart(2, '0') }}</strong>
                    <small>+1 from last week</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon blue-bg">⌂</div>
                <div>
                    <span>Available facilities</span>
                    <strong>12</strong>
                    <small>Across 4 locations</small>
                </div>
            </article>
            <article class="stat-card">
                <div class="stat-icon yellow-bg">✓</div>
                <div>
                    <span>Hours reserved</span>
                    <strong>18.5</strong>
                    <small>This month</small>
                </div>
            </article>
        </div>

        <!-- Reservation list -->
        <div class="section-heading">
            <div>
                <h2>Upcoming reservations</h2>
                <p>Your confirmed and pending bookings.</p>
            </div>
            <label class="search" for="search-reservations">
                <span>⌕</span>
                <input id="search-reservations" v-model="search" placeholder="Search reservations">
            </label>
        </div>

        <div class="reservation-list">
            <article
                v-for="reservation in filteredReservations"
                :key="reservation.name"
                class="reservation-row"
            >
                <div :class="['facility-icon', reservation.color]">
                    {{ iconMap[reservation.type] ?? '▦' }}
                </div>
                <div class="reservation-info">
                    <strong>{{ reservation.name }}</strong>
                    <span>{{ reservation.type }}</span>
                </div>
                <div class="reservation-date">
                    <span>DATE &amp; TIME</span>
                    <strong>{{ reservation.date }}</strong>
                </div>
                <span :class="['status', reservation.status.toLowerCase()]">{{ reservation.status }}</span>
                <button class="more-button" title="More actions" :aria-label="`More actions for ${reservation.name}`">•••</button>
            </article>
            <div v-if="filteredReservations.length === 0" class="empty-state">
                No reservations found.
            </div>
        </div>

        <!-- Lower grid: calendar + tip -->
        <div class="lower-grid">
            <section class="calendar-panel">
                <div class="panel-heading">
                    <div>
                        <h2>June 2025</h2>
                        <p>Choose a date to see facility availability.</p>
                    </div>
                    <div>
                        <button class="calendar-arrow" aria-label="Previous month">‹</button>
                        <button class="calendar-arrow" aria-label="Next month">›</button>
                    </div>
                </div>
                <div class="weekdays">
                    <span v-for="day in ['MON','TUE','WED','THU','FRI','SAT','SUN']" :key="day">{{ day }}</span>
                </div>
                <div class="dates">
                    <button
                        v-for="day in 30"
                        :key="day"
                        :class="{ selected: day === 12, muted: day < 5 }"
                        :aria-label="`${day} June`"
                        :aria-pressed="day === 12"
                        @click="selectedDate = `${day} Jun`"
                    >{{ day }}</button>
                </div>
            </section>

            <section class="tip-panel">
                <div class="tip-art" aria-hidden="true">✦</div>
                <div>
                    <p class="eyebrow">QUICK TIP</p>
                    <h2>Plan ahead, stay productive.</h2>
                    <p>Reserve your favorite space early to make sure it is ready when you need it.</p>
                    <button class="text-button" id="btn-explore-facilities" @click="goToFacilities">
                        Explore facilities <span>→</span>
                    </button>
                </div>
            </section>
        </div>

        <!-- New Reservation Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="modal-backdrop"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
                @click.self="showModal = false"
            >
                <div class="modal">
                    <button class="modal-close" aria-label="Close modal" @click="showModal = false">×</button>
                    <p class="eyebrow">NEW BOOKING</p>
                    <h2 id="modal-title">Reserve a space</h2>
                    <p>Selecting a space for <strong>{{ selectedDate }}</strong>.</p>
                    <button class="primary-button full" id="btn-confirm-reservation" @click="addReservation">
                        Confirm reservation <span>→</span>
                    </button>
                </div>
            </div>
        </Teleport>
    </section>
</template>
