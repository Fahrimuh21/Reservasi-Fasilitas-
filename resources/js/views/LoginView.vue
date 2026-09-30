<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ArrowRight } from 'lucide-vue-next';
import campusImage from '../../asset/Undip.png';
import { setAuthSession } from '../auth';

const router = useRouter();
const form = ref({ email: '', password: '' });
const isSubmitting = ref(false);
const errorMessage = ref('');

async function submitLogin() {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    errorMessage.value = '';
    try {
        const response = await axios.post('/api/login', form.value);
        setAuthSession(response.data.token, response.data.user);
        const destination = { admin: 'admin', officer: 'officer', user: 'facilities' };
        await router.push({ name: destination[response.data.user.role] || 'facilities' });
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || 'Login gagal. Periksa email dan password.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-panel">
      <div class="auth-form-panel">
        <RouterLink :to="{ name: 'landing' }" class="auth-brand">RUANGKITA</RouterLink>
        <div class="auth-heading">
          <span class="auth-eyebrow">AKSES CIVITAS AKADEMIKA</span>
          <h1>Selamat datang kembali</h1>
          <p>Masuk untuk melanjutkan reservasi dan pengelolaan fasilitas kampus.</p>
        </div>
        <form @submit.prevent="submitLogin">
          <label>Email<input v-model="form.email" type="email" placeholder="nama@kampus.ac.id" required /></label>
          <label>Password<input v-model="form.password" type="password" placeholder="Masukkan password" required /></label>
          <p v-if="errorMessage" class="auth-error" role="alert">{{ errorMessage }}</p>
          <button class="auth-submit" type="submit" :disabled="isSubmitting">{{ isSubmitting ? 'Memproses...' : 'Masuk' }}<ArrowRight :size="17" /></button>
        </form>
        <RouterLink :to="{ name: 'landing' }" class="guest-link">Kembali ke halaman utama</RouterLink>
        <p class="auth-switch">Belum punya akun? <RouterLink :to="{ name: 'register' }">Daftar sekarang</RouterLink></p>
      </div>
      <div class="auth-image-panel">
        <img :src="campusImage" alt="Kampus Universitas Diponegoro" />
        <div class="auth-image-copy"><span>RUANGKITA / UNDIP</span><h2>Fasilitas kampus, lebih dekat dan teratur.</h2><p>Satu ruang digital untuk mencari, memesan, dan memantau penggunaan fasilitas.</p></div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.auth-page { min-height: 100vh; display: grid; place-items: center; padding: 32px; background: #f8fafc; font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; color: #0f172a; }
.auth-panel { width: min(900px, 100%); min-height: 560px; display: grid; grid-template-columns: .9fr 1.1fr; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 24px 70px rgba(15,23,42,.12); }
.auth-form-panel { display: flex; flex-direction: column; justify-content: center; padding: 38px 42px; }
.auth-brand { margin-bottom: 42px; color: #2563eb; font-size: 16px; font-weight: 800; letter-spacing: .1em; text-decoration: none; }
.auth-eyebrow { color: #2563eb; font-size: 11px; font-weight: 800; letter-spacing: .1em; }
.auth-heading h1 { margin: 13px 0 10px; font-size: 32px; line-height: 1.08; letter-spacing: -.04em; }
.auth-heading p { margin: 0 0 32px; color: #64748b; line-height: 1.6; font-size: 14px; }
form { display: grid; gap: 17px; }
label { display: grid; gap: 8px; color: #334155; font-size: 13px; font-weight: 700; }
input { min-height: 48px; padding: 0 14px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #0f172a; font: inherit; }
input:focus { outline: 3px solid #dbeafe; border-color: #2563eb; }
.auth-error { margin: 0; padding: 10px 12px; border-radius: 7px; background: #fef2f2; color: #b91c1c; font-size: 12px; }
.auth-submit { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 0; border-radius: 7px; background: #2563eb; color: #fff; font: inherit; font-weight: 800; cursor: pointer; }
.auth-submit:hover { background: #1d4ed8; }
.auth-submit:disabled { opacity: .7; cursor: wait; }
.guest-link { margin-top: 14px; color: #2563eb; font-size: 12px; font-weight: 700; text-align: center; text-decoration: none; }
.auth-switch { margin: 34px 0 0; color: #64748b; font-size: 12px; text-align: center; }
.auth-switch a { color: #2563eb; font-weight: 800; text-decoration: none; }
.auth-image-panel { position: relative; min-height: 560px; overflow: hidden; background: #1e3a8a; }
.auth-image-panel img { width: 100%; height: 100%; display: block; object-fit: cover; }
.auth-image-panel::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,.05), rgba(15,23,42,.75)); }
.auth-image-copy { position: absolute; z-index: 1; right: 48px; bottom: 48px; left: 48px; color: #fff; }
.auth-image-copy span { font-size: 11px; font-weight: 800; letter-spacing: .1em; }
.auth-image-copy h2 { max-width: 470px; margin: 16px 0 10px; font-size: 36px; line-height: 1.08; letter-spacing: -.04em; }
.auth-image-copy p { max-width: 440px; margin: 0; color: #dbeafe; line-height: 1.6; font-size: 13px; }
@media (max-width: 760px) { .auth-page { padding: 16px; } .auth-panel { grid-template-columns: 1fr; } .auth-image-panel { min-height: 240px; grid-row: 1; } .auth-image-copy { right: 24px; bottom: 24px; left: 24px; } .auth-image-copy h2, .auth-image-copy p { display: none; } .auth-form-panel { padding: 32px 24px; grid-row: 2; } .auth-brand { margin-bottom: 42px; } }
.auth-page { height: 100dvh; min-height: 0; padding: 16px; overflow: hidden; }
.auth-panel { width: min(820px, calc(100vw - 32px)); height: min(560px, calc(100dvh - 32px)); min-height: 0; }
.auth-form-panel { padding: 28px 34px; }
.auth-brand { margin-bottom: 28px; }
.auth-heading h1 { font-size: 29px; }
.auth-heading p { margin-bottom: 22px; }
form { gap: 12px; }
input, .auth-submit { min-height: 42px; }
.auth-switch { margin-top: 20px; }
.auth-image-panel { min-height: 0; }
.auth-image-copy { right: 32px; bottom: 32px; left: 32px; }
.auth-image-copy h2 { font-size: 29px; }
@media (max-width: 760px) { .auth-page { height: auto; min-height: 100dvh; overflow: auto; } .auth-panel { width: min(100%, 460px); height: auto; } }

@media (min-width: 761px) {
  .auth-page { padding: 10px; }
  .auth-panel { width: min(620px, calc(100vw - 20px)); height: min(440px, calc(100dvh - 20px)); }
  .auth-form-panel { padding: 18px 22px; }
  .auth-brand { margin-bottom: 16px; font-size: 13px; }
  .auth-heading h1 { font-size: 22px; }
  .auth-heading p { margin-bottom: 12px; font-size: 11px; }
  form { gap: 7px; }
  label { gap: 5px; font-size: 11px; }
  input, .auth-submit { min-height: 32px; font-size: 11px; }
  .guest-link { margin-top: 6px; font-size: 10px; }
  .auth-switch { margin-top: 10px; font-size: 10px; }
  .auth-image-copy { right: 24px; bottom: 24px; left: 24px; }
  .auth-image-copy h2 { margin-top: 8px; font-size: 21px; }
}
</style>
