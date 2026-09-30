<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import campusImage from '../../asset/Undip.png';

const router = useRouter();
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    user_type: 'mahasiswa',
});
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

async function submitRegister() {
    isSubmitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await axios.post('/api/register', form.value);
        successMessage.value = 'Registrasi berhasil. Tunggu verifikasi admin untuk bisa login.';

        setTimeout(() => {
            router.push({ name: 'login' });
        }, 1200);
    } catch (error) {
        const validationErrors = error?.response?.data?.errors;
        const firstMessage = validationErrors
            ? Object.values(validationErrors)[0]?.[0]
            : error?.response?.data?.message;

        errorMessage.value = firstMessage || 'Registrasi gagal. Silakan coba lagi.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div class="auth-shell">
        <div class="auth-panel">
            <div class="auth-illustration">
                <img :src="campusImage" alt="Kampus Universitas Diponegoro" class="auth-campus-image" />
                <div class="illustration-overlay">
                    <span class="illustration-badge">RUANGKITA / UNDIP</span>
                    <h2>Buat akun dan mulai kelola kebutuhan kampus.</h2>
                    <p>Daftarkan diri Anda untuk mengajukan reservasi fasilitas, mengirim laporan, dan mengikuti status aktivitas kampus.</p>
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-header">
                    <div class="brand-badge">RK</div>
                    <h1>Buat akun baru</h1>
                    <p>Daftar untuk mengajukan reservasi dan laporan fasilitas.</p>
                </div>

                <form class="auth-form" @submit.prevent="submitRegister">
                    <label>
                        <span>Nama Lengkap</span>
                        <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap" required />
                    </label>

                    <label>
                        <span>Email</span>
                        <input v-model="form.email" type="email" placeholder="nama@kampus.ac.id" required />
                    </label>

                    <label>
                        <span>Jenis Pengguna</span>
                        <select v-model="form.user_type" required>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                            <option value="staf">Staf</option>
                        </select>
                    </label>

                    <label>
                        <span>Password</span>
                        <input v-model="form.password" type="password" placeholder="Minimal 8 karakter" required />
                    </label>

                    <label>
                        <span>Konfirmasi Password</span>
                        <input v-model="form.password_confirmation" type="password" placeholder="Ulangi password" required />
                    </label>

                    <div v-if="errorMessage" class="error-box">{{ errorMessage }}</div>
                    <div v-if="successMessage" class="success-box">{{ successMessage }}</div>

                    <button type="submit" class="primary-button" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Mendaftar...' : 'Daftar' }}
                    </button>
                </form>

                <div class="auth-footer">
                    <span>Sudah punya akun?</span>
                    <RouterLink :to="{ name: 'login' }">Masuk</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-shell {
    min-height: 100vh;
    display: grid;
    place-items: center;
    background:
        radial-gradient(circle at top left, rgba(96, 165, 250, 0.18), transparent 30%),
        radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.12), transparent 25%),
        #f8fafc;
    padding: 32px 16px;
}

.auth-panel {
    width: min(100%, 1100px);
    min-height: 680px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 28px;
    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.10);
    display: grid;
    grid-template-columns: 1.08fr 0.92fr;
    overflow: hidden;
    backdrop-filter: blur(12px);
}

.auth-illustration {
    padding: 52px 42px;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 45%, #e0f2fe 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
}

.auth-illustration::before {
    content: "";
    position: absolute;
    inset: 28px;
    border: 1px solid rgba(37, 99, 235, 0.12);
    border-radius: 22px;
}

.illustration-badge {
    position: relative;
    z-index: 1;
    align-self: flex-start;
    background: rgba(255, 255, 255, 0.6);
    color: #1d4ed8;
    border: 1px solid rgba(37, 99, 235, 0.16);
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.illustration-circle {
    position: relative;
    z-index: 1;
    width: min(100%, 430px);
    height: 300px;
    margin: 28px auto 20px;
    border-radius: 32px;
    background: linear-gradient(180deg, rgba(255,255,255,0.25), rgba(255,255,255,0.05));
    border: 1px solid rgba(255,255,255,0.4);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);
    display: grid;
    place-items: center;
}

.mini-card {
    position: absolute;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(148, 163, 184, 0.25);
    border-radius: 16px;
    padding: 12px 16px;
    font-weight: 700;
    color: #0f172a;
    box-shadow: 0 18px 40px rgba(37, 99, 235, 0.08);
}

.mini-card.top {
    top: 28px;
    left: 26px;
}

.mini-card.mid {
    top: 110px;
    right: 26px;
}

.mini-card.bottom {
    bottom: 30px;
    left: 84px;
}

.auth-illustration h2 {
    position: relative;
    z-index: 1;
    margin: 0;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.1;
    color: #0f172a;
}

.auth-illustration p {
    position: relative;
    z-index: 1;
    margin-top: 14px;
    max-width: 500px;
    color: #475569;
    font-size: 1rem;
    line-height: 1.7;
}

.auth-card {
    background: #ffffff;
    padding: 52px 42px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-header {
    text-align: left;
    margin-bottom: 24px;
}

.brand-badge {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: #ffffff;
    border: 1.5px solid rgba(37, 99, 235, 0.12);
    box-shadow: 0 4px 16px rgba(37, 99, 235, 0.10);
    padding: 8px;
    margin-bottom: 18px;
}

.brand-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.auth-header h1 {
    margin: 0;
    font-size: clamp(1.9rem, 2.8vw, 2.5rem);
    color: #0f172a;
}

.auth-header p {
    margin: 10px 0 0;
    color: #64748b;
    font-size: 0.98rem;
}

.auth-form {
    display: grid;
    gap: 18px;
}

.auth-form label {
    display: grid;
    gap: 8px;
    color: #1e293b;
    font-weight: 600;
}

.auth-form input,
.auth-form select {
    border: 1px solid #dbe3ee;
    border-radius: 12px;
    padding: 13px 14px;
    font: inherit;
    background: #f8fafc;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.auth-form input:focus,
.auth-form select:focus {
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    outline: none;
}

.error-box,
.success-box {
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.92rem;
}

.error-box {
    border: 1px solid rgba(185, 28, 28, 0.22);
    background: rgba(254, 242, 242, 0.92);
    color: #991b1b;
}

.success-box {
    border: 1px solid rgba(22, 163, 74, 0.2);
    background: rgba(236, 253, 245, 0.9);
    color: #166534;
}

.primary-button {
    margin-top: 8px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    font-weight: 700;
    font-size: 1rem;
    padding: 14px 16px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    box-shadow: 0 16px 22px rgba(37, 99, 235, 0.18);
}

.primary-button:hover {
    transform: translateY(-1px);
}

.primary-button:disabled {
    opacity: 0.72;
    cursor: wait;
}

.auth-footer {
    margin-top: 22px;
    display: flex;
    justify-content: center;
    gap: 8px;
    color: #475569;
    font-size: 0.95rem;
}

.auth-footer a {
    color: #1d4ed8;
    font-weight: 700;
    text-decoration: none;
}

@media (max-width: 900px) {
    .auth-panel {
        grid-template-columns: 1fr;
    }

    .auth-illustration {
        min-height: 260px;
        padding: 32px 24px;
    }

    .auth-card {
        padding: 28px 22px 32px;
    }
}

@media (min-width: 901px) {
    .auth-shell { padding: 24px 16px; }
    .auth-panel { width: min(900px, 100%); min-height: 560px; border-radius: 14px; }
    .auth-illustration { min-height: 560px; }
    .auth-card { padding: 36px 34px; }
    .auth-header { margin-bottom: 18px; }
    .brand-badge { width: 76px; height: 76px; margin-bottom: 12px; }
    .auth-header h1 { font-size: 2rem; }
    .auth-form { gap: 12px; }
}

@media (max-width:700px){
    .auth-shell{
        min-height:100dvh;
        padding:24px 14px;
    }

    .auth-panel{
        width:min(100%, 460px);
        min-height:0;
        grid-template-columns:1fr;
        border-radius:24px;
    }

    .auth-illustration{
        display:none;
    }

    .auth-card{
        padding:36px 24px 30px;
    }

    .auth-header h1{
        font-size:34px;
        line-height:1.08;
    }
}

@media (max-width:380px){
    .auth-shell{
        padding:16px 10px;
    }

    .auth-card{
        padding:28px 18px 24px;
    }

    .auth-header h1{
        font-size:30px;
    }
}

.auth-panel {
    grid-template-columns: .9fr 1.1fr;
}

.auth-illustration {
    position: relative;
    min-height: 680px;
    padding: 0;
    overflow: hidden;
    grid-column: 2;
    grid-row: 1;
    background: #0f172a;
}

.auth-campus-image {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.auth-illustration::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, .08), rgba(15, 23, 42, .78));
}

.illustration-overlay {
    position: absolute;
    z-index: 1;
    left: 42px;
    right: 42px;
    bottom: 42px;
    color: #fff;
}

.illustration-overlay h2 {
    margin: 20px 0 0;
    color: #fff;
}

.illustration-overlay p {
    color: rgba(255, 255, 255, .82);
}

.illustration-badge {
    background: rgba(255, 255, 255, .16);
    border-color: rgba(37, 99, 235, .3);
    color: #fff;
}

.auth-card {
    grid-column: 1;
    grid-row: 1;
}

@media (max-width: 900px) {
    .auth-illustration {
        min-height: 260px;
        grid-column: 1;
        grid-row: 1;
    }

    .auth-campus-image {
        min-height: 260px;
    }

    .illustration-overlay {
        left: 24px;
        right: 24px;
        bottom: 24px;
    }

    .illustration-overlay h2,
    .illustration-overlay p {
        display: none;
    }

    .auth-card {
        grid-column: 1;
        grid-row: 2;
    }
}
@media (min-width: 901px) {
    .auth-shell { height: 100dvh; min-height: 0; padding: 16px; overflow: hidden; }
    .auth-panel { width: min(820px, calc(100vw - 32px)); height: min(580px, calc(100dvh - 32px)); min-height: 0; }
    .auth-illustration { min-height: 0; }
    .auth-card { padding: 24px 30px; overflow: hidden; }
    .auth-header { margin-bottom: 12px; }
    .brand-badge { width: 62px; height: 62px; margin-bottom: 8px; }
    .auth-header h1 { font-size: 1.8rem; }
    .auth-header p { margin-top: 5px; font-size: .86rem; }
    .auth-form { gap: 8px; }
    .auth-form input, .auth-form select { padding: 9px 11px; }
    .primary-button { margin-top: 2px; padding: 10px 14px; }
    .auth-footer { margin-top: 12px; }
}

@media (min-width: 901px) {
    .auth-shell { padding: 10px; }
    .auth-panel { width: min(660px, calc(100vw - 20px)); height: min(500px, calc(100dvh - 20px)); min-height: 0; }
    .auth-illustration { min-height: 0; }
    .auth-card { padding: 16px 20px; overflow: hidden; }
    .auth-header { margin-bottom: 12px; }
    .brand-badge { width: 48px; height: 48px; margin-bottom: 5px; }
    .auth-header h1 { font-size: 1.45rem; }
    .auth-header p { margin-top: 3px; font-size: .72rem; }
    .auth-form { gap: 5px; }
    .auth-form label { gap: 3px; font-size: .7rem; }
    .auth-form input, .auth-form select { padding: 5px 8px; font-size: .72rem; }
    .primary-button { margin-top: 2px; padding: 7px 11px; font-size: .75rem; }
    .auth-footer { margin-top: 8px; font-size: .7rem; }
}
</style>
