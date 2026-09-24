<template>
  <!-- h-screen dan overflow-hidden memastikan halaman pas 100% tinggi layar dan tidak bisa di-scroll -->
  <div class="flex flex-col h-screen overflow-hidden bg-page-background text-slate-900 font-sans">
    
    <!-- Navbar (Fixed height, tidak bisa menyusut) -->
    <header class="flex-none flex items-center justify-between p-6 border-b border-slate-200 bg-surface">
      <h1 class="text-2xl font-bold text-blue-600">RuangKita</h1>
      <nav class="space-x-4">
        <router-link
          :to="{ name: 'login' }"
          class="px-4 py-2 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition"
        >Masuk</router-link>
        <router-link
          :to="{ name: 'register' }"
          class="px-4 py-2 rounded-md border border-blue-600 text-blue-600 font-semibold hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition"
        >Daftar</router-link>
      </nav>
    </header>

    <!-- Main Content (Otomatis mengisi sisa ruang, konten persis di tengah) -->
    <main class="flex-1 flex flex-col items-center justify-center px-4">
      <div class="text-center max-w-4xl">
        <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 text-slate-900 leading-tight">
          Kelola Reservasi &amp; Laporan Fasilitas Kampus
        </h2>
        <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10">
          Temukan, pesan, dan laporkan fasilitas kampus secara mudah. Antarmuka intuitif
          untuk mahasiswa, dosen, staf, petugas, dan admin.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            :to="{ name: 'facilities' }"
            class="px-8 py-3 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition"
          >Lihat Fasilitas</router-link>
          <router-link
            :to="{ name: 'login' }"
            class="px-8 py-3 rounded-md border border-blue-600 text-blue-600 font-semibold hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition"
          >Mulai Reservasi</router-link>
        </div>
      </div>
    </main>

    <!-- Footer (Fixed height) -->
    <footer class="flex-none py-6 text-center text-sm text-slate-600 bg-page-background">
      © {{ new Date().getFullYear() }} RuangKita. Semua hak cipta dilindungi.
    </footer>
    
  </div>
</template>

<script setup>
// Static landing page – no special logic required.
</script>
