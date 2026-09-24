<template>
  <div class="rk-page rk-grid">
    <header class="w-full pt-5">
      <div class="rk-shell">
        <nav class="flex items-center justify-between gap-4 rounded-2xl border border-blue-100 bg-white/85 px-4 py-3 shadow-sm backdrop-blur-md sm:px-5" aria-label="Navigasi utama">
          <div class="flex items-center gap-3">
            <span class="rk-brand inline-flex rounded-lg bg-blue-600 px-3 py-2 text-xs font-extrabold text-white">
              RuangKita
            </span>
            <div class="hidden items-center gap-2 sm:flex">
              <span class="rk-dot" aria-hidden="true"></span> 
              <span class="text-xs font-semibold text-slate-500">Sistem Online</span>
            </div>
          </div>
          <router-link :to="{ name: 'login' }" class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 transition-colors">
            <Building2 :size="14" aria-hidden="true" />
            <span>Masuk Portal</span>
          </router-link>
        </nav>
      </div>
    </header>

    <main class="flex min-h-[calc(100vh-92px)] items-center py-12 sm:py-16">
      <section class="w-full" aria-labelledby="hero-title">
        <div class="rk-shell">
          <div class="grid items-center gap-12 lg:grid-cols-[1.03fr_0.97fr] lg:gap-14">
            <div class="rk-reveal max-w-2xl">
              <p class="rk-kicker mb-5 text-sm font-bold uppercase text-blue-600">Sistem Manajemen Fasilitas</p>
              <h1 id="hero-title" class="rk-heading max-w-xl text-4xl font-extrabold text-slate-900 sm:text-5xl lg:text-6xl">
                Kelola Reservasi & Laporan Fasilitas Kampus
              </h1>
              <p class="mt-6 max-w-xl text-base leading-7 text-slate-500 sm:text-lg">
                Temukan, pesan, dan laporkan fasilitas kampus dalam satu platform terpadu. Antarmuka intuitif untuk mahasiswa, dosen, staf, dan pengelola.
              </p>
              <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <router-link :to="{ name: 'login' }" class="rk-btn rk-btn-primary rk-focus bg-blue-600 text-white">
                  <CalendarPlus :size="19" aria-hidden="true" />
                  <span>Mulai Reservasi</span>
                </router-link>
                <router-link :to="{ name: 'facilities' }" class="rk-btn rk-btn-secondary rk-focus bg-white text-blue-600 hover:bg-blue-50">
                  <LayoutGrid :size="19" aria-hidden="true" />
                  <span>Lihat Fasilitas</span>
                </router-link>
              </div>
            </div>
            <aside class="rk-visual rk-reveal-delay rounded-3xl bg-white/80" aria-label="Ilustrasi kampus">
              <div class="absolute inset-0 flex items-center justify-center p-8" aria-hidden="true">
                <svg viewBox="0 0 520 400" class="relative z-10 w-full max-w-lg" role="img" aria-label="Ilustrasi gedung kampus ceria">
                  <ellipse cx="260" cy="350" rx="205" ry="24" fill="#bfdbfe" opacity=".55" /> 
                  <path d="M83 326h354V185L260 95 83 185z" fill="#dbeafe" stroke="#2563eb" stroke-width="5" stroke-linejoin="round" /> 
                  <path d="M58 190 260 75l202 115" fill="#60a5fa" stroke="#2563eb" stroke-width="6" stroke-linejoin="round" /> 
                  <path d="M224 326V244a36 36 0 0 1 72 0v82" fill="#2563eb" /> 
                  <path d="M119 216h48v48h-48zm234 0h48v48h-48zM190 175h42v42h-42zm98 0h42v42h-42z" fill="#fff" stroke="#60a5fa" stroke-width="4" /> 
                  <path d="M260 75V39m0 0 38 12-38 12" fill="none" stroke="#1d4ed8" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" /> 
                  <circle cx="104" cy="130" r="15" fill="#fbbf24" />
                  <path d="M104 102v-9m0 83v-9m28-37h9m-83 0h9m57-20 7-7m-59 59 7-7m45 0 7 7m-59-59 7 7" stroke="#f59e0b" stroke-width="4" stroke-linecap="round" /> 
                  <path d="M409 122c0-14 11-25 25-25s25 11 25 25-11 25-25 25" fill="#34d399" />
                  <path d="M434 146v28m0-16-17-13m17 22 17-13" stroke="#059669" stroke-width="5" stroke-linecap="round" /> 
                  <circle cx="165" cy="300" r="12" fill="#fbbf24" />
                  <path d="M165 312v28m0-17-13 12m13-12 13 12" stroke="#f59e0b" stroke-width="5" stroke-linecap="round" /> 
                  <circle cx="358" cy="300" r="12" fill="#fb7185" />
                  <path d="M358 312v28m0-17-13 12m13-12 13 12" stroke="#e11d48" stroke-width="5" stroke-linecap="round" />
                </svg>
              </div>
            </aside>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { Building2, CalendarPlus, LayoutGrid } from 'lucide-vue-next';
</script>

<style scoped>
.rk-page {
  --rk-blue: #2563eb;
  --rk-blue-dark: #1d4ed8;
  --rk-blue-soft: #eff6ff;
  --rk-line: #dbeafe;
  --rk-ink: #0f172a;
  --rk-body: #64748b;
  --rk-shadow: 0 22px 55px rgba(37, 99, 235, 0.15);
  --rk-shadow-soft: 0 12px 30px rgba(37, 99, 235, 0.10);

  width: 100%;
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  isolation: isolate;
  color: var(--rk-ink);
  background-color: #ffffff;
}

.rk-page::before {
  content: "";
  position: absolute;
  z-index: -2;
  width: 550px;
  height: 550px;
  top: -250px;
  right: -160px;
  border-radius: 999px;
  background: rgba(96, 165, 250, 0.25);
  filter: blur(26px);
}

.rk-page::after {
  content: "";
  position: absolute;
  z-index: -2;
  width: 430px;
  height: 430px;
  bottom: -240px;
  left: -180px;
  border-radius: 999px;
  background: rgba(147, 197, 253, 0.30);
  filter: blur(24px);
}

.rk-grid {
  background-image: radial-gradient(rgba(37, 99, 235, 0.11) 1px, transparent 1px);
  background-size: 28px 28px;
  background-position: center top;
}

.rk-shell {
  width: 100%;
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 24px;
}

.rk-brand {
  letter-spacing: 0.13em;
  box-shadow: 0 7px 18px rgba(37, 99, 235, 0.18);
}

.rk-dot {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  background: #22c55e;
  box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.13);
}

.rk-kicker {
  letter-spacing: 0.11em;
}

.rk-heading {
  letter-spacing: -0.055em;
  line-height: 1.04;
}

.rk-btn {
  min-height: 50px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  border-radius: 0.9rem;
  padding: 0.78rem 1.15rem;
  font-weight: 700;
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

.rk-btn-secondary {
  border: 1px solid #bfdbfe;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.06);
}

.rk-focus:focus-visible {
  outline: 3px solid rgba(37, 99, 235, 0.38);
  outline-offset: 4px;
}

.rk-visual {
  min-height: 430px;
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(203, 213, 225, 0.8);
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
  border-radius: 1.5rem;
}

.rk-visual::before {
  content: "";
  position: absolute;
  width: 240px;
  height: 240px;
  border-radius: 999px;
  top: -110px;
  right: -75px;
  background: rgba(37, 99, 235, 0.06);
  filter: blur(10px);
}

.rk-visual::after {
  content: "";
  position: absolute;
  width: 180px;
  height: 180px;
  border-radius: 999px;
  left: -80px;
  bottom: -90px;
  background: rgba(96, 165, 250, 0.08);
  filter: blur(12px);
}

.rk-reveal {
  animation: rkReveal 600ms ease both;
}

.rk-reveal-delay {
  animation: rkReveal 600ms 130ms ease both;
}

@keyframes rkReveal {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 767px) {
  .rk-shell {
    padding: 0 18px;
  }

  .rk-visual {
    min-height: 390px;
  }
}
</style>
