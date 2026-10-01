<script setup>
import { CheckCircle2 } from 'lucide-vue-next';

defineProps({
    image: { type: String, required: true },
    features: { type: Array, required: true },
});
</script>

<template>
    <section id="tentang" class="about-section" aria-labelledby="about-title">
        <div class="about-visual" data-reveal="left">
            <img :src="image" alt="Area fasilitas Universitas Diponegoro" loading="lazy" />
            <div class="about-badge"><CheckCircle2 :size="18" /><span><strong>Terintegrasi</strong>Data dan jadwal dalam satu sistem</span></div>
        </div>
        <div class="about-copy" data-reveal="right">
            <span class="section-kicker">TENTANG RUANGKITA</span>
            <h2 id="about-title">Satu alur yang lebih tenang untuk fasilitas kampus.</h2>
            <p>Cari ruang, periksa jadwal, ajukan reservasi, dan pantau prosesnya tanpa berpindah layanan. Informasi penting tetap mudah dipahami oleh pengguna, petugas, dan admin.</p>
            <ul>
                <li><CheckCircle2 :size="18" />Informasi fasilitas yang selalu diperbarui</li>
                <li><CheckCircle2 :size="18" />Alur persetujuan yang transparan</li>
                <li><CheckCircle2 :size="18" />Pelaporan kerusakan yang terdokumentasi</li>
            </ul>
        </div>
    </section>

    <section class="feature-section" aria-labelledby="feature-title">
        <header data-reveal="up">
            <span class="section-kicker">FITUR UTAMA</span>
            <h2 id="feature-title">Dirancang agar setiap langkah terasa sederhana.</h2>
            <p>Fungsi inti yang dibutuhkan civitas kampus disusun dalam pengalaman yang konsisten.</p>
        </header>
        <div class="feature-grid">
            <article v-for="(feature, index) in features" :key="feature.title" data-reveal="up" :style="{ '--reveal-delay': `${index * 70}ms` }">
                <span class="feature-icon"><component :is="feature.icon" :size="22" /></span>
                <h3>{{ feature.title }}</h3>
                <p>{{ feature.desc }}</p>
            </article>
        </div>
    </section>
</template>

<style scoped>
.about-section, .about-section *, .feature-section, .feature-section * { box-sizing: border-box; }
.about-section, .feature-section { max-width: 1180px; margin: auto; padding: 96px 24px; }
.about-section { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
.about-visual { position: relative; min-width: 0; }.about-visual > img { width: 100%; height: 420px; display: block; object-fit: cover; border-radius: 18px; box-shadow: 0 24px 55px rgba(15,23,42,.14); }
.about-badge { position: absolute; right: -18px; bottom: 24px; display: flex; align-items: center; gap: 10px; max-width: 230px; padding: 14px 16px; border: 1px solid #dbeafe; border-radius: 12px; background: rgba(255,255,255,.96); color: #2563eb; box-shadow: 0 16px 34px rgba(15,23,42,.14); }.about-badge span { color: #475569; font-size: 12px; line-height: 1.4; }.about-badge strong { display: block; color: #0f172a; font-size: 13px; }
.section-kicker { color: #2563eb; font-size: 11px; font-weight: 600; letter-spacing: .11em; }.about-copy h2, .feature-section h2 { margin: 14px 0 18px; color: #0f172a; font-size: clamp(30px,4vw,44px); line-height: 1.14; letter-spacing: -.035em; }.about-copy > p, .feature-section header > p { color: #64748b; line-height: 1.75; }.about-copy ul { display: grid; gap: 12px; margin: 24px 0 0; padding: 0; list-style: none; }.about-copy li { display: flex; gap: 9px; align-items: center; color: #334155; font-size: 14px; }.about-copy li svg { flex: 0 0 auto; color: #16a34a; }
.feature-section { max-width: none; padding-inline: max(24px, calc((100% - 1132px) / 2)); background: #fff; }.feature-section header { max-width: 650px; margin-bottom: 38px; }.feature-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 18px; }.feature-grid article { padding: 24px; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 8px 22px rgba(15,23,42,.05); transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }.feature-grid article:hover { transform: translateY(-5px) scale(1.01); border-color: #bfdbfe; box-shadow: 0 18px 38px rgba(37,99,235,.12); }.feature-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 12px; background: #eff6ff; color: #2563eb; }.feature-grid h3 { margin: 18px 0 8px; color: #0f172a; font-size: 17px; font-weight: 600; }.feature-grid p { margin: 0; color: #64748b; font-size: 13px; line-height: 1.65; }
[data-reveal] { opacity: 0; transform: translateY(20px); transition: opacity .55s ease var(--reveal-delay,0ms), transform .55s ease var(--reveal-delay,0ms); }[data-reveal="left"], [data-reveal="right"] { transform: translateY(20px); }[data-reveal].is-revealed { opacity: 1; transform: translate(0); }
@media (max-width: 900px) { .about-section { grid-template-columns: 1fr; gap: 42px; }.about-visual > img { height: 360px; }.about-badge { right: 16px; }.feature-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } }
@media (max-width: 560px) { .about-section, .feature-section { padding: 64px 16px; }.about-visual > img { height: 280px; }.about-badge { right: 12px; bottom: 12px; left: 12px; max-width: none; }.feature-grid { grid-template-columns: 1fr; }.feature-grid article { padding: 20px; } }
@media (prefers-reduced-motion: reduce) { [data-reveal] { opacity: 1; transform: none; transition: none; }.feature-grid article { transition: none; } }
</style>
