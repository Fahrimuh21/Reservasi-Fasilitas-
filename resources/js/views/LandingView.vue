<script setup>

import {
  ref,
  computed,
  watch,
  nextTick,
  onMounted,
  onUnmounted
} from 'vue'


import { RouterLink } from 'vue-router'
import axios from 'axios'


import campusImage from '../../asset/Undip.png'
import mascotImage from '../../asset/Makot.png'


import {
  useLiveCollection
} from '../composables/useLiveCollection'
import { useScrollReveal } from '../composables/useScrollReveal'

import BrandLogo from '../components/common/BrandLogo.vue'
import LandingAboutFeatures from '../components/sections/LandingAboutFeatures.vue'
import LandingJourneySection from '../components/sections/LandingJourneySection.vue'


import {

  Search,
  CalendarCheck,
  ShieldCheck,
  Building2,
  MapPin,
  Users,
  CheckCircle,
  CalendarDays,
  Clock,
  ArrowRight,
  Menu,
  X,
  ChevronRight,
  ChevronLeft,
  Sparkles,
  FileText,
  Send,
  Eye

} from 'lucide-vue-next'



/* =========================
STATE
========================= */


const mobileMenuOpen = ref(false)
const mobileMenuButton = ref(null)
const mobileMenuPanel = ref(null)
const activeSection = ref('beranda')

const isScrolled = ref(false)

const { refresh: refreshReveal } = useScrollReveal()



/* =========================
FACILITY DATA
========================= */


const currentCalendarMonth = ref(
  new Date().getMonth()
)


const currentCalendarYear = ref(
  new Date().getFullYear()
)


const selectedFacilityId = ref(null)
const selectedAvailabilityDate = ref(
  new Date().toLocaleDateString('en-CA')
)
const selectedAvailabilitySlots = ref([])
const availabilityLoading = ref(false)
const availabilityError = ref('')
const currentTime = ref(new Date())
let availabilityClockInterval



const {

  items: liveFacilities,

  loading: facilitiesLoading,

  error: facilitiesError,

  updatedAt: facilitiesUpdatedAt

} = useLiveCollection(
  '/api/facilities?per_page=100',
  3000
)




/* =========================
CALENDAR
========================= */


const monthNames = [

  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember'

]


const dayLabels = [

  'Min',
  'Sen',
  'Sel',
  'Rab',
  'Kam',
  'Jum',
  'Sab'

]




const selectedFacility = computed(()=>{


  return (

    liveFacilities.value.find(

      facility =>
      String(facility.id)
      ===
      String(selectedFacilityId.value)

    )

    ||

    liveFacilities.value[0]

    ||

    null

  )


})





const calendarDays = computed(()=>{


  const year =
  currentCalendarYear.value


  const month =
  currentCalendarMonth.value


  const firstDay =
  new Date(
    year,
    month,
    1
  ).getDay()



  const daysInMonth =
  new Date(
    year,
    month + 1,
    0
  ).getDate()



  const days=[]



  for(
    let i=0;
    i<firstDay;
    i++
  ){

    days.push({

      day:'',
      empty:true

    })

  }



  const selectedDate = selectedAvailabilityDate.value
  const hasAvailable = selectedAvailabilitySlots.value.some(
    slot => slot.status === 'tersedia'
  )
  const hasBooked = selectedAvailabilitySlots.value.some(
    slot => slot.status === 'terisi'
  )




  for(
    let d=1;
    d<=daysInMonth;
    d++
  ){


    days.push({

      day:d,

      empty:false,

      date: `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`,
      selected: selectedDate === `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`,
      today: d === new Date().getDate() && month === new Date().getMonth() && year === new Date().getFullYear(),
      available: selectedDate === `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}` && hasAvailable,
      booked: selectedDate === `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}` && hasBooked && !hasAvailable


    })


  }



  return days


})





const prevMonth = ()=>{


  if(
    currentCalendarMonth.value === 0
  ){

    currentCalendarMonth.value = 11

    currentCalendarYear.value--

  }

  else{

    currentCalendarMonth.value--

  }

  selectedAvailabilityDate.value = `${currentCalendarYear.value}-${String(currentCalendarMonth.value + 1).padStart(2, '0')}-01`


}




const nextMonth = ()=>{


  if(
    currentCalendarMonth.value === 11
  ){

    currentCalendarMonth.value = 0

    currentCalendarYear.value++

  }

  else{

    currentCalendarMonth.value++

  }

  selectedAvailabilityDate.value = `${currentCalendarYear.value}-${String(currentCalendarMonth.value + 1).padStart(2, '0')}-01`


}






/* =========================
TIME SLOT
========================= */


const timeSlots = computed(() => {
  const now = currentTime.value
  const isToday = selectedAvailabilityDate.value === now.toLocaleDateString('en-CA')

  return selectedAvailabilitySlots.value
    .filter(slot => {
      if (!isToday) return true

      const slotStart = new Date(`${selectedAvailabilityDate.value}T${slot.start}:00`)
      return slotStart > now
    })
    .map(slot => ({
      time: `${slot.start} - ${slot.end}`,
      status: slot.status === 'tersedia' ? 'available' : 'booked'
    }))
})

watch(
  [selectedFacilityId, selectedAvailabilityDate],
  async ([facilityId, date], _previous, onCleanup) => {
    if (!facilityId || !date) {
      selectedAvailabilitySlots.value = []
      return
    }

    const controller = new AbortController()
    onCleanup(() => controller.abort())
    availabilityLoading.value = true
    availabilityError.value = ''

    try {
      const response = await axios.get(
        `/api/facilities/${facilityId}/availability`,
        { params: { date }, signal: controller.signal }
      )
      selectedAvailabilitySlots.value = response.data.data.slots || []
    } catch (error) {
      if (!controller.signal.aborted) {
        selectedAvailabilitySlots.value = []
        availabilityError.value = error.response?.data?.message || 'Ketersediaan gagal dimuat.'
      }
    } finally {
      if (!controller.signal.aborted) availabilityLoading.value = false
    }
  },
  { immediate: true }
)

const selectAvailabilityDate = date => {
  if (date) selectedAvailabilityDate.value = date
}

watch(
  liveFacilities,
  items => {
    if (!selectedFacilityId.value && items.length) {
      selectedFacilityId.value = items[0].id
    }
  },
  { immediate: true }
)

const facilityAvailabilityText =
computed(()=>{


  if(facilitiesLoading.value)

  return 'Memuat data ketersediaan...'



  if(facilitiesError.value)

  return facilitiesError.value



  if(!selectedFacility.value)

  return 'Belum ada fasilitas aktif.'



  if (availabilityLoading.value) return 'Memuat ketersediaan...'
  if (availabilityError.value) return availabilityError.value

  return `${timeSlots.value.filter(slot => slot.status === 'available').length} slot tersedia pada ${new Date(`${selectedAvailabilityDate.value}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`


})







/* =========================
FACILITY CARD
========================= */


const facilityGradients = [

  'from-blue-500 to-blue-700',

  'from-indigo-500 to-indigo-700',

  'from-cyan-500 to-cyan-700',

  'from-violet-500 to-violet-700'

]


const facilityIcons = [

  '🏛️',

  '📋',

  '💻',

  '🤝'

]





const facilities = computed(()=>{


  return liveFacilities.value

  .slice(0,4)

  .map(

    (facility,index)=>({


      ...facility,



      location:

      typeof facility.location === 'object'

      ?

      [

        facility.location?.name,

        facility.location?.building,

        facility.location?.floor

      ]

      .filter(Boolean)

      .join(' · ')

      :

      facility.location

      ||

      'Lokasi tersedia',




      capacity:

      `${facility.capacity || 0} orang`,




      status:

      facility.status === 'active'

      ?

      'Tersedia'

      :

      'Tidak aktif',




      gradient:

      facilityGradients[
        index % facilityGradients.length
      ],




      icon:

      facilityIcons[
        index % facilityIcons.length
      ],




      availableSlots:

      (

        facility.availability_today

        ||

        []

      )

      .filter(

        slot =>
        slot.status === 'tersedia'

      )

      .length



    })


  )


})

watch(facilities, refreshReveal, { flush: 'post' })






/* =========================
FEATURE
========================= */


const features = [

  {

    icon:Search,

    title:'Cari Fasilitas',

    desc:
    'Temukan fasilitas berdasarkan tipe, lokasi, dan kapasitas.'

  },


  {

    icon:Eye,

    title:'Cek Ketersediaan',

    desc:
    'Lihat jadwal fasilitas secara real-time.'

  },


  {

    icon:CalendarCheck,

    title:'Reservasi Online',

    desc:
    'Ajukan penggunaan fasilitas dengan mudah.'

  },


  {

    icon:ShieldCheck,

    title:'Pengelolaan Terstruktur',

    desc:
    'Kelola penggunaan fasilitas lebih rapi.'

  }


]







/* =========================
STEPS
========================= */


const steps = [

{

num:'01',

title:'Cari Fasilitas',

desc:
'Temukan fasilitas yang sesuai kebutuhan.',

icon:Search

},


{

num:'02',

title:'Pilih Waktu',

desc:
'Tentukan tanggal dan slot penggunaan.',

icon:CalendarDays

},


{

num:'03',

title:'Isi Tujuan',

desc:
'Masukkan tujuan penggunaan.',

icon:FileText

},


{

num:'04',

title:'Kirim Pengajuan',

desc:
'Tunggu proses persetujuan.',

icon:Send

}

]







/* =========================
NAVIGATION
========================= */


const closeMobileMenu = (restoreFocus = true)=>{
  if(!mobileMenuOpen.value) return
  mobileMenuOpen.value = false
  document.body.classList.remove('landing-menu-open')
  if(restoreFocus) nextTick(() => mobileMenuButton.value?.focus())
}

const openMobileMenu = ()=>{
  mobileMenuOpen.value = true
  document.body.classList.add('landing-menu-open')
  nextTick(() => mobileMenuPanel.value?.querySelector('button, a')?.focus())
}

const toggleMobileMenu = ()=> mobileMenuOpen.value ? closeMobileMenu() : openMobileMenu()

const scrollToSection = (id)=>{


closeMobileMenu(false)
activeSection.value = id



const element =
document.getElementById(id)



if(element){

element.scrollIntoView({

behavior:'smooth',

block:'start'

})

}


}



/* =========================
SCROLL OBSERVER
========================= */


const handleScroll = ()=>{

isScrolled.value =
window.scrollY > 50

const sectionIds = ['beranda', 'fasilitas', 'ketersediaan']
const current = sectionIds.findLast(id => {
  const section = document.getElementById(id)
  return section && section.getBoundingClientRect().top <= 130
})
activeSection.value = current || 'beranda'

}

const handleResize = ()=>{
  if(window.innerWidth > 840){
    mobileMenuOpen.value = false
    document.body.classList.remove('landing-menu-open')
  }
}

const handleMenuKeydown = event=>{
  if(event.key === 'Escape' && mobileMenuOpen.value){
    event.preventDefault()
    closeMobileMenu()
    return
  }

  if(event.key !== 'Tab' || !mobileMenuOpen.value || !mobileMenuPanel.value) return
  const focusable = [...mobileMenuPanel.value.querySelectorAll('a[href], button:not([disabled])')]
  if(!focusable.length) return
  const first = focusable[0]
  const last = focusable[focusable.length - 1]
  if(event.shiftKey && document.activeElement === first){ event.preventDefault(); last.focus() }
  else if(!event.shiftKey && document.activeElement === last){ event.preventDefault(); first.focus() }
}





onMounted(()=>{


  window.addEventListener(

'scroll',

handleScroll,

{
passive:true
}

  )

  window.addEventListener('resize', handleResize)
  window.addEventListener('keydown', handleMenuKeydown)
  availabilityClockInterval = window.setInterval(() => {
    currentTime.value = new Date()
  }, 30000)
  handleScroll()




})






onUnmounted(()=>{


window.removeEventListener(

'scroll',

handleScroll

)

window.removeEventListener('resize', handleResize)
window.removeEventListener('keydown', handleMenuKeydown)
window.clearInterval(availabilityClockInterval)
document.body.classList.remove('landing-menu-open')



})



</script>

<template>
  <div class="landing-root">
    <header class="landing-header" :class="{ 'is-scrolled': isScrolled }">
      <nav class="landing-nav" aria-label="Navigasi utama">
        <RouterLink :to="{ name: 'landing' }" class="brand-mark" aria-label="RuangKita, halaman utama"><BrandLogo :size="38" /></RouterLink>
        <div class="landing-links">
          <a href="#beranda" :class="{ active: activeSection === 'beranda' }" :aria-current="activeSection === 'beranda' ? 'page' : undefined" @click.prevent="scrollToSection('beranda')">Beranda</a>
          <a href="#fasilitas" :class="{ active: activeSection === 'fasilitas' }" :aria-current="activeSection === 'fasilitas' ? 'page' : undefined" @click.prevent="scrollToSection('fasilitas')">Fasilitas</a>
          <a href="#ketersediaan" :class="{ active: activeSection === 'ketersediaan' }" :aria-current="activeSection === 'ketersediaan' ? 'page' : undefined" @click.prevent="scrollToSection('ketersediaan')">Ketersediaan</a>
        </div>
        <div class="landing-actions">
          <RouterLink :to="{ name: 'login' }" class="link-button">Masuk</RouterLink>
          <RouterLink :to="{ name: 'register' }" class="primary-button">Daftar</RouterLink>
        </div>
        <button ref="mobileMenuButton" type="button" class="menu-button" aria-label="Buka menu navigasi" aria-controls="landing-mobile-menu" :aria-expanded="mobileMenuOpen" @click="toggleMobileMenu"><Menu :size="21" /></button>
      </nav>
      <button v-if="mobileMenuOpen" type="button" class="mobile-nav-overlay" aria-label="Tutup menu navigasi" @click="closeMobileMenu()"></button>
      <aside v-if="mobileMenuOpen" id="landing-mobile-menu" ref="mobileMenuPanel" class="mobile-nav" role="dialog" aria-modal="true" aria-label="Menu navigasi">
        <div class="mobile-nav__head"><BrandLogo :size="38" /><button type="button" aria-label="Tutup menu navigasi" @click="closeMobileMenu()"><X :size="21" /></button></div>
        <nav aria-label="Navigasi mobile">
          <a href="#beranda" :class="{ active: activeSection === 'beranda' }" :aria-current="activeSection === 'beranda' ? 'page' : undefined" @click.prevent="scrollToSection('beranda')">Beranda</a>
          <a href="#fasilitas" :class="{ active: activeSection === 'fasilitas' }" :aria-current="activeSection === 'fasilitas' ? 'page' : undefined" @click.prevent="scrollToSection('fasilitas')">Fasilitas</a>
          <a href="#ketersediaan" :class="{ active: activeSection === 'ketersediaan' }" :aria-current="activeSection === 'ketersediaan' ? 'page' : undefined" @click.prevent="scrollToSection('ketersediaan')">Ketersediaan</a>
        </nav>
        <div class="mobile-nav__actions"><RouterLink :to="{ name: 'login' }" class="secondary-button" @click="closeMobileMenu(false)">Masuk</RouterLink><RouterLink :to="{ name: 'register' }" class="primary-button" @click="closeMobileMenu(false)">Daftar</RouterLink></div>
      </aside>
    </header>

    <main>
      <section id="beranda" class="hero-section">
        <div class="hero-copy" data-reveal="left">
          <span class="eyebrow"><Sparkles :size="14" /> PLATFORM RESERVASI KAMPUS</span>
          <h1>Kelola fasilitas kampus lebih mudah.</h1>
          <p>Reservasi ruang, pantau ketersediaan, dan laporkan kerusakan dalam satu platform digital untuk civitas akademika.</p>
          <div class="hero-actions">
            <RouterLink :to="{ name: 'login' }" class="primary-button"><CalendarCheck :size="18" /> Mulai Reservasi</RouterLink>
            <RouterLink :to="{ name: 'facilities' }" class="secondary-button"><Building2 :size="18" /> Lihat Fasilitas</RouterLink>
          </div>
          <div class="hero-stats">
            <span><strong>{{ liveFacilities.length }}</strong> fasilitas aktif</span>
            <span><strong>{{ facilitiesUpdatedAt ? 'LIVE' : '...' }}</strong> ketersediaan</span>
          </div>
        </div>
        <div class="hero-visual" data-reveal="right">
          <img :src="campusImage" alt="Kampus Universitas Diponegoro" />
          <div class="hero-overlay"></div>
          <div class="hero-float"><Clock :size="16" /><span>{{ facilityAvailabilityText }}</span></div>
          <img :src="mascotImage" alt="Maskot Universitas" class="mascot" />
        </div>
      </section>

      <LandingAboutFeatures :image="campusImage" :features="features" />

      <section id="fasilitas" class="section-block">
        <div class="section-heading" data-reveal="up">
          <span class="eyebrow">KATALOG FASILITAS</span>
          <h2>Ruang yang siap digunakan</h2>
          <p>Data fasilitas dan slot diperbarui otomatis dari sistem reservasi.</p>
        </div>
        <div v-if="facilitiesError" class="notice error">{{ facilitiesError }}</div>
        <div v-else-if="facilitiesLoading" class="notice">Memuat fasilitas...</div>
        <div v-else-if="!facilities.length" class="notice">Belum ada fasilitas aktif.</div>
        <div v-else class="facility-grid">
          <article v-for="(facility, index) in facilities" :key="facility.id" class="facility-card" data-reveal="up" :style="{ '--reveal-delay': `${index * 70}ms` }">
            <div class="facility-banner" :class="`bg-gradient-to-br ${facility.gradient}`"><span>{{ facility.icon }}</span><b><CheckCircle :size="13" /> {{ facility.status }}</b></div>
            <div class="facility-body">
              <h3>{{ facility.name }}</h3>
              <p><MapPin :size="14" /> {{ facility.location }}</p>
              <p><Users :size="14" /> {{ facility.capacity }}</p>
              <p class="availability-label"><CheckCircle :size="14" /> {{ facility.availableSlots }} slot tersedia hari ini</p>
              <RouterLink :to="{ name: 'facilities' }" class="card-link">Lihat detail <ArrowRight :size="14" /></RouterLink>
            </div>
          </article>
        </div>
      </section>

      <section id="ketersediaan" class="availability-section">
        <div class="availability-panel" data-reveal="up">
          <div class="calendar-panel">
            <div class="calendar-header"><button type="button" aria-label="Bulan sebelumnya" @click="prevMonth"><ChevronLeft :size="18" /></button><strong>{{ monthNames[currentCalendarMonth] }} {{ currentCalendarYear }}</strong><button type="button" aria-label="Bulan berikutnya" @click="nextMonth"><ChevronRight :size="18" /></button></div>
            <div class="calendar-days"><span v-for="day in dayLabels" :key="day">{{ day }}</span></div>
            <div class="calendar-grid"><button v-for="(day, index) in calendarDays" :key="index" type="button" :disabled="day.empty" :aria-label="day.empty ? undefined : new Date(`${day.date}T00:00:00`).toLocaleDateString('id-ID', { dateStyle: 'full' })" :aria-pressed="day.selected" :class="{ empty: day.empty, today: day.today, selected: day.selected, available: day.available, booked: day.booked }" @click="selectAvailabilityDate(day.date)">{{ day.day }}</button></div>
            <div class="legend"><span><i class="dot available"></i> Tersedia</span><span><i class="dot booked"></i> Terisi</span></div>
          </div>
          <div class="availability-copy">
            <span class="eyebrow">KETERSEDIAAN REAL-TIME</span>
            <h2>Pilih fasilitas, lihat slot yang tersedia.</h2>
            <label>Fasilitas yang dipantau<select v-model="selectedFacilityId" :disabled="facilitiesLoading || !liveFacilities.length"><option v-for="facility in liveFacilities" :key="facility.id" :value="facility.id">{{ facility.name }}</option></select></label>
            <p class="sync-status" :class="{ 'is-error': availabilityError }">{{ facilityAvailabilityText }}<span v-if="facilitiesUpdatedAt"> · {{ facilitiesUpdatedAt.toLocaleTimeString() }}</span></p>
            <div v-if="availabilityLoading" class="notice">Memuat slot tanggal ini...</div>
            <div v-else-if="availabilityError" class="notice error">{{ availabilityError }}</div>
            <div v-else-if="!selectedFacility" class="notice">Belum ada fasilitas aktif.</div>
            <div v-else class="time-slots"><div v-for="slot in timeSlots.slice(0, 8)" :key="slot.time" :class="['time-slot', slot.status]"><Clock :size="14" /> <span>{{ slot.time }}</span><b>{{ slot.status === 'available' ? 'Tersedia' : 'Terisi' }}</b></div></div>
          </div>
        </div>
      </section>

      <LandingJourneySection :steps="steps" />

      <section class="cta-section"><div data-reveal="up"><span class="eyebrow">RUANGKITA</span><h2>Siap memakai fasilitas kampus?</h2><p>Masuk untuk mengajukan reservasi dan mengelola aktivitas Anda.</p><RouterLink :to="{ name: 'login' }" class="primary-button">Mulai sekarang <ArrowRight :size="17" /></RouterLink></div></section>
    </main>

    <footer class="landing-footer" data-reveal="up">
      <div class="footer-main">
        <div class="footer-brand">
          <BrandLogo :size="38" />
          <p>Platform reservasi fasilitas Universitas Diponegoro untuk penggunaan ruang yang lebih tertata.</p>
          <span class="footer-status">Sistem operasional</span>
        </div>
        <div class="footer-column">
          <h3>Navigasi</h3>
          <a href="#beranda" @click.prevent="scrollToSection('beranda')">Beranda</a>
          <a href="#fasilitas" @click.prevent="scrollToSection('fasilitas')">Fasilitas</a>
          <a href="#ketersediaan" @click.prevent="scrollToSection('ketersediaan')">Ketersediaan</a>
        </div>
        <div class="footer-column">
          <h3>Layanan</h3>
          <RouterLink :to="{ name: 'login' }">Masuk ke akun</RouterLink>
          <RouterLink :to="{ name: 'register' }">Daftar akun</RouterLink>
          <RouterLink :to="{ name: 'facilities' }">Katalog fasilitas</RouterLink>
        </div>
        <div class="footer-column footer-contact">
          <h3>Kontak</h3>
          <span>Universitas Diponegoro</span>
          <span>Jl. Prof. Soedarto, SH</span>
          <span>Tembalang, Semarang</span>
          <a href="mailto:info@undip.ac.id">info@undip.ac.id</a>
        </div>
      </div>
      <div class="footer-bottom"><span>© {{ new Date().getFullYear() }} RuangKita</span><span>Universitas Diponegoro</span></div>
    </footer>
  </div>
</template>

<style scoped>
.landing-root { --blue: #2563eb; --blue-dark: #1e40af; --blue-soft: #eff6ff; --ink: #0f172a; --muted: #64748b; --line: #e2e8f0; --surface: #fff; width: 100%; min-width: 0; min-height: 100vh; background: #f8fafc; color: var(--ink); font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; font-weight: 400; }
.landing-root, .landing-root * { box-sizing: border-box; }
:global(body.landing-menu-open) { overflow: hidden; }
.landing-header { position: sticky; top: 0; z-index: 20; padding: 18px 24px 0; }
.landing-nav { max-width: 1180px; min-height: 64px; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 10px 14px; border: 1px solid #dbeafe; border-radius: 14px; background: rgba(255,255,255,.94); box-shadow: 0 10px 28px rgba(15,23,42,.06); backdrop-filter: blur(14px); transition: box-shadow .2s ease; }
.landing-header.is-scrolled .landing-nav { box-shadow: 0 14px 34px rgba(15,23,42,.12); }
.brand-mark { flex: 0 1 auto; min-width: 0; color: var(--blue); text-decoration: none; }
.landing-links, .landing-actions, .hero-actions, .hero-stats { display: flex; align-items: center; gap: 20px; }
.landing-links a, .link-button, .landing-footer a { color: var(--muted); font-size: 13px; font-weight: 500; text-decoration: none; }
.landing-links a:hover, .link-button:hover, .landing-footer a:hover { color: var(--blue); }
.landing-links a.active { color: var(--blue); font-weight: 600; }
.primary-button, .secondary-button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 8px; font-weight: 600; text-decoration: none; }
.primary-button { border: 1px solid var(--blue); background: var(--blue); color: #fff; box-shadow: 0 10px 22px rgba(37,99,235,.2); }
.primary-button:hover { background: var(--blue-dark); transform: translateY(-2px); box-shadow: 0 14px 26px rgba(37,99,235,.25); }
.secondary-button { border: 1px solid #bfdbfe; background: #fff; color: var(--blue); }
.menu-button, .calendar-header button { display: grid; place-items: center; border: 1px solid var(--line); border-radius: 8px; background: #fff; color: var(--ink); }
.menu-button { display: none; width: 44px; height: 44px; flex: 0 0 44px; }
.mobile-nav, .mobile-nav-overlay { display: none; }
.hero-section, .section-block, .availability-section { max-width: 1180px; margin: auto; padding: 96px 24px; }
.hero-section { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 64px; min-height: 680px; }
.hero-copy, .hero-visual { will-change: transform, opacity; }
.eyebrow { display: inline-flex; align-items: center; gap: 7px; color: var(--blue); font-size: 11px; font-weight: 600; letter-spacing: .11em; }
h1, h2, h3, p { margin-top: 0; }
h1 { max-width: 600px; margin-bottom: 20px; font-size: clamp(40px, 5vw, 64px); font-weight: 700; line-height: 1.08; letter-spacing: -.04em; overflow-wrap: normal; word-break: normal; }
h2 { font-size: clamp(30px, 4vw, 44px); font-weight: 700; line-height: 1.14; letter-spacing: -.035em; }
.hero-copy > p, .section-heading p, .availability-copy > p, .cta-section p, .footer-brand p { font-weight: 400; }
.hero-copy > p, .section-heading p, .availability-copy > p, .cta-section p { max-width: 560px; color: var(--muted); line-height: 1.7; }
.hero-actions { margin-top: 30px; flex-wrap: wrap; }
.hero-stats { margin-top: 32px; color: var(--muted); font-size: 12px; }
.hero-stats strong { display: block; color: var(--ink); font-size: 22px; }
.hero-visual { position: relative; min-height: 480px; overflow: hidden; border-radius: 18px; box-shadow: 0 24px 60px rgba(15,23,42,.18); }
.hero-visual > img:first-child { width: 100%; height: 100%; min-height: 480px; object-fit: cover; display: block; }
.hero-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 35%, rgba(15,23,42,.72)); }
.hero-float { position: absolute; left: 22px; bottom: 22px; display: flex; align-items: center; gap: 8px; max-width: calc(100% - 44px); padding: 13px 16px; border: 1px solid rgba(255,255,255,.5); border-radius: 10px; background: rgba(255,255,255,.92); color: var(--blue); font-size: 12px; font-weight: 600; }
.mascot { position: absolute; top: 20px; right: 20px; width: 72px; height: 72px; border: 3px solid #fff; border-radius: 50%; object-fit: cover; animation: mascot-float 4s ease-in-out infinite; }
.section-block { background: #fff; max-width: none; padding-left: max(24px, calc((100% - 1132px) / 2)); padding-right: max(24px, calc((100% - 1132px) / 2)); }
.section-heading { margin-bottom: 36px; }
.facility-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 290px)); justify-content: center; gap: 18px; }
.facility-card { width: 100%; overflow: hidden; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: 0 8px 20px rgba(15,23,42,.05); }
.facility-banner { position: relative; height: 132px; display: grid; place-items: center; color: #fff; }.facility-banner > span { font-size: 48px; opacity: .5; }.facility-banner b { position: absolute; top: 12px; right: 12px; display: flex; align-items: center; gap: 4px; padding: 5px 8px; border-radius: 999px; background: rgba(255,255,255,.9); color: #15803d; font-size: 10px; }
.facility-body { padding: 18px; }.facility-body h3 { margin-bottom: 13px; font-size: 17px; font-weight: 600; }.facility-body p { display: flex; align-items: center; gap: 6px; margin: 7px 0; color: var(--muted); font-size: 12px; }.facility-body .availability-label { color: var(--blue); font-weight: 600; }.card-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; color: var(--blue); font-size: 12px; font-weight: 600; text-decoration: none; }
.notice { padding: 18px; border: 1px solid var(--line); border-radius: 10px; color: var(--muted); background: #fff; }.notice.error { color: #b91c1c; background: #fef2f2; }
.availability-section { max-width: none; background: var(--blue-soft); padding-left: max(24px, calc((100% - 1132px) / 2)); padding-right: max(24px, calc((100% - 1132px) / 2)); }.availability-panel { display: grid; grid-template-columns: .9fr 1.1fr; gap: 56px; align-items: center; }.calendar-panel { padding: 22px; border: 1px solid #dbeafe; border-radius: 14px; background: #fff; }.calendar-header, .calendar-days, .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 7px; align-items: center; text-align: center; }.calendar-header { grid-template-columns: 38px 1fr 38px; margin-bottom: 22px; }.calendar-header button { width: 34px; height: 34px; }.calendar-days { margin-bottom: 8px; color: #64748b; font-size: 11px; font-weight: 600; }.calendar-grid span { display: grid; place-items: center; aspect-ratio: 1; border-radius: 7px; color: var(--muted); font-size: 12px; }.calendar-grid .available { background: #dcfce7; color: #15803d; }.calendar-grid .booked { background: #fee2e2; color: #b91c1c; }.calendar-grid .today { outline: 2px solid var(--blue); outline-offset: 2px; }.legend { display: flex; gap: 16px; margin-top: 20px; color: var(--muted); font-size: 11px; }.dot { width: 8px; height: 8px; display: inline-block; margin-right: 4px; border-radius: 50%; background: #dcfce7; }.dot.booked { background: #fee2e2; }.availability-copy label { display: grid; gap: 8px; max-width: 430px; margin: 24px 0 10px; color: var(--ink); font-size: 12px; font-weight: 600; }.availability-copy select { min-height: 44px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; background: #fff; font: inherit; }.sync-status { color: #15803d !important; font-size: 12px; font-weight: 600; }.time-slots { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-top: 18px; }.time-slot { display: flex; align-items: center; gap: 7px; padding: 10px; border-radius: 7px; background: #fff; color: var(--muted); font-size: 11px; }.time-slot b { margin-left: auto; font-size: 10px; }.time-slot.available b { color: #15803d; }.time-slot.booked b { color: #b91c1c; }
.cta-section { padding: 82px 24px; text-align: center; background: var(--blue); color: #fff; }.cta-section h2 { margin: 15px auto; color: #fff; }.cta-section p { margin: 0 auto 26px; color: #dbeafe; }.cta-section .primary-button { background: #fff; color: var(--blue); }
.landing-footer { padding: 58px max(24px, calc((100% - 1132px) / 2)) 24px; background: #fff; border-top: 1px solid var(--line); color: var(--muted); font-size: 12px; }
.footer-main { display: grid; grid-template-columns: 1.6fr repeat(3, 1fr); gap: 48px; padding-bottom: 44px; }
.footer-brand p { max-width: 270px; margin: 16px 0; color: var(--muted); line-height: 1.7; }
.footer-status { display: inline-flex; align-items: center; gap: 7px; color: #15803d; font-size: 11px; font-weight: 700; }
.footer-status i { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 4px #dcfce7; }
.footer-column { display: flex; flex-direction: column; align-items: flex-start; gap: 11px; }
.footer-column h3 { margin: 2px 0 8px; color: var(--ink); font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
.footer-column a, .footer-column span { color: var(--muted); font-size: 12px; text-decoration: none; }
.footer-column a:hover { color: var(--blue); }
.footer-bottom { display: flex; justify-content: space-between; gap: 16px; padding-top: 20px; border-top: 1px solid var(--line); color: #64748b; font-size: 11px; }
[data-reveal] { opacity: 0; transform: translateY(20px); transition: opacity .55s ease var(--reveal-delay,0ms), transform .55s ease var(--reveal-delay,0ms); }
[data-reveal="left"], [data-reveal="right"] { transform: translateY(20px); }
[data-reveal].is-revealed { opacity: 1; transform: translate(0); }
@keyframes mascot-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
@media (max-width: 900px) { .hero-section, .availability-panel { grid-template-columns: 1fr; gap: 36px; }.hero-section { padding-top: 72px; }.hero-visual { min-height: 360px; }.hero-visual > img:first-child { min-height: 360px; }.facility-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 290px)); }.footer-main { grid-template-columns: 1.4fr repeat(2, 1fr); }.footer-contact { grid-column: 2 / -1; } }
@media (max-width: 840px) {
  .landing-links, .landing-actions { display: none; }
  .menu-button { display: grid; place-items: center; }
  .mobile-nav-overlay { position: fixed; inset: 0; z-index: 30; display: block; width: 100%; height: 100%; padding: 0; border: 0; background: rgba(15,23,42,.48); }
  .mobile-nav { position: fixed; inset: 0 0 0 auto; z-index: 31; display: flex; width: min(340px, calc(100vw - 32px)); flex-direction: column; gap: 28px; padding: 20px; background: #fff; box-shadow: -20px 0 50px rgba(15,23,42,.18); }
  .mobile-nav__head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
  .mobile-nav__head button { display: grid; width: 44px; height: 44px; flex: 0 0 44px; place-items: center; border: 1px solid var(--line); border-radius: 8px; background: #fff; color: var(--ink); }
  .mobile-nav nav { display: grid; gap: 8px; }
  .mobile-nav nav a { min-height: 48px; display: flex; align-items: center; padding: 0 14px; border-radius: 8px; color: var(--muted); font-size: 15px; font-weight: 500; text-decoration: none; }
  .mobile-nav nav a:hover, .mobile-nav nav a.active { background: var(--blue-soft); color: var(--blue-dark); font-weight: 600; }
  .mobile-nav__actions { display: grid; gap: 10px; margin-top: auto; }
  .mobile-nav__actions > * { width: 100%; }
}
@media (max-width: 560px) { .landing-header { padding: 8px 10px 0; }.landing-nav { min-height: 60px; padding: 7px 10px; }.hero-section, .section-block, .availability-section { padding: 56px 16px; }.hero-section { min-height: auto; padding-top: 52px; }.hero-copy h1 { font-size: clamp(34px, 11vw, 42px); line-height: 1.1; letter-spacing: -.035em; }.hero-copy > p { font-size: 15px; line-height: 1.65; }.hero-actions { align-items: stretch; gap: 10px; }.hero-actions > * { flex: 1 1 100%; }.hero-stats { gap: 12px; flex-wrap: wrap; }.facility-grid, .time-slots { grid-template-columns: 1fr; }.calendar-panel { padding: 16px 12px; }.calendar-header, .calendar-days, .calendar-grid { gap: 4px; }.footer-main { grid-template-columns: 1fr 1fr; gap: 30px 20px; }.footer-brand { grid-column: 1 / -1; }.footer-contact { grid-column: auto; }.footer-bottom { flex-direction: column; gap: 8px; }.hero-visual, .hero-visual > img:first-child { min-height: 280px; } }
@media (max-width: 380px) { .landing-footer { padding-inline: 16px; }.footer-main { grid-template-columns: 1fr; }.footer-brand, .footer-contact { grid-column: auto; }.time-slot { min-width: 0; flex-wrap: wrap; }.time-slot b { margin-left: 21px; }.hero-stats span { flex: 1 1 120px; } }
@media (prefers-reduced-motion: reduce) { [data-reveal] { opacity: 1; transform: none; transition: none; }.mascot { animation: none; }.primary-button { transition: none; } }
</style>
