<script setup>
/**
 * ReportView — Screen 4
 * Screen ID: 55597836a1f445e4ab3cd358e0c6155b
 * Form Pelaporan Kerusakan Berfoto & Tiket
 */
import { ref, reactive } from 'vue';

const submitted   = ref(false);
const previewUrl  = ref(null);
const dragOver    = ref(false);
const ticketId    = ref('');

const form = reactive({
    facility:    '',
    location:    '',
    category:    '',
    severity:    'medium',
    description: '',
    photo:       null,
    reporterName: 'Fahri Ahmad',
    reporterEmail: 'fahri@campus.ac.id',
});

const facilities = [
    'Ruang Rapat Merapi', 'Lapangan Futsal A', 'Studio Kreatif',
    'Ruang Seminar Bromo', 'Lapangan Basket', 'Lab Komputer Rinjani',
];
const categories = ['Listrik & Lampu', 'Plumbing & Sanitasi', 'HVAC / AC', 'Perabot & Furniture', 'Peralatan Elektronik', 'Struktural / Sipil', 'Lainnya'];
const severities = [
    { value: 'low',      label: '🟢 Rendah',  desc: 'Tidak mengganggu operasional' },
    { value: 'medium',   label: '🟡 Sedang',  desc: 'Mengganggu sebagian layanan' },
    { value: 'high',     label: '🔴 Tinggi',  desc: 'Layanan tidak dapat digunakan' },
];

const tickets = ref([
    { id: 'TKT-2025-089', facility: 'Lab Komputer Rinjani', category: 'Peralatan Elektronik', severity: 'high',   status: 'open',        date: '14 Jun' },
    { id: 'TKT-2025-088', facility: 'Lapangan Futsal A',    category: 'Listrik & Lampu',       severity: 'low',    status: 'in-progress', date: '13 Jun' },
    { id: 'TKT-2025-087', facility: 'Ruang Rapat Merapi',   category: 'HVAC / AC',             severity: 'medium', status: 'resolved',    date: '11 Jun' },
]);

function handleFileDrop(e) {
    dragOver.value = false;
    const file = e.dataTransfer?.files[0] || e.target.files?.[0];
    if (file && file.type.startsWith('image/')) {
        form.photo = file;
        previewUrl.value = URL.createObjectURL(file);
    }
}

function submitReport() {
    const id = `TKT-2025-${(90 + tickets.value.length).toString().padStart(3, '0')}`;
    ticketId.value = id;
    tickets.value.unshift({
        id,
        facility: form.facility,
        category: form.category,
        severity: form.severity,
        status: 'open',
        date: 'Hari ini',
    });
    submitted.value = true;
}

function resetForm() {
    Object.assign(form, { facility: '', location: '', category: '', severity: 'medium', description: '', photo: null });
    previewUrl.value = null;
    submitted.value  = false;
}

const severityColor = { low: 'blue', medium: 'yellow', high: 'coral' };
const statusLabel   = { open: 'Terbuka', 'in-progress': 'Diproses', resolved: 'Selesai' };
const statusClass   = { open: 'pending', 'in-progress': 'pending', resolved: 'confirmed' };
</script>

<template>
    <section class="content-wrap" id="screen-report" data-screen-id="55597836a1f445e4ab3cd358e0c6155b">

        <div class="intro-row">
            <div>
                <p class="eyebrow">PELAPORAN KERUSAKAN</p>
                <h1>Buat Tiket Laporan<span class="sun">✦</span></h1>
                <p class="subheading">Laporkan kerusakan fasilitas dengan foto pendukung untuk penanganan cepat.</p>
            </div>
        </div>

        <div class="report-layout">

            <!-- === FORM PANEL === -->
            <div class="form-panel">

                <!-- Success state -->
                <div v-if="submitted" class="success-banner">
                    <div class="success-icon">✓</div>
                    <div>
                        <strong>Laporan berhasil dikirim!</strong>
                        <p>Tiket <strong>{{ ticketId }}</strong> telah dibuat dan akan segera ditangani oleh tim teknis.</p>
                    </div>
                    <button class="text-button" @click="resetForm">Buat laporan baru →</button>
                </div>

                <template v-else>
                    <!-- Facility & Location -->
                    <div class="form-group">
                        <label for="report-facility">Fasilitas *</label>
                        <select id="report-facility" v-model="form.facility" required>
                            <option value="" disabled>Pilih fasilitas...</option>
                            <option v-for="f in facilities" :key="f" :value="f">{{ f }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="report-location">Lokasi Spesifik</label>
                        <input id="report-location" v-model="form.location" placeholder="cth: Meja pojok kiri, dekat jendela" />
                    </div>

                    <!-- Category & Severity -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="report-category">Kategori *</label>
                            <select id="report-category" v-model="form.category" required>
                                <option value="" disabled>Pilih kategori...</option>
                                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Severity -->
                    <div class="form-group">
                        <label>Tingkat Keparahan *</label>
                        <div class="severity-options">
                            <label
                                v-for="s in severities"
                                :key="s.value"
                                :class="['severity-chip', s.value, { selected: form.severity === s.value }]"
                            >
                                <input type="radio" v-model="form.severity" :value="s.value" class="sr-only" />
                                <span>{{ s.label }}</span>
                                <small>{{ s.desc }}</small>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="report-desc">Deskripsi Kerusakan *</label>
                        <textarea
                            id="report-desc"
                            v-model="form.description"
                            rows="4"
                            placeholder="Jelaskan kerusakan secara detail: apa yang rusak, kapan pertama terdeteksi, dampaknya..."
                        ></textarea>
                    </div>

                    <!-- Photo upload -->
                    <div class="form-group">
                        <label>Foto Pendukung</label>
                        <label
                            class="dropzone"
                            :class="{ 'dragover': dragOver }"
                            for="file-upload"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleFileDrop"
                        >
                            <img v-if="previewUrl" :src="previewUrl" class="photo-preview" alt="Preview foto kerusakan" />
                            <template v-else>
                                <span class="dropzone-icon">📷</span>
                                <span>Seret & lepas foto di sini, atau <u>pilih file</u></span>
                                <small>JPG, PNG, WEBP — maks 10MB</small>
                            </template>
                            <input id="file-upload" type="file" accept="image/*" class="sr-only" @change="handleFileDrop" />
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        class="primary-button full"
                        id="btn-submit-report"
                        :disabled="!form.facility || !form.category || !form.description"
                        @click="submitReport"
                        style="margin-top: 8px"
                    >
                        <span>⚑</span> Kirim Laporan &amp; Buat Tiket
                    </button>
                </template>
            </div>

            <!-- === TICKET LIST PANEL === -->
            <div class="ticket-panel">
                <div class="panel-heading" style="margin-bottom: 16px">
                    <div>
                        <h2>Riwayat Tiket</h2>
                        <p>Tiket yang pernah Anda buat.</p>
                    </div>
                </div>
                <div class="ticket-list">
                    <article v-for="ticket in tickets" :key="ticket.id" class="ticket-card">
                        <div :class="['facility-icon', severityColor[ticket.severity]]">⚠</div>
                        <div class="ticket-info">
                            <strong>{{ ticket.id }}</strong>
                            <span>{{ ticket.facility }}</span>
                            <small>{{ ticket.category }} · {{ ticket.date }}</small>
                        </div>
                        <span :class="['status', statusClass[ticket.status]]">{{ statusLabel[ticket.status] }}</span>
                    </article>
                </div>
            </div>

        </div>
    </section>
</template>

<style scoped>
.report-layout {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 24px;
    margin-top: 32px;
    align-items: start;
}
@media (max-width: 900px) { .report-layout { grid-template-columns: 1fr; } }

.form-panel, .ticket-panel {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 28px;
}
.form-group { margin-bottom: 18px; }
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7a75;
    text-transform: uppercase;
    letter-spacing: .8px;
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--line);
    border-radius: 7px;
    font-size: 13px;
    color: var(--ink);
    background: #fafcfb;
    outline: none;
    transition: border-color 0.15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 3px var(--primary-soft); }

.form-row { display: grid; grid-template-columns: 1fr; gap: 14px; }

/* Severity chips */
.severity-options { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }
.severity-chip {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 10px 12px;
    border: 1.5px solid var(--line);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
}
.severity-chip span { font-size: 12px; font-weight: 700; }
.severity-chip small { font-size: 9px; color: #9aa6a1; line-height: 1.4; }
.severity-chip.low.selected    { border-color: #6aafd4; background: #e3eef4; }
.severity-chip.medium.selected { border-color: #c9a32e; background: #fbf0d5; }
.severity-chip.high.selected   { border-color: var(--coral); background: #fbe4df; }

/* Dropzone */
.dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 28px;
    border: 2px dashed var(--line);
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    color: #8a9892;
    text-align: center;
    transition: border-color 0.15s, background 0.15s;
}
.dropzone:hover, .dropzone.dragover { border-color: var(--primary); background: var(--primary-soft); }
.dropzone-icon { font-size: 28px; }
.photo-preview { max-width: 100%; max-height: 180px; border-radius: 8px; object-fit: cover; }

/* Success banner */
.success-banner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
    padding: 16px 0;
}
.success-icon {
    display: grid;
    place-items: center;
    width: 56px; height: 56px;
    border-radius: 50%;
    background: #e1f3e8;
    color: #3a8f62;
    font-size: 24px;
}
.success-banner strong { display: block; font-size: 16px; }
.success-banner p { font-size: 12px; color: #8a9892; margin: 4px 0 0; }

/* Ticket list */
.ticket-list { display: grid; gap: 10px; }
.ticket-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #fafcfb;
}
.ticket-info { flex: 1; }
.ticket-info strong { display: block; font: 700 11px 'DM Mono', monospace; color: var(--coral); }
.ticket-info span   { display: block; font-size: 12px; margin-top: 2px; }
.ticket-info small  { display: block; font-size: 10px; color: #9aa6a1; margin-top: 2px; }

/* Accessibility */
.sr-only {
    position: absolute;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}
</style>
