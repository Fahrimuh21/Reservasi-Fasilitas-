<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router';
import { CalendarPlus, Building2, Sparkles, Bell, Clock } from 'lucide-vue-next';

const currentTime = ref('');
const currentDate = ref('');

let timer;
const updateTime = () => {
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  currentTime.value = `${hours}:${minutes}`;
  
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 10000);
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <div class="rk-page rk-grid">
    <header class="w-full pt-5 relative z-50">
      <div class="rk-shell">
        <nav class="flex items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-white/85 px-4 py-3 shadow-sm backdrop-blur-md sm:px-5" aria-label="Navigasi utama">
          
          <div class="flex items-center gap-4">
            <RouterLink :to="{ name: 'landing' }" class="rk-focus shrink-0 rounded-lg"> 
              <span class="rk-brand inline-flex rounded-full bg-white px-3 py-2 text-xs font-extrabold text-blue-600">RUANGKITA</span> 
            </RouterLink>
            
            <div class="hidden items-center gap-2 rounded-full border border-green-100 bg-green-50 px-2.5 py-1 lg:flex" title="Sistem Operasional">
              <span class="relative flex h-2.5 w-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
              </span>
              <span class="text-[11px] font-bold uppercase tracking-wider text-green-700">Sistem Online</span>
            </div>
          </div>
          
          <div class="hidden md:flex items-center gap-2 rounded-full bg-slate-50 px-4 py-1.5 border border-slate-100">
            <Clock :size="15" class="text-slate-400" />
            <span class="text-sm font-medium text-slate-500">{{ currentDate }} <span class="mx-1.5 text-slate-300">|</span> <span class="font-bold text-slate-700">{{ currentTime }}</span></span>
          </div>

          <div class="flex items-center gap-2">
            <RouterLink :to="{ name: 'login' }" class="rk-btn rk-btn-primary rk-focus hidden bg-blue-600 px-4 py-2 text-sm text-white sm:inline-flex">Masuk</RouterLink> 
            <RouterLink :to="{ name: 'register' }" class="rk-btn rk-btn-outline rk-focus bg-white px-4 py-2 text-sm text-blue-600">Daftar</RouterLink>
          </div>
          
        </nav>
      </div>
    </header>
    
    <main class="h-full overflow-y-auto pb-20">
      <section id="beranda" class="rk-hero" aria-labelledby="hero-title">
        <div class="rk-shell">
          <div class="mx-auto max-w-4xl flex flex-col items-center text-center">
            
            <div class="rk-pill rk-reveal inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-extrabold uppercase text-blue-600 shadow-sm">
              <Sparkles :size="14" aria-hidden="true" />
              <span>RUANGKITA</span>
            </div>
            
            <h1 class="rk-heading rk-reveal rk-delay mt-5 max-w-2xl font-extrabold text-blue-950 text-[42px] leading-[1.1] text-balance">
              Kelola fasilitas kampus lebih mudah.
            </h1>
            
            <p class="rk-reveal rk-delay mt-6 max-w-2xl leading-[1.7] text-slate-500 text-base">
              Reservasi ruang, pantau status fasilitas, dan laporkan kerusakan dengan satu platform.
            </p>
            
            <div class="rk-reveal rk-delay-two mt-8 flex flex-col justify-center gap-3 sm:flex-row">
              <RouterLink :to="{ name: 'login' }" class="rk-btn rk-btn-primary rk-focus bg-blue-600 text-white">
                <CalendarPlus :size="19" aria-hidden="true" />
                <span>Mulai Reservasi</span>
              </RouterLink>
              <RouterLink :to="{ name: 'facilities' }" class="rk-btn rk-btn-outline rk-focus bg-white text-blue-600">
                <Building2 :size="19" aria-hidden="true" />
                <span>Lihat Fasilitas</span>
              </RouterLink>
            </div>
            
            <div class="rk-mockup-wrap rk-reveal rk-delay-two w-full" aria-label="Contoh tampilan reservasi RuangKita">
              <div class="rk-mockup">
                <div class="rk-window-top">
                  <div class="rk-dot-row" aria-hidden="true">
                    <span class="rk-tiny-dot"></span><span class="rk-tiny-dot"></span><span class="rk-tiny-dot"></span>
                  </div>
                  <div class="text-xs font-bold text-slate-500">app.ruangkita.com</div>
                  <Bell :size="16" class="text-blue-600" aria-label="Notifikasi" />
                </div>
                
                <div class="rk-mini-card one">
                  <div class="rk-chip">
                    <span aria-hidden="true">📅</span>
                    <span>Reservasi</span>
                  </div>
                </div>
                
                <article class="rk-reservation-card">
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <p class="text-xs font-bold uppercase tracking-wider text-blue-600">FASILITAS</p>
                      <h2 class="mt-1 font-extrabold text-blue-950 text-xl">Auditorium Utama</h2>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">Disetujui</span>
                  </div>
                  <div class="mt-5 grid grid-cols-2 gap-3 text-left">
                    <div class="rounded-xl bg-blue-50 p-3">
                      <p class="text-xs font-semibold text-slate-500">Waktu</p>
                      <p class="mt-1 text-sm font-extrabold text-blue-950">08:00 - 10:00</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-3">
                      <p class="text-xs font-semibold text-slate-500">Kapasitas</p>
                      <p class="mt-1 text-sm font-extrabold text-blue-950">500 Orang</p>
                    </div>
                  </div>
                </article>
                
                <div class="rk-mini-card two">
                  <div class="rk-chip">
                    <span aria-hidden="true">✨</span>
                    <span>Fasilitas</span>
                  </div>
                </div>
                
                <div class="absolute bottom-5 left-1/2 z-10 -translate-x-1/2">
                  <div class="rk-chip">
                    <span aria-hidden="true">✓</span>
                    <span>Cek Status</span>
                  </div>
                </div>
                
              </div>
            </div>
            
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
    :root {
      --rk-blue: #2563eb;
      --rk-blue-dark: #1d4ed8;
      --rk-blue-soft: #eff6ff;
      --rk-line: #dbeafe;
      --rk-ink: #172554;
      --rk-body: #475569;
      --rk-shadow: 0 22px 55px rgba(37, 99, 235, 0.15);
      --rk-shadow-soft: 0 12px 30px rgba(37, 99, 235, 0.10);
    }

    .rk-page {
      width: 100%;
      height: 100dvh;
      overflow: hidden;
      position: fixed;
      inset: 0;
      isolation: isolate;
      color: #172554;
      background-color: #ffffff;
    }

    .rk-grid {
      background-image: radial-gradient(rgba(37, 99, 235, 0.08) 1px, transparent 1px);
      background-size: 30px 30px;
      background-position: center top;
    }

    .rk-shell {
      width: 100%;
      max-width: 1180px;
      margin: 0 auto;
      padding: 0 24px;
    }

    .rk-focus:focus-visible {
      outline: 3px solid rgba(37, 99, 235, 0.42);
      outline-offset: 4px;
    }

    .rk-heading {
      letter-spacing: -0.055em;
      line-height: 1.03;
    }

    .rk-brand {
      letter-spacing: 0.13em;
      box-shadow: 0 7px 18px rgba(37, 99, 235, 0.14);
    }

    .rk-pill {
      letter-spacing: 0.12em;
    }

    .rk-nav-link {
      color: #475569;
      font-size: 0.875rem;
      font-weight: 700;
      text-decoration: none;
      transition: color 180ms ease;
    }

    .rk-nav-link:hover { color: #2563eb; }

    .rk-btn {
      min-height: 50px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.55rem;
      border-radius: 0.9rem;
      padding: 0.78rem 1.2rem;
      font-weight: 700;
      text-decoration: none;
      transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
    }

    .rk-btn:hover {
      transform: translateY(-2px);
    }

    .rk-btn-primary {
      box-shadow: 0 12px 24px rgba(37, 99, 235, 0.26);
    }

    .rk-btn-primary:hover {
      box-shadow: 0 16px 30px rgba(37, 99, 235, 0.32);
    }

    .rk-btn-outline {
      border: 1px solid #bfdbfe;
      box-shadow: 0 8px 20px rgba(37, 99, 235, 0.06);
    }

    .rk-hero {
      position: relative;
      padding: 4.5rem 0 9.5rem;
    }

    .rk-hero::before,
    .rk-hero::after {
      content: "";
      position: absolute;
      z-index: -1;
      border-radius: 999px;
      filter: blur(16px);
      pointer-events: none;
    }

    .rk-hero::before {
      width: 360px;
      height: 360px;
      top: -150px;
      right: -130px;
      background: rgba(147, 197, 253, 0.30);
    }

    .rk-hero::after {
      width: 300px;
      height: 300px;
      bottom: 20px;
      left: -170px;
      background: rgba(191, 219, 254, 0.38);
    }

    .rk-mockup-wrap {
      height: 335px;
      max-width: 770px;
      margin: 3.5rem auto -12.6rem;
      position: relative;
      overflow: hidden;
      border-radius: 2rem 2rem 0 0;
      border: 1px solid rgba(191, 219, 254, 0.92);
      box-shadow: 0 24px 65px rgba(37, 99, 235, 0.15);
    }

    .rk-mockup {
      min-height: 430px;
      padding: 1.35rem;
      position: relative;
      background: linear-gradient(145deg, #dbeafe 0%, #eff6ff 48%, #ffffff 100%);
    }

    .rk-mockup::before {
      content: "";
      position: absolute;
      width: 230px;
      height: 230px;
      top: -92px;
      right: -42px;
      border-radius: 999px;
      background: rgba(96, 165, 250, 0.22);
      filter: blur(15px);
    }

    .rk-window-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      z-index: 1;
      padding: 0.78rem 0.9rem;
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.76);
      border: 1px solid rgba(255, 255, 255, 0.92);
    }

    .rk-dot-row { display: flex; gap: 5px; }
    .rk-tiny-dot { width: 7px; height: 7px; border-radius: 50%; background: #93c5fd; }
    .rk-tiny-dot:first-child { background: #2563eb; }

    .rk-reservation-card {
      width: min(100%, 450px);
      position: relative;
      z-index: 2;
      margin: 1.45rem auto 0;
      padding: 1.35rem;
      border: 1px solid #dbeafe;
      border-radius: 1.35rem;
      background: rgba(255, 255, 255, 0.94);
      box-shadow: 0 12px 30px rgba(37, 99, 235, 0.10);
    }

    .rk-mini-card {
      position: absolute;
      z-index: 3;
      padding: 0.8rem 0.9rem;
      border: 1px solid rgba(219, 234, 254, 0.96);
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 12px 25px rgba(37, 99, 235, 0.12);
    }

    .rk-mini-card.one { top: 96px; left: 5%; }
    .rk-mini-card.two { right: 5%; bottom: 78px; }

    .rk-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.42rem;
      padding: 0.55rem 0.72rem;
      border: 1px solid rgba(219, 234, 254, 0.95);
      border-radius: 999px;
      background: #ffffff;
      box-shadow: 0 8px 18px rgba(37, 99, 235, 0.09);
      color: #172554;
      font-size: 0.75rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .rk-glass {
      background: rgba(255, 255, 255, 0.68);
      border: 1px solid rgba(255, 255, 255, 0.95);
      box-shadow: 0 18px 42px rgba(37, 99, 235, 0.12);
    }

    @supports (backdrop-filter: blur(1px)) {
      .rk-glass { backdrop-filter: blur(15px); }
    }

    .rk-stat-strip {
      position: relative;
      z-index: 3;
      margin-top: 7.9rem;
      border-radius: 1.35rem;
      padding: 0.7rem;
    }

    .rk-stat {
      min-height: 96px;
      padding: 1rem;
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.55);
    }

    .rk-card {
      border: 1px solid #dbeafe;
      border-radius: 1.5rem;
      box-shadow: 0 12px 30px rgba(37, 99, 235, 0.10);
      transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .rk-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 22px 55px rgba(37, 99, 235, 0.15);
    }

    .rk-icon-box {
      width: 46px;
      height: 46px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.9rem;
      background: #eff6ff;
      color: #2563eb;
    }

    .rk-step-number {
      width: 34px;
      height: 34px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      background: #2563eb;
      color: #ffffff;
      font-size: 0.875rem;
      font-weight: 800;
      box-shadow: 0 8px 16px rgba(37, 99, 235, 0.22);
    }

    .rk-role {
      border: 1px solid #dbeafe;
      border-radius: 1rem;
      background: #ffffff;
      box-shadow: 0 8px 18px rgba(37, 99, 235, 0.06);
    }

    .rk-reveal {
      animation: rkReveal 650ms ease both;
    }

    .rk-delay { animation-delay: 120ms; }
    .rk-delay-two { animation-delay: 210ms; }

    @keyframes rkReveal {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 767px) {
      .rk-shell { padding: 0 18px; }
      .rk-hero { padding-top: 3.4rem; padding-bottom: 8rem; }
      .rk-mockup-wrap { height: 280px; margin-top: 2.5rem; margin-bottom: -10.6rem; }
      .rk-mockup { min-height: 400px; padding: 1rem; }
      .rk-mini-card.one { left: -15px; }
      .rk-mini-card.two { right: -18px; }
      .rk-stat-strip { margin-top: 6.3rem; }
    }
</style>
