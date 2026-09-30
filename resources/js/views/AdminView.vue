<script setup>

import {
    ref,
    computed,
    onMounted,
    onUnmounted
} from "vue";

import axios from "axios";





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







const chart = computed(() => facilitySummary.value.slice(0, 7).map(facility => ({
    day: facility.name.length > 12 ? `${facility.name.slice(0, 12)}...` : facility.name,
    value: facility.usage || 0,
})));






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


await axios.patch(

`/api/admin/facilities/${item.id}/toggle-status`

);


loadFacilities();


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

ADMIN CONTROL

</p>


<h1>

Analytics Center

<span class="sun">

<p v-if="saveError" class="admin-feedback error-box">
{{saveError}}
</p>
✦
</span>


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


<div class="stat-grid">

<p v-if="summaryError" class="admin-feedback error-box">
{{summaryError}}
</p>


<article

v-for="item in kpi"

:key="item.title"

class="stat-card"

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

{{item.value}}

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
Usage Analytics
</h2>


<p>
Reservasi mingguan
</p>




<div class="chart">


<div

v-for="c in chart"

:key="c.day"

class="bar-wrapper"

>


<div

class="bar"

:style="{
height:c.value+'%'
}"

></div>


<span>

{{c.day}}

</span>


</div>


</div>


</div>








<!-- ACTIVITY -->


<div class="panel">


<h2>
Activity Log
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

⬇ Export Report

</button>



</div>




</div>









<!-- FACILITY -->

<div class="panel facility-panel">


<div class="panel-header">


<div>


<h2>
Facility Management
</h2>


<p>
CRUD fasilitas kampus
</p>


</div>



<button

@click="openCreate"

>

＋ Add Facility

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
Belum ada fasilitas. Tambahkan fasilitas pertama melalui tombol Add Facility.
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



<div>


<button

@click="openEdit(f)"

>

Edit

</button>



<button

@click="toggleStatus(f)"

:disabled="f.status === 'maintenance'"

>

{{f.status === 'maintenance' ? 'Petugas' : 'Toggle'}}

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

@click.self="modal=false"

>


<div class="modal">


<h2>

{{editing?'Edit':'Tambah'}}

Facility

</h2>





<input

v-model="form.code"

placeholder="Code"

required

/>



<input

v-model="form.name"

placeholder="Name"

required

/>





<select
v-model="form.facility_type_id"
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
</select>

<select
v-model="form.location_id"
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
</select>

<textarea
v-model="form.description"
placeholder="Deskripsi fasilitas"
rows="3"
></textarea>






<input

type="number"

v-model="form.capacity"

placeholder="Capacity"

min="1"

required

/>




<div class="modal-action">


<button

type="button"

@click="modal=false"

>

Cancel

</button>


<button

type="button"

@click="saveFacility"

:disabled="saving"

>

{{saving?'Saving':'Save'}}

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


}



.modal{


background:white;


width:400px;


padding:30px;


border-radius:25px;


display:grid;


gap:15px;


}



.modal input,
.modal select,
.modal textarea{


padding:12px;


border-radius:12px;


border:1px solid #ddd;


}



.modal-action{


display:flex;


justify-content:end;


gap:10px;


}



.modal-action button:last-child{

background:#2563eb;

color:white;

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

#screen-admin .stat-card:first-of-type {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

#screen-admin .stat-card:first-of-type span,
#screen-admin .stat-card:first-of-type small,
#screen-admin .stat-card:first-of-type strong {
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
    color: #94a3b8;
    font-size: 11px;
}

#screen-admin .chart {
    height: 250px;
    margin-top: 22px;
    padding: 14px 8px 0;
    border-top: 1px dashed #e2e8f0;
    background: repeating-linear-gradient(to bottom, transparent 0 38px, #f1f5f9 39px 40px);
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
}
</style>
