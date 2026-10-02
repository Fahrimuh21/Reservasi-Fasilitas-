<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import notification from '../components/notification/notificationService';
import { downloadCsvResponse, downloadErrorMessage } from '../utils/download';

const stats = ref({});
const exporting = ref(false);

onMounted(async () => {
    try {
        const response = await axios.get('/api/admin/recap');
        stats.value = response.data;
    } catch (error) {
        console.error("Gagal mengambil data rekap", error);
        notification.error('Data rekap gagal dimuat. Silakan coba lagi.');
    }
});

const downloadCsv = async () => {
    if (exporting.value) return;
    exporting.value = true;
    try {
        const response = await axios.get('/api/admin/export/reports', { responseType: 'blob' });
        await downloadCsvResponse(response, 'rekap_laporan_kerusakan.csv');
        notification.success('Unduhan rekap dimulai.');
    } catch (error) {
        notification.error(await downloadErrorMessage(error, 'Rekap laporan gagal diunduh. Silakan coba lagi.'));
    } finally {
        exporting.value = false;
    }
};
</script>

<template>
    <div class="p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">Dashboard Rekapitulasi Admin</h2>
        
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-blue-500">
                <h3 class="text-gray-500 text-sm">Total Fasilitas</h3>
                <p class="text-2xl font-bold">{{ stats.total_facilities || 0 }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-green-500">
                <h3 class="text-gray-500 text-sm">Reservasi Bulan Ini</h3>
                <p class="text-2xl font-bold">{{ stats.total_reservations || 0 }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-yellow-500">
                <h3 class="text-gray-500 text-sm">Total Laporan</h3>
                <p class="text-2xl font-bold">{{ stats.total_reports || 0 }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow border-l-4 border-red-500">
                <h3 class="text-gray-500 text-sm">Fasilitas Rusak</h3>
                <p class="text-2xl font-bold">{{ stats.broken_facilities || 0 }}</p>
            </div>
        </div>

        <button @click="downloadCsv" :disabled="exporting" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-60">
            {{ exporting ? 'Mengunduh...' : 'Download Rekap CSV' }}
        </button>
    </div>
</template>
