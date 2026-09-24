<script setup>
import { computed, onUnmounted, reactive, ref } from 'vue';
import axios from 'axios';
import { Activity, ImagePlus, Send, Wrench, X } from 'lucide-vue-next';
import { apiError, useLiveCollection } from '../composables/useLiveCollection';
import { formatDate, statusLabel } from '../utils/reservations';
import '../../css/workflow.css';

const { items: facilities, error: facilityError } = useLiveCollection('/api/facilities?per_page=100', 15000);
const { items: tickets, error, updatedAt, loading, refresh } = useLiveCollection('/api/user/reports');
const categories = ['Elektronik', 'AC / HVAC', 'Listrik', 'Kebersihan', 'Infrastruktur'];
const form = reactive({ facility_id: '', category: '', description: '' });
const photo = ref(null);
const preview = ref('');
const fileInput = ref(null);
const saving = ref(false);
const formError = ref('');
const notice = ref('');
const openCount = computed(() => tickets.value.filter(item => ['new', 'in_progress'].includes(item.status)).length);

function clearPhoto() {
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = '';
    photo.value = null;
    if (fileInput.value) fileInput.value.value = '';
}
function uploadFile(event) {
    const file = event.target.files[0];
    clearPhoto();
    if (!file) return;
    if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 2 * 1024 * 1024) {
        formError.value = 'Foto harus JPG atau PNG, maksimal 2 MB.';
        return;
    }
    formError.value = '';
    photo.value = file;
    preview.value = URL.createObjectURL(file);
}
onUnmounted(clearPhoto);

async function submitReport() {
    if (saving.value) return;
    saving.value = true;
    formError.value = '';
    notice.value = '';
    const payload = new FormData();
    for (const [key, value] of Object.entries(form)) payload.append(key, value);
    if (photo.value) payload.append('photos[]', photo.value);
    try {
        const response = await axios.post('/api/user/reports', payload);
        notice.value = `Laporan LAP-${response.data.data.id} tersimpan dan menunggu penanganan petugas.`;
        form.description = '';
        clearPhoto();
        await refresh();
    } catch (failure) {
        formError.value = apiError(failure);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <section id="screen-report" class="content-wrap workflow">
        <header class="wf-header">
            <div><p class="eyebrow">RUANGKITA / PENGGUNA</p><h1>Laporan kerusakan</h1></div>
            <span class="wf-live" :class="{ offline: error }"><Activity :size="15" />{{ error ? 'Koneksi bermasalah' : updatedAt ? 'Tersinkron' : 'Menghubungkan...' }}</span>
        </header>
        <p v-if="notice" class="wf-notice" role="status">{{ notice }}</p>
        <p v-if="error || facilityError" class="wf-notice wf-error" role="alert">{{ error || facilityError }}</p>
        <div class="report-columns">
            <form @submit.prevent="submitReport">
                <h2>Laporan baru</h2>
                <fieldset class="wf-fields report-fields" :disabled="saving">
                    <label>Fasilitas<select v-model="form.facility_id" aria-label="Fasilitas" required><option value="" disabled>Pilih fasilitas</option><option v-for="facility in facilities" :key="facility.id" :value="facility.id">{{ facility.name }}</option></select></label>
                    <label>Kategori<select v-model="form.category" aria-label="Kategori" required><option value="" disabled>Pilih kategori</option><option v-for="category in categories" :key="category">{{ category }}</option></select></label>
                    <label>Deskripsi kerusakan<textarea v-model="form.description" required maxlength="5000" rows="5" /></label>
                    <label><span class="wf-actions"><ImagePlus :size="17" /> Foto (opsional, JPG/PNG, maks. 2 MB)</span><input ref="fileInput" type="file" accept="image/jpeg,image/png" @change="uploadFile" /></label>
                    <div v-if="preview" class="report-preview"><img :src="preview" alt="Foto kerusakan" /><button type="button" class="wf-icon-button" title="Hapus foto" aria-label="Hapus foto" @click="clearPhoto"><X :size="17" /></button></div>
                    <p v-if="formError" class="wf-notice wf-error" role="alert">{{ formError }}</p>
                    <button type="submit" class="wf-button wf-primary" :disabled="saving"><Send :size="16" />{{ saving ? 'Mengirim...' : 'Kirim laporan' }}</button>
                </fieldset>
            </form>
            <section aria-label="Riwayat laporan">
                <div class="wf-section-head"><h2>Riwayat laporan</h2><span class="wf-reference">{{ openCount }} terbuka</span></div>
                <div v-if="loading" class="wf-empty">Memuat laporan...</div>
                <div v-else-if="!tickets.length" class="wf-empty"><Wrench :size="30" /><h3>Belum ada laporan</h3></div>
                <div class="wf-list report-fields">
                    <article v-for="ticket in tickets" :key="ticket.id" class="wf-request">
                        <div class="wf-request-top"><h3>{{ ticket.facility?.name }}</h3><span class="wf-status" :class="ticket.status">{{ statusLabel(ticket.status) }}</span></div>
                        <p>{{ ticket.category }}</p><p class="wf-purpose">{{ ticket.description }}</p>
                        <p v-if="ticket.resolution_note">Catatan petugas: {{ ticket.resolution_note }}</p>
                        <footer class="wf-request-footer"><span class="wf-reference">LAP-{{ ticket.id }} / {{ formatDate(ticket.created_at) }}</span></footer>
                    </article>
                </div>
            </section>
        </div>
    </section>
</template>

<style scoped>
.report-columns { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: 32px; }
.report-fields { margin-top: 20px; }
.report-preview { display: flex; align-items: flex-start; gap: 12px; }
.report-preview img { width: 180px; max-width: 75%; height: 130px; object-fit: contain; border: 1px solid var(--line); border-radius: 6px; }
@media (max-width: 900px) { .report-columns { grid-template-columns: minmax(0, 1fr); } }
</style>
