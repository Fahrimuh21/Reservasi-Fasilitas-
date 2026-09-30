<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ArrowRight, Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import campusImage from '../../asset/Undip.png';
import { setAuthSession } from '../auth';

const router = useRouter();
const form = ref({ email: '', password: '' });
const isSubmitting = ref(false);
const errorMessage = ref('');
const showPassword = ref(false);

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
  <main class="auth-screen">
    <section class="auth-screen__panel">
      <div class="auth-screen__media">
        <img :src="campusImage" alt="Kampus Universitas Diponegoro" />
        <div class="auth-screen__media-copy"><span>RUANGKITA / UNDIP</span><h2>Fasilitas kampus, lebih dekat dan teratur.</h2><p>Satu ruang digital untuk mencari, memesan, dan memantau penggunaan fasilitas.</p></div>
      </div>
      <div class="auth-screen__form">
        <RouterLink :to="{ name: 'landing' }" class="auth-screen__brand">RUANGKITA</RouterLink>
        <div class="auth-screen__heading">
          <span class="auth-screen__eyebrow">AKSES CIVITAS AKADEMIKA</span>
          <h1>Selamat datang kembali</h1>
          <p>Masuk untuk melanjutkan reservasi dan pengelolaan fasilitas kampus.</p>
        </div>
        <form class="auth-screen__fields" @submit.prevent="submitLogin">
          <label>Email <input v-model="form.email" type="email" autocomplete="email" placeholder="nama@kampus.ac.id" required /></label>
          <label>Password
            <span class="auth-screen__password">
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="Masukkan password" required />
              <button type="button" class="auth-screen__password-toggle" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" @click="showPassword = !showPassword">
                <EyeOff v-if="showPassword" :size="17" /><Eye v-else :size="17" />
              </button>
            </span>
          </label>
          <p v-if="errorMessage" class="auth-screen__message is-error" role="alert">{{ errorMessage }}</p>
          <button class="auth-screen__submit" type="submit" :disabled="isSubmitting">
            <LoaderCircle v-if="isSubmitting" class="auth-screen__spin" :size="17" />
            <ArrowRight v-else :size="17" />
            {{ isSubmitting ? 'Memproses...' : 'Masuk' }}
          </button>
        </form>
        <RouterLink :to="{ name: 'landing' }" class="auth-screen__back">Kembali ke halaman utama</RouterLink>
        <p class="auth-screen__switch">Belum punya akun? <RouterLink :to="{ name: 'register' }">Daftar sekarang</RouterLink></p>
      </div>
    </section>
  </main>
</template>
