<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import campusImage from '../../asset/Undip.png';

const router = useRouter();
const form = ref({ name: '', email: '', password: '', password_confirmation: '', user_type: 'mahasiswa' });
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

async function submitRegister() {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await axios.post('/api/register', form.value);
        successMessage.value = 'Registrasi berhasil. Tunggu verifikasi admin untuk bisa login.';
        window.setTimeout(() => router.push({ name: 'login' }), 1200);
    } catch (error) {
        const validationErrors = error?.response?.data?.errors;
        const firstMessage = validationErrors ? Object.values(validationErrors)[0]?.[0] : error?.response?.data?.message;
        errorMessage.value = firstMessage || 'Registrasi gagal. Silakan periksa data dan coba lagi.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <main class="auth-screen">
        <section class="auth-screen__panel">
            <div class="auth-screen__media">
                <img :src="campusImage" alt="Kampus Universitas Diponegoro" />
                <div class="auth-screen__media-copy">
                    <span>RUANGKITA / UNDIP</span>
                    <h2>Buat akun dan mulai kelola kebutuhan kampus.</h2>
                    <p>Ajukan reservasi fasilitas, kirim laporan, dan pantau status aktivitas kampus.</p>
                </div>
            </div>
            <div class="auth-screen__form">
                <RouterLink :to="{ name: 'landing' }" class="auth-screen__brand">RUANGKITA</RouterLink>
                <div class="auth-screen__heading">
                    <span class="auth-screen__eyebrow">AKSES CIVITAS AKADEMIKA</span>
                    <h1>Buat akun baru</h1>
                    <p>Gunakan data yang aktif agar admin dapat memverifikasi akun Anda.</p>
                </div>
                <form class="auth-screen__fields" @submit.prevent="submitRegister">
                    <label>Nama lengkap <input v-model="form.name" type="text" autocomplete="name" placeholder="Masukkan nama lengkap" required /></label>
                    <label>Email <input v-model="form.email" type="email" autocomplete="email" placeholder="nama@kampus.ac.id" required /></label>
                    <label>Jenis pengguna <select v-model="form.user_type" required><option value="mahasiswa">Mahasiswa</option><option value="dosen">Dosen</option><option value="staf">Staf</option></select></label>
                    <label>Password
                        <span class="auth-screen__password"><input v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Minimal 8 karakter" minlength="8" required /><button type="button" class="auth-screen__password-toggle" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" @click="showPassword = !showPassword"><EyeOff v-if="showPassword" :size="17" /><Eye v-else :size="17" /></button></span>
                    </label>
                    <label>Konfirmasi password
                        <span class="auth-screen__password"><input v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" autocomplete="new-password" placeholder="Ulangi password" minlength="8" required /><button type="button" class="auth-screen__password-toggle" :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'" :title="showPasswordConfirmation ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'" @click="showPasswordConfirmation = !showPasswordConfirmation"><EyeOff v-if="showPasswordConfirmation" :size="17" /><Eye v-else :size="17" /></button></span>
                    </label>
                    <p class="auth-screen__helper">Password minimal 8 karakter.</p>
                    <p v-if="errorMessage" class="auth-screen__message is-error" role="alert">{{ errorMessage }}</p>
                    <p v-if="successMessage" class="auth-screen__message is-success" role="status">{{ successMessage }}</p>
                    <button type="submit" class="auth-screen__submit" :disabled="isSubmitting"><LoaderCircle v-if="isSubmitting" class="auth-screen__spin" :size="17" />{{ isSubmitting ? 'Mendaftar...' : 'Daftar' }}</button>
                </form>
                <p class="auth-screen__switch">Sudah punya akun? <RouterLink :to="{ name: 'login' }">Masuk</RouterLink></p>
            </div>
        </section>
    </main>
</template>
