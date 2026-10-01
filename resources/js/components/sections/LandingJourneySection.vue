<script setup>
defineProps({ steps: { type: Array, required: true } });

const testimonials = [
    { quote: 'Jadwal ruang kini lebih mudah dilihat sebelum kegiatan diajukan.', role: 'Mahasiswa', initials: 'MA' },
    { quote: 'Permintaan dan laporan masuk tersusun rapi dalam satu alur kerja.', role: 'Petugas fasilitas', initials: 'PF' },
];
</script>

<template>
    <section class="journey-section" aria-labelledby="journey-title">
        <header data-reveal="up"><span>ALUR RESERVASI</span><h2 id="journey-title">Dari pencarian hingga persetujuan.</h2><p>Empat langkah ringkas dengan status yang selalu dapat dipantau.</p></header>
        <div class="journey-grid">
            <article v-for="(step, index) in steps" :key="step.num" data-reveal="up" :style="{ '--reveal-delay': `${index * 70}ms` }">
                <div><span>{{ step.num }}</span><component :is="step.icon" :size="20" /></div><h3>{{ step.title }}</h3><p>{{ step.desc }}</p>
            </article>
        </div>
        <div class="testimonial-grid" aria-label="Testimoni pengguna">
            <blockquote v-for="(item, index) in testimonials" :key="item.role" data-reveal="up" :style="{ '--reveal-delay': `${index * 90}ms` }">
                <p>“{{ item.quote }}”</p><footer><span>{{ item.initials }}</span><strong>{{ item.role }}</strong></footer>
            </blockquote>
        </div>
    </section>
</template>

<style scoped>
.journey-section, .journey-section * { box-sizing: border-box; }
.journey-section { max-width: 1180px; margin: auto; padding: 96px 24px; }.journey-section > header { max-width: 650px; }.journey-section > header > span { color: #2563eb; font-size: 11px; font-weight: 600; letter-spacing: .11em; }.journey-section h2 { margin: 14px 0 12px; color: #0f172a; font-size: clamp(30px,4vw,44px); line-height: 1.14; letter-spacing: -.035em; }.journey-section > header p { color: #64748b; line-height: 1.7; }
.journey-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 16px; margin-top: 38px; }.journey-grid article { position: relative; padding: 22px; border: 1px solid #dbeafe; border-radius: 14px; background: #fff; }.journey-grid article > div { display: flex; align-items: center; justify-content: space-between; color: #2563eb; }.journey-grid article > div > span { font-size: 12px; font-weight: 700; letter-spacing: .08em; }.journey-grid h3 { margin: 30px 0 8px; color: #0f172a; font-size: 17px; font-weight: 600; }.journey-grid p { margin: 0; color: #64748b; font-size: 13px; line-height: 1.6; }
.testimonial-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 18px; margin-top: 48px; }.testimonial-grid blockquote { margin: 0; padding: 24px; border-radius: 14px; background: #0f172a; color: #fff; box-shadow: 0 18px 38px rgba(15,23,42,.14); }.testimonial-grid blockquote > p { margin: 0 0 22px; color: #e2e8f0; font-size: 16px; line-height: 1.65; }.testimonial-grid footer { display: flex; align-items: center; gap: 10px; }.testimonial-grid footer span { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #2563eb; font-size: 11px; font-weight: 700; }.testimonial-grid footer strong { font-size: 13px; }
[data-reveal] { opacity: 0; transform: translateY(20px); transition: opacity .55s ease var(--reveal-delay,0ms), transform .55s ease var(--reveal-delay,0ms); }[data-reveal].is-revealed { opacity: 1; transform: translateY(0); }
@media (max-width: 900px) { .journey-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } }
@media (max-width: 560px) { .journey-section { padding: 64px 16px; }.journey-grid, .testimonial-grid { grid-template-columns: 1fr; }.journey-grid h3 { margin-top: 22px; } }
@media (prefers-reduced-motion: reduce) { [data-reveal] { opacity: 1; transform: none; transition: none; } }
</style>
