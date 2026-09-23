<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({
    facility_id: '',
    category: 'Kebersihan',
    description: '',
});
const photos = ref([]);
const message = ref('');

const handleFileUpload = (event) => {
    photos.value = event.target.files;
};

const submitReport = async () => {
    const formData = new FormData();
    formData.append('facility_id', form.value.facility_id);
    formData.append('category', form.value.category);
    formData.append('description', form.value.description);
    
    for (let i = 0; i < photos.value.length; i++) {
        formData.append(`photos[${i}]`, photos.value[i]);
    }

    try {
        await axios.post('/api/user/reports', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        message.value = 'Laporan Kerusakan Berhasil Dikirim!';
        form.value.description = ''; // Reset form
    } catch (error) {
        message.value = 'Gagal mengirim laporan. Pastikan semua data terisi.';
    }
};
</script>

<template>
    <div class="p-6 max-w-lg mx-auto bg-white rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-4">Form Laporan Kerusakan</h2>
        <div v-if="message" class="mb-4 p-3 bg-blue-100 text-blue-700 rounded">{{ message }}</div>
        
        <form @submit.prevent="submitReport" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">ID Fasilitas (Contoh: 1)</label>
                <input v-model="form.facility_id" type="number" required class="w-full border p-2 rounded" />
            </div>
            <div>
                <label class="block text-sm font-medium">Kategori</label>
                <select v-model="form.category" class="w-full border p-2 rounded">
                    <option>Infrastruktur</option>
                    <option>Elektronik</option>
                    <option>Kebersihan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Deskripsi Masalah</label>
                <textarea v-model="form.description" required class="w-full border p-2 rounded"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium">Upload Foto Bukti</label>
                <input type="file" @change="handleFileUpload" multiple accept="image/*" class="w-full" />
            </div>
            <button type="submit" class="w-full bg-red-600 text-white p-2 rounded hover:bg-red-700">Kirim Laporan</button>
        </form>
    </div>
</template>