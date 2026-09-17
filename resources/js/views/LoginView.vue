<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { setAuthSession } from '../auth';

const router = useRouter();
const form = ref({
    email: '',
    password: '',
});
const isSubmitting = ref(false);
const errorMessage = ref('');

async function submitLogin() {
    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post('/api/login', form.value);
        const { token, user } = response.data;

        setAuthSession(token, user);

        if (user.role === 'admin') {
            router.push({ name: 'admin' });
            return;
        }

        if (user.role === 'officer') {
            router.push({ name: 'officer' });
            return;
        }

        router.push({ name: 'facilities' });
    } catch (error) {
        const message = error?.response?.data?.message
            || error?.response?.data?.errors?.account?.[0]
            || 'Login gagal. Silakan cek email dan password Anda.';

        errorMessage.value = message;
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div class="auth-shell">
        <div class="auth-panel">
            <div class="auth-illustration">
                <div class="illustration-badge">RuangKita</div>
                <div class="illustration-circle">
                    <div class="mini-card top">📅 Reservasi</div>
                    <div class="mini-card mid">✅ Cek Status</div>
                    <div class="mini-card bottom">📍 Fasilitas</div>
                </div>
                <h2>Kelola fasilitas kampus lebih mudah.</h2>
                <p>Reservasi ruang, pantau status fasilitas, dan laporkan kerusakan dengan satu platform.</p>
            </div>

            <div class="auth-card">
                <div class="auth-header">
                    <div class="brand-badge">R</div>
                    <h1>Masuk ke RuangKita</h1>
                    <p>Kelola reservasi dan laporan fasilitas kampus.</p>
                </div>

                <form class="auth-form" @submit.prevent="submitLogin">
                    <label>
                        <span>Email</span>
                        <input v-model="form.email" type="email" placeholder="nama@kampus.ac.id" required />
                    </label>

                    <label>
                        <span>Password</span>
                        <input v-model="form.password" type="password" placeholder="Masukkan password" required />
                    </label>

                    <div v-if="errorMessage" class="error-box">{{ errorMessage }}</div>

                    <button type="submit" class="primary-button" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>

                <div class="auth-footer">
                    <span>Belum punya akun?</span>
                    <RouterLink :to="{ name: 'register' }">Daftar sekarang</RouterLink>
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
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    font-weight: 800;
    font-size: 24px;
    margin-bottom: 18px;
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

.auth-form input {
    border: 1px solid #dbe3ee;
    border-radius: 12px;
    padding: 13px 14px;
    font: inherit;
    background: #f8fafc;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.auth-form input:focus {
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    outline: none;
}

.error-box {
    border: 1px solid rgba(185, 28, 28, 0.22);
    background: rgba(254, 242, 242, 0.92);
    color: #991b1b;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.92rem;
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
</style>
