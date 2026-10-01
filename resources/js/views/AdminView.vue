<script setup>

import {
    ref,
    computed,
    onMounted,
    onUnmounted
} from "vue";

import axios from "axios";
import notification from '../components/notification/notificationService';
import '../../css/workflow.css';





/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/


const period = ref("month");


const periods=[

{
value:"week",
label:"7 Hari"
},

{
value:"month",
label:"30 Hari"
},

{
value:"year",
label:"1 Tahun"
}

];





const stats = ref({

reservations:0,

usage:0,

reports:0,

users:0

});
const summaryLoading = ref(true);
const summaryError = ref("");
const facilitySummary = ref([]);








const kpi = computed(()=>[


{
title:"Total Reservasi",
value:
stats.value.reservations,

icon:"◷",

trend:"Aktual",

type:"blue"

},


{
title:"Utilisasi Fasilitas",
value:
stats.value.usage+"%",

icon:"▦",

trend:"Aktual",

type:"green"

},


{
title:"Laporan Kerusakan",
value:
stats.value.reports,

icon:"⚠",

trend:"Aktual",

type:"yellow"

},


{
title:"Pengguna Aktif",

value:
stats.value.users,

icon:"◉",

trend:"Aktual",

type:"purple"

}


]);

async function loadSummary(){
    summaryLoading.value = true;
    try{
        const response = await axios.get("/api/admin/facilities/summary", {
            params:{ period:period.value },
            timeout:10000
        });
        const kpis = response.data.kpis || {};
        stats.value = {
            reservations:kpis.reservations || 0,
            usage:kpis.usage_rate || 0,
            reports:kpis.reports || 0,
            users:kpis.active_users || 0
        };
        facilitySummary.value = response.data.facilities || [];
        summaryError.value = "";
    } catch(error){
        summaryError.value = "Ringkasan belum dapat dimuat dari server.";
    } finally{
        summaryLoading.value = false;
    }
}







const chart = computed(() => facilitySummary.value
    .map(facility => ({
        id: facility.id || facility.code || facility.name,
        name: facility.name,
        code: facility.code || 'Fasilitas',
        value: Math.min(100, Math.max(0, Math.round(Number(facility.usage || 0) * 10) / 10)),
    }))
    .sort((a, b) => b.value - a.value));






/*
|--------------------------------------------------------------------------
| Facility Management
|--------------------------------------------------------------------------
*/


const facilities = ref([]);

const types = ref([]);

const locations = ref([]);
let facilitiesTimer;
const facilitiesLoading = ref(true);
const facilitiesError = ref("");


const modal = ref(false);

const editing = ref(false);



const saving = ref(false);
const saveError = ref("");
const statusBusy = ref(null);






const form = ref({

id:null,

code:"",

name:"",

facility_type_id:"",

location_id:"",

capacity:10,

description:"",

status:"active"

});

function statusLabel(status){
    return {
        active:"Aktif",
        inactive:"Tidak aktif",
        maintenance:"Pemeliharaan",
        pending:"Menunggu persetujuan"
    }[status] || status;
}

function facilityLocation(facility){
    return [
        facility.location?.name,
        facility.location?.building,
        facility.location?.floor ? `Lantai ${facility.location.floor}` : null,
    ].filter(Boolean).join(" · ");
}

function closeModal(){
    if(saving.value) return;
    modal.value=false;
    saveError.value="";
}








async function loadFacilities(){


try{


const res =
await axios.get(
"/api/admin/facilities?per_page=100",
{ timeout:10000 }
);



facilities.value =
res.data.data;
facilitiesError.value = "";


}

catch(err){

facilitiesError.value = "Data fasilitas belum dapat dimuat. Periksa koneksi server.";

console.log(err);

}

finally{
    facilitiesLoading.value = false;
}


}






async function loadMaster(){


try{


const [
typeRes,
locationRes
]=await Promise.all([


axios.get("/api/facility-types"),


axios.get("/api/locations")


]);



types.value =
typeRes.data.data;



locations.value =
locationRes.data.data;


}

catch(e){

console.log(e);

}


}






onMounted(()=>{


loadFacilities();


loadMaster();
loadSummary();

facilitiesTimer = window.setInterval(loadFacilities, 15000);


});

onUnmounted(()=>window.clearInterval(facilitiesTimer));









function openCreate(){


editing.value=false;
saveError.value="";


form.value={

id:null,

code:"",

name:"",

facility_type_id:"",

location_id:"",

capacity:10,

description:"",

status:"active"

};



modal.value=true;


}






function openEdit(item){


editing.value=true;
saveError.value="";


form.value={

id:item.id,

code:item.code,

name:item.name,

facility_type_id:
item.facility_type_id
||
item.type?.id,

location_id:
item.location_id
||
item.location?.id,

capacity:item.capacity,

description:item.description || "",

status:item.status

};



modal.value=true;


}









async function saveFacility(){


saveError.value="";

const payload={
    code:String(form.value.code || "").trim(),
    name:String(form.value.name || "").trim(),
    facility_type_id:form.value.facility_type_id,
    location_id:form.value.location_id,
    capacity:Number(form.value.capacity),
    description:String(form.value.description || "").trim()
};

if(!payload.code || !payload.name || !payload.facility_type_id || !payload.location_id || !Number.isInteger(payload.capacity) || payload.capacity < 1){
    saveError.value="Lengkapi kode, nama, tipe, lokasi, dan kapasitas minimal 1.";
    return;
}

saving.value=true;


try{


if(editing.value){


await axios.put(

`/api/admin/facilities/${form.value.id}`,

payload

);


}

else{


await axios.post(

"/api/admin/facilities",

payload

);


}



await loadFacilities();
modal.value=false;
notification.success(editing.value
    ? "Perubahan fasilitas berhasil disimpan."
    : "Fasilitas baru berhasil ditambahkan.");
}

catch(error){
    const validationErrors=error?.response?.data?.errors;
    saveError.value=validationErrors
        ? Object.values(validationErrors).flat()[0]
        : error?.response?.data?.message || "Fasilitas gagal disimpan ke server.";
}

finally{


saving.value=false;


}



}







async function toggleStatus(item){
    if(statusBusy.value || item.status === "maintenance") return;
    const target = item.status === "active" ? "nonaktif" : "aktif";
    await notification.confirm({
        title: 'Ubah status fasilitas',
        message: `Ubah status ${item.name} menjadi ${target}?`,
        confirmLabel: `Jadikan ${target}`,
        tone: item.status === "active" ? 'danger' : 'primary',
        onConfirm: async () => {
            statusBusy.value=item.id;
            saveError.value="";
            try{
                await axios.patch(`/api/admin/facilities/${item.id}/toggle-status`);
                notification.success(`Status ${item.name} berhasil diperbarui.`);
                await loadFacilities();
            } catch(error){
                throw new Error(error?.response?.data?.message || "Status fasilitas gagal diperbarui. Coba lagi.");
            } finally{
                statusBusy.value=null;
            }
        },
    });


}









const sortedFacilities =
computed(()=>{


return [...facilities.value]

.sort(
(a,b)=>
b.capacity-a.capacity
)

.slice(0,5);


});






const activities=[


{
text:"Reservasi REQ-102 disetujui",
time:"5 menit lalu"
},


{
text:"Fasilitas Lab Baru ditambahkan",
time:"30 menit lalu"
},


{
text:"Laporan kerusakan selesai",
time:"1 jam lalu"
}


];







function exportReport(){


window.open(

"/api/admin/export/reports",

"_blank"

);


}




</script>









<template>


<section

class="content-wrap"

id="screen-admin"

>





<div class="intro-row">


<div>


<p class="eyebrow">

RUANGKITA / ADMIN

</p>


<h1>

Pusat analitik



</h1>


<p class="subheading">

Monitor penggunaan fasilitas dan kelola seluruh sistem.

</p>


</div>






<div class="period">


<button

v-for="p in periods"

:key="p.value"

:class="{active:period===p.value}"

@click="period=p.value; loadSummary()"

>

{{p.label}}

</button>


</div>



</div>








<!-- KPI -->


<p v-if="saveError && !modal" class="admin-feedback error-box" role="alert">{{saveError}}</p>
<p v-if="summaryError" class="admin-feedback error-box" role="alert">{{summaryError}}</p>

<div class="stat-grid" :aria-busy="summaryLoading">


<article

v-for="item in kpi"

:key="item.title"

class="stat-card"

:class="`is-${item.type}`"

>


<div

:class="[

'stat-icon',

item.type

]"

>


{{item.icon}}

</div>



<div>


<span>
{{item.title}}
</span>


<strong>

{{summaryLoading ? '—' : item.value}}

</strong>


<small>

{{item.trend}}

</small>


</div>


</article>


</div>








<div class="admin-grid">







<!-- CHART -->

<div class="panel">


<h2>
Analitik penggunaan
</h2>


<p>
Persentase pemakaian per fasilitas
</p>




<div v-if="summaryLoading" class="chart-state" role="status">Memuat analitik penggunaan...</div>
<div v-else-if="!chart.length" class="chart-state">Belum ada data penggunaan pada periode ini.</div>
<div v-else class="usage-chart" aria-label="Grafik utilisasi fasilitas">
    <article v-for="c in chart" :key="c.id" class="usage-row">
        <div class="usage-row__header">
            <div><strong>{{c.name}}</strong><small>{{c.code}}</small></div>
            <span>{{c.value.toLocaleString('id-ID')}}%</span>
        </div>
        <div class="usage-track" role="progressbar" :aria-label="`Utilisasi ${c.name}`" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="c.value">
            <span class="usage-fill" :style="{ width: (c.value > 0 ? Math.max(c.value, 3) : 0) + '%' }"></span>
        </div>
    </article>
</div>


</div>








<!-- ACTIVITY -->


<div class="panel">


<h2>
Aktivitas terbaru
</h2>



<div

v-for="a in activities"

:key="a.text"

class="activity"

>


<div></div>


<p>

{{a.text}}

<small>

{{a.time}}

</small>


</p>


</div>


<button

@click="exportReport"

class="export"

>

Unduh rekap

</button>



</div>




</div>









<!-- FACILITY -->

<div class="panel facility-panel">


<div class="panel-header">


<div>


<h2>
Manajemen fasilitas
</h2>


<p>
CRUD fasilitas kampus
</p>


</div>



<button

@click="openCreate"

>

Tambah fasilitas

</button>


</div>








<div class="facility-table">

<p v-if="facilitiesError" class="admin-feedback error-box">
{{facilitiesError}}
</p>

<p v-else-if="facilitiesLoading" class="admin-feedback">
Memuat data fasilitas...
</p>

<div v-else-if="!facilities.length" class="admin-feedback">
Belum ada fasilitas. Tambahkan fasilitas pertama melalui tombol Tambah fasilitas.
</div>


<div

v-for="f in facilities"

:key="f.id"

class="facility-row"

>


<div>

<strong>
{{f.code}}
</strong>


<p>
{{f.name}}
</p>

<small>
{{f.type?.name || "Tipe tidak tersedia"}} · {{facilityLocation(f)}}
</small>


</div>



<span>

{{f.capacity}} orang

</span>



<b

:class="f.status"

>

{{statusLabel(f.status)}}

</b>



<div class="facility-actions">


<button

@click="openEdit(f)"

>

Edit

</button>



<button

@click="toggleStatus(f)"

:disabled="f.status === 'maintenance' || statusBusy === f.id"

>

{{statusBusy === f.id ? 'Menyimpan...' : f.status === 'maintenance' ? 'Dikelola petugas' : f.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'}}

</button>


</div>



</div>


</div>



</div>








<!-- MODAL -->


<Teleport to="body">


<div

v-if="modal"

class="overlay"

@click.self="closeModal"

@keydown.esc="closeModal"

>


<div class="modal" role="dialog" aria-modal="true" aria-labelledby="facility-dialog-title">


<h2 id="facility-dialog-title">

{{editing?'Edit':'Tambah'}} fasilitas

</h2>

<button type="button" class="modal-close" aria-label="Tutup" title="Tutup" :disabled="saving" @click="closeModal">×</button>





<label class="modal-field"><span>Kode fasilitas</span><input

v-model="form.code"

aria-label="Kode fasilitas"

placeholder="Contoh: RKU-101"

required

 /></label>



<label class="modal-field"><span>Nama fasilitas</span><input

v-model="form.name"

aria-label="Nama fasilitas"

placeholder="Nama fasilitas"

required

 /></label>





<label class="modal-field"><span>Tipe fasilitas</span><select
v-model="form.facility_type_id"
aria-label="Tipe fasilitas"
required
>
<option value="" disabled>Pilih tipe fasilitas</option>
<option
v-for="t in types"
:key="t.id"
:value="t.id"
>
{{t.name}}
</option>
</select></label>

<label class="modal-field"><span>Lokasi fasilitas</span><select
v-model="form.location_id"
aria-label="Lokasi fasilitas"
required
>
<option value="" disabled>Pilih lokasi</option>
<option
v-for="location in locations"
:key="location.id"
:value="location.id"
>
{{location.name}} · {{location.building}} · Lantai {{location.floor}}
</option>
</select></label>

<label class="modal-field"><span>Deskripsi <small>(opsional)</small></span><textarea
v-model="form.description"
aria-label="Deskripsi fasilitas"
placeholder="Deskripsi fasilitas"
rows="3"
></textarea></label>






<label class="modal-field"><span>Kapasitas</span><input

type="number"

v-model="form.capacity"

aria-label="Kapasitas fasilitas"

placeholder="Kapasitas"

min="1"

required

 /></label>




<p class="modal-helper">Kode, nama, tipe, lokasi, dan kapasitas wajib diisi.</p>
<p v-if="saveError" class="admin-feedback error-box" role="alert">{{saveError}}</p>

<div class="modal-action">


<button

type="button"

class="wf-button"

@click="closeModal"

>

Batal

</button>


<button

type="button"

class="wf-button wf-primary"

@click="saveFacility"

:disabled="saving"

>

{{saving?'Menyimpan...':'Simpan'}}

</button>


</div>



</div>


</div>


</Teleport>





</section>


</template>









<style scoped>



.period{


display:flex;


background:#f1f5f9;


padding:4px;


border-radius:12px;


}



.period button{


padding:8px 15px;


border-radius:9px;


font-size:12px;


}



.period .active{


background:white;


box-shadow:0 3px 10px #0001;


}





.admin-grid{


display:grid;


grid-template-columns:
1.4fr
0.6fr;


gap:22px;


margin-top:30px;


}



.panel{


background:white;


border:1px solid var(--line);


border-radius:25px;


padding:25px;


}



.panel h2{


margin:0;


}




.chart{


height:230px;


display:flex;


align-items:end;


gap:15px;


margin-top:30px;


}



.bar-wrapper{


flex:1;


height:100%;


display:flex;


flex-direction:column;


justify-content:end;


align-items:center;


gap:8px;


}



.bar{


width:100%;


background:#2563eb;


border-radius:10px 10px 0 0;


transition:.3s;


}




.activity{


display:flex;


gap:12px;


margin:20px 0;


}



.activity div{


width:10px;


height:10px;


border-radius:50%;


background:#2563eb;


margin-top:5px;


}



.activity p{


margin:0;


font-size:13px;


}



.activity small{


display:block;


color:#94a3b8;


}



.export{


margin-top:20px;


width:100%;


padding:12px;


border-radius:14px;


background:#0f172a;


color:white;


font-weight:700;


}






.panel-header{


display:flex;


justify-content:space-between;


align-items:center;


margin-bottom:20px;


}



.panel-header button{


background:#2563eb;


color:white;


padding:12px 18px;


border-radius:12px;


font-weight:700;


}




.facility-row{


display:grid;


grid-template-columns:
2fr
1fr
1fr
auto;


align-items:center;


padding:15px;


border-bottom:1px solid var(--line);


}



.facility-row p{


margin:3px 0;


color:#64748b;


font-size:12px;


}



.facility-row b{


padding:5px 10px;


border-radius:999px;


font-size:10px;


}



.facility-row .active{


background:#dcfce7;


color:#15803d;


}



.facility-row .inactive{


background:#fee2e2;


color:#dc2626;


}



.overlay{


position:fixed;


inset:0;


background:#0008;


display:grid;


place-items:center;


z-index:999;

padding:16px;


}



.modal{


background:white;

position:relative;


width:min(480px, 100%);

max-height:calc(100dvh - 32px);

overflow-y:auto;


padding:30px;


border-radius:16px;


display:grid;


gap:15px;


}

.modal-close{
    position:absolute;
    top:18px;
    right:18px;
    display:grid;
    width:36px;
    height:36px;
    place-items:center;
    border:1px solid var(--slate-300);
    border-radius:8px;
    background:#fff;
    color:var(--slate-700);
    font-size:22px;
    line-height:1;
}

.modal h2{
    padding-right:44px;
}



.modal input,
.modal select,
.modal textarea{


padding:12px;


border-radius:12px;


border:1px solid #ddd;


}

.modal-field{
    display:grid;
    gap:7px;
    color:var(--slate-700);
    font-size:13px;
    font-weight:700;
}

.modal-field small{
    font-weight:500;
}

.modal-field input,
.modal-field select,
.modal-field textarea{
    width:100%;
    min-width:0;
    color:var(--slate-900);
    font:inherit;
    font-weight:400;
}

.modal-helper{
    margin:0;
    color:var(--slate-600);
    font-size:12px;
    line-height:1.5;
}



.modal-action{


display:flex;


justify-content:end;


gap:10px;


}



.admin-feedback{
    margin:0;
    padding:24px 16px;
    border:1px dashed var(--line);
    border-radius:12px;
    color:var(--muted);
    text-align:center;
    font-size:13px;
}

.admin-feedback.error-box{
    border-style:solid;
    border-color:#fecaca;
    background:#fef2f2;
    color:var(--danger);
}

.admin-feedback.success-box{
    margin-bottom:16px;
    border-style:solid;
    border-color:#bbf7d0;
    background:#f0fdf4;
    color:#166534;
}

.modal :focus-visible,
#screen-admin button:focus-visible{
    outline:3px solid var(--blue-200);
    outline-offset:2px;
}

 





@media(max-width:900px){


.admin-grid{


grid-template-columns:1fr;


}



.facility-row{


grid-template-columns:1fr;


gap:10px;


}


}



.content-wrap#screen-admin {
    max-width: 1320px;
    padding: 34px 42px 56px;
}

#screen-admin .intro-row {
    align-items: center;
    margin-bottom: 26px;
}

#screen-admin h1 {
    color: #0f172a;
    font-size: clamp(34px, 4vw, 48px);
    letter-spacing: -1.8px;
}

#screen-admin .subheading {
    margin-top: 10px;
    color: #64748b;
}

#screen-admin .period {
    border: 1px solid #e2e8f0;
    background: #f1f5f9;
    border-radius: 10px;
}

#screen-admin .period button {
    min-width: 68px;
    color: #64748b;
}

#screen-admin .period button.active {
    color: #1e40af;
}

#screen-admin .stat-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin: 0 0 22px;
}

#screen-admin .stat-card {
    min-height: 128px;
    align-items: center;
    padding: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
}

#screen-admin .stat-card.is-blue {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

#screen-admin .stat-card.is-blue span,
#screen-admin .stat-card.is-blue small,
#screen-admin .stat-card.is-blue strong {
    color: #fff;
}

#screen-admin .stat-icon {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border-radius: 9px;
}

#screen-admin .stat-card > div:last-child span,
#screen-admin .stat-card > div:last-child small {
    color: #64748b;
    font-size: 11px;
}

#screen-admin .stat-card > div:last-child strong {
    display: block;
    margin: 6px 0 4px;
    color: #0f172a;
    font-size: 25px;
}

#screen-admin .admin-grid {
    grid-template-columns: minmax(0, 1.55fr) minmax(250px, .75fr);
    gap: 16px;
    margin-top: 0;
}

#screen-admin .panel {
    padding: 22px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
}

#screen-admin .panel h2 {
    font-size: 16px;
    color: #0f172a;
}

#screen-admin .panel > p,
#screen-admin .panel-header p {
    color: #64748b;
    font-size: 11px;
}

#screen-admin .chart {
    height: 250px;
    margin-top: 22px;
    padding: 14px 8px 0;
    border-top: 1px dashed #e2e8f0;
    background: repeating-linear-gradient(to bottom, transparent 0 38px, #f1f5f9 39px 40px);
}

#screen-admin .usage-chart {
    display:grid;
    gap:16px;
    max-height:320px;
    margin-top:22px;
    padding-right:6px;
    overflow-y:auto;
    overscroll-behavior:contain;
    scrollbar-gutter:stable;
}

#screen-admin .usage-chart::-webkit-scrollbar { width:6px; }
#screen-admin .usage-chart::-webkit-scrollbar-track { background:transparent; }
#screen-admin .usage-chart::-webkit-scrollbar-thumb { border-radius:999px; background:var(--slate-300); }
#screen-admin .usage-chart::-webkit-scrollbar-thumb:hover { background:var(--slate-400); }

#screen-admin .usage-row {
    display:grid;
    gap:8px;
}

#screen-admin .usage-row__header {
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:16px;
}

#screen-admin .usage-row__header > div {
    min-width:0;
}

#screen-admin .usage-row__header strong,
#screen-admin .usage-row__header small {
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

#screen-admin .usage-row__header strong {
    color:var(--slate-800);
    font-size:13px;
}

#screen-admin .usage-row__header small {
    margin-top:2px;
    color:var(--slate-500);
    font-size:10px;
}

#screen-admin .usage-row__header > span {
    flex-shrink:0;
    min-width:48px;
    padding:3px 8px;
    border-radius:999px;
    background:var(--blue-50);
    color:var(--blue-800);
    font-size:11px;
    font-weight:800;
    text-align:center;
}

#screen-admin .usage-track {
    height:10px;
    overflow:hidden;
    border:1px solid var(--blue-100);
    border-radius:999px;
    background:var(--slate-100);
}

#screen-admin .usage-fill {
    display:block;
    height:100%;
    border-radius:inherit;
    background:linear-gradient(90deg, var(--blue-600), var(--blue-400));
    box-shadow:0 0 12px rgba(37,99,235,.24);
    transition:width .35s ease;
}

#screen-admin .chart-state {
    display:grid;
    min-height:220px;
    place-items:center;
    margin-top:18px;
    border:1px dashed var(--slate-300);
    border-radius:12px;
    background:var(--slate-50);
    color:var(--slate-600);
    font-size:13px;
    text-align:center;
}

#screen-admin .bar-wrapper {
    gap: 7px;
}

#screen-admin .bar {
    max-width: 26px;
    border-radius: 8px 8px 2px 2px;
    background: #2563eb;
}

#screen-admin .activity {
    margin: 18px 0;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
}

#screen-admin .export {
    margin-top: 10px;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
}

#screen-admin .facility-panel {
    grid-column: 1 / -1;
    margin-top: 0;
}

@media (max-width: 1000px) {
    #screen-admin .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 720px) {
    .content-wrap#screen-admin { padding: 24px 16px 42px; }
    #screen-admin .intro-row { align-items: flex-start; flex-direction: column; }
    #screen-admin .admin-grid { grid-template-columns: 1fr; }
    #screen-admin .facility-panel { grid-column: auto; }
    .modal { padding:22px 18px; }
    .modal-action { flex-direction:column-reverse; }
    .modal-action button { width:100%; min-height:42px; }
}

/* Final admin layout contract. */
.content-wrap#screen-admin {
    width:min(100%, 1320px);
    padding:32px clamp(20px, 3vw, 40px) 56px;
}

#screen-admin .intro-row {
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:24px;
}

#screen-admin h1 {
    margin:5px 0 0;
    font-size:clamp(28px, 3vw, 38px);
    line-height:1.15;
    letter-spacing:-1.2px;
}

#screen-admin .period {
    flex-shrink:0;
    gap:2px;
    padding:4px;
}

#screen-admin .period button {
    min-height:36px;
    padding:7px 12px;
    border:0;
    background:transparent;
    white-space:nowrap;
}

#screen-admin .stat-grid {
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:14px;
    margin:0 0 18px;
}

#screen-admin .stat-card {
    display:flex;
    min-width:0;
    min-height:112px;
    align-items:center;
    gap:14px;
    padding:18px;
    border:1px solid var(--slate-200);
    border-radius:14px;
    background:#fff;
    box-shadow:0 8px 24px rgba(15,23,42,.05);
}

#screen-admin .stat-card > div:last-child {
    min-width:0;
}

#screen-admin .stat-card > div:last-child span {
    display:block;
    overflow-wrap:anywhere;
}

.app-shell #screen-admin .stat-card.is-blue,
.app-shell #screen-admin .stat-card.is-blue .stat-icon,
.app-shell #screen-admin .stat-card.is-blue > div:last-child span,
.app-shell #screen-admin .stat-card.is-blue > div:last-child strong,
.app-shell #screen-admin .stat-card.is-blue > div:last-child small {
    color:#fff;
}

#screen-admin .admin-grid {
    display:grid;
    grid-template-columns:minmax(0, 1.55fr) minmax(280px, .75fr);
    column-gap:20px;
    row-gap:24px;
    margin:0;
}

#screen-admin .panel {
    min-width:0;
    padding:20px;
    border:1px solid var(--slate-200);
    border-radius:14px;
    background:#fff;
    box-shadow:0 8px 24px rgba(15,23,42,.04);
}

#screen-admin .chart {
    width:100%;
    min-width:0;
    height:220px;
    gap:10px;
    overflow:hidden;
}

#screen-admin .bar-wrapper span {
    max-width:100%;
    overflow:hidden;
    color:var(--slate-600);
    font-size:10px;
    text-overflow:ellipsis;
    white-space:nowrap;
}

#screen-admin .panel-header {
    gap:16px;
    margin-bottom:12px;
}

#screen-admin .panel-header button,
#screen-admin .facility-actions button {
    min-height:38px;
    padding:8px 13px;
    border:1px solid var(--slate-300);
    border-radius:8px;
    background:#fff;
    color:var(--slate-700);
    font:inherit;
    font-size:12px;
    font-weight:700;
    white-space:nowrap;
}

#screen-admin .panel-header button {
    border-color:var(--blue-600);
    background:var(--blue-600);
    color:#fff;
}

#screen-admin .facility-table {
    min-width:0;
}

#screen-admin .facility-row {
    display:grid;
    grid-template-columns:minmax(220px, 1.7fr) minmax(90px, .45fr) minmax(90px, .45fr) auto;
    align-items:center;
    gap:16px;
    padding:16px 0;
}

#screen-admin .facility-row > * {
    min-width:0;
}

#screen-admin .facility-row small {
    display:block;
    overflow-wrap:anywhere;
    color:var(--slate-600);
}

#screen-admin .facility-row b {
    justify-self:start;
    white-space:nowrap;
}

#screen-admin .facility-actions {
    display:flex;
    justify-content:flex-end;
    gap:8px;
}

#screen-admin .facility-actions button:last-child {
    border-color:#fecaca;
    color:#b91c1c;
}

@media (max-width:1000px) {
    #screen-admin .stat-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
    #screen-admin .admin-grid { grid-template-columns:1fr; row-gap:18px; }
    #screen-admin .facility-panel { grid-column:auto; }
}

@media (max-width:720px) {
    .content-wrap#screen-admin { padding:24px 16px 40px; }
    #screen-admin .intro-row { align-items:flex-start; flex-direction:column; margin-bottom:18px; }
    #screen-admin .period { width:100%; overflow:hidden; }
    #screen-admin .period button { min-width:0; flex:1; }
    #screen-admin .stat-grid { gap:10px; }
    #screen-admin .stat-card { min-height:100px; padding:14px; gap:10px; }
    #screen-admin .panel { padding:16px; }
    #screen-admin .chart { height:190px; }
    #screen-admin .usage-chart { max-height:280px; }
    #screen-admin .panel-header { align-items:flex-start; }
    #screen-admin .facility-row {
        grid-template-columns:minmax(0, 1fr) auto;
        gap:10px 14px;
        padding:16px 0;
    }
    #screen-admin .facility-row > div:first-child { grid-column:1 / -1; }
    #screen-admin .facility-actions { grid-column:1 / -1; display:grid; grid-template-columns:1fr 1fr; }
    #screen-admin .facility-actions button { width:100%; }
}

@media (max-width:420px) {
    #screen-admin .stat-grid { grid-template-columns:1fr; }
    #screen-admin .stat-card { min-height:88px; }
    #screen-admin .panel-header { flex-direction:column; }
    #screen-admin .panel-header button { width:100%; }
}
</style>
