<script setup>

import {
    ref,
    computed,
    onMounted,
    onUnmounted
} from "vue";

import {
    useRouter
} from "vue-router";

import axios from "axios";

import {
    hasRole,
    isAuthenticated
} from "../auth";
import notification from '../components/notification/notificationService';



const router = useRouter();



const searchQuery = ref("");

const selectedType = ref("all");

const facilities = ref([]);
const facilityTypes = ref([
    { value:"all", label:"Semua" }
]);

const loading = ref(true);
const loadError = ref("");
const lastUpdated = ref(null);
let fetching = false;
let refreshTimer;



const timeSlots = ref([]);



for(let h=7;h<20;h++){

timeSlots.value.push(
`${String(h).padStart(2,"0")}:00`
);


timeSlots.value.push(
`${String(h).padStart(2,"0")}:30`
);


}






async function fetchFacilities(){
if(fetching) return;
fetching = true;


try{


const [res, typesRes] = await Promise.all([
    axios.get("/api/facilities", { params: { per_page: 100 } }),
    axios.get("/api/facility-types")
]);

facilityTypes.value = [
    { value:"all", label:"Semua" },
    ...typesRes.data.data.map(type => ({
        value: String(type.id),
        label: type.name
    }))
];



facilities.value =
res.data.data.map(f=>{


const type = typeof f.type === "object" && f.type?.id
    ? String(f.type.id)
    : "unknown";
const typeName = typeof f.type === "object"
    ? f.type?.name || "Tipe tidak tersedia"
    : f.type || "Tipe tidak tersedia";
const location = typeof f.location === "string"
    ? f.location
    : [f.location?.name, f.location?.building, f.location?.floor]
        .filter(Boolean)
        .join(" · ");





const slots={};



if(f.availability_today){

f.availability_today.forEach(s=>{

slots[s.start] =
s.status==="tersedia"
?"available"
:"booked";

});


}



return {


id:f.id,

code:f.code,

name:f.name,

type,

typeName,

capacity:f.capacity,


location,

description:f.description || "Tidak ada deskripsi fasilitas.",



slots,


status:f.status || "active"


};



});
loadError.value = "";
lastUpdated.value = new Date();



}
catch(err){
loadError.value = "Ketersediaan belum dapat diperbarui. Periksa koneksi Anda.";

console.error(
"facility error",
err
);

}


finally{

loading.value=false;
fetching=false;

}


}




onMounted(() => {
fetchFacilities();
refreshTimer = window.setInterval(() => { if(!document.hidden) fetchFacilities(); }, 3000);
});
onUnmounted(() => window.clearInterval(refreshTimer));






const filteredFacilities =
computed(()=>{


return facilities.value.filter(f=>{


const matchType =
selectedType.value==="all"
||
f.type===selectedType.value;



const keyword =
searchQuery.value
.toLowerCase();



const matchSearch =

f.name
.toLowerCase()
.includes(keyword)

||

f.location
.toLowerCase()
.includes(keyword);



return matchType && matchSearch;


});


});





function availableCount(f){


return Object.values(
f.slots || {}
)
.filter(
x=>x==="available"
)
.length;


}

function statusLabel(status){
    return {
        active:"Aktif",
        inactive:"Tidak aktif",
        maintenance:"Pemeliharaan",
        pending:"Menunggu persetujuan"
    }[status] || status;
}





function reserve(id, slot = null){


if(!isAuthenticated()){

notification.info('Login terlebih dahulu untuk melakukan reservasi.');

router.push({
name:"login"
});

return;

}

if(!hasRole("user")){

notification.warning('Hanya pengguna yang dapat mengajukan reservasi.');

return;

}


router.push({
name:"reservations",
query:{
facility_id:id,
...(slot ? {slot} : {})
}
});


}



</script>





<template>


<section
class="content-wrap"
id="screen-facilities"
>
<p v-if="loadError" role="alert">{{loadError}}</p>



<!-- HEADER -->

<div class="intro-row">


<div>


<p class="eyebrow">
FACILITY CATALOG
</p>


<h1>
Explore Facilities
</h1>


<p class="subheading">

Cari ruang terbaik dan lihat ketersediaan secara realtime.

</p>

<small v-if="lastUpdated" class="refresh-status">
Data terakhir diperbarui {{lastUpdated.toLocaleTimeString()}}
</small>


</div>


</div>








<!-- SEARCH -->

<div class="toolbar">


<div class="search-box">


<span>
⌕
</span>


<input

v-model="searchQuery"

placeholder="
Cari fasilitas atau lokasi...
"

>


</div>





<div class="filters">


<button

v-for="t in facilityTypes"

:key="t.value"

:class="[
'filter',
{
active:selectedType===t.value
}
]"

@click="
selectedType=t.value
"

>


{{t.label}}


</button>
</div>


</div>







<!-- LOADING -->


<div
v-if="loading"
class="facility-grid"
>


<div

v-for="i in 6"

:key="i"

class="facility-card skeleton"

>

</div>


</div>







<!-- CARD -->

<div

v-else

class="facility-grid"

>



<article

v-for="f in filteredFacilities"

:key="f.id"

class="facility-card"

>





<div class="card-header">


<div

class="facility-symbol"

>

▦

</div>


<div>


<h3>
{{f.name}}
</h3>


<p>
{{f.location}}
</p>

<small>
{{f.code}} · {{f.typeName}}
</small>


</div>


<span

:class="[
'status-pill',
f.status
]"

>

{{statusLabel(f.status)}}

</span>


</div>

<p class="facility-description">
{{f.description}}
</p>






<div class="meta">


<div>

<span>
Kapasitas
</span>

<strong>
{{f.capacity}}
Orang
</strong>

</div>



<div>

<span>
Slot tersedia
</span>

<strong>
{{availableCount(f)}}
</strong>


</div>


</div>








<div class="slots">


<button

v-for="slot in timeSlots"

:key="slot"

:class="[
'slot',
f.slots[slot]
]"

:disabled="
f.slots[slot]==='booked'
"

@click="
reserve(f.id, slot)
"


>


{{slot}}

</button>


</div>






<button

class="reserve-btn"

@click="
reserve(f.id)
"

>


Reservasi Sekarang →

</button>






</article>







<div

v-if="
filteredFacilities.length===0
"

class="empty"

>


<div>
⌂
</div>


<h3>
Tidak ada fasilitas ditemukan
</h3>


<p>
Coba ubah kata pencarian atau filter.
</p>


</div>






</div>



</section>


</template>









<style scoped>



.toolbar{


display:flex;


justify-content:space-between;


align-items:center;


gap:20px;


margin:35px 0;


flex-wrap:wrap;


}




.search-box{


height:44px;


width:340px;


display:flex;


align-items:center;


gap:10px;


background:white;


border:1px solid var(--line);


padding:0 16px;


border-radius:14px;


}



.search-box input{


border:0;


outline:0;


width:100%;


font-size:13px;


}





.filters{


display:flex;


gap:8px;


}




.filter{


padding:9px 18px;


border-radius:999px;


background:white;


border:1px solid var(--line);


font-size:12px;


font-weight:700;


color:#64748b;


transition:.2s;


}



.filter.active,
.filter:hover{


background:var(--primary);


color:white;


border-color:var(--primary);


}






.facility-grid{


display:grid;


grid-template-columns:
repeat(
auto-fill,
minmax(320px,1fr)
);


gap:22px;


}






.facility-card{


background:white;


border:1px solid var(--line);


border-radius:24px;


padding:22px;


transition:.3s;


}



.facility-card:hover{


transform:
translateY(-6px);


box-shadow:
0 20px 45px
rgba(15,23,42,.1);


}




.card-header{


display:flex;


align-items:center;


gap:14px;


}



.facility-symbol{


width:46px;


height:46px;


border-radius:16px;


display:grid;


place-items:center;


background:
var(--primary-soft);


color:var(--primary);


font-size:22px;


}



.card-header h3{


margin:0;


font-size:16px;


}



.card-header p{


margin:4px 0 0;


font-size:12px;


color:#64748b;


}





.status-pill{


margin-left:auto;


font-size:10px;


padding:5px 10px;


border-radius:999px;


background:#dcfce7;


color:#166534;


}



.status-pill.inactive{


background:#fee2e2;


color:#991b1b;


}






.meta{


display:flex;


gap:40px;


margin:22px 0;


padding:15px;


border-radius:16px;


background:#f8fafc;


}



.meta span{


display:block;


font-size:11px;


color:#64748b;


}



.meta strong{


display:block;


margin-top:5px;


font-size:14px;


}







.slots{


display:grid;


grid-template-columns:
repeat(4,1fr);


gap:6px;


}



.slot{


height:32px;


border-radius:8px;


font-size:10px;


font-weight:700;


}



.slot.available{


background:#dbeafe;


color:#2563eb;


}



.slot.available:hover{


background:#2563eb;


color:white;


}




.slot.booked{


background:#f1f5f9;


color:#cbd5e1;


cursor:not-allowed;


}





.reserve-btn{


margin-top:20px;


width:100%;


padding:13px;


border-radius:14px;


background:#0f172a;


color:white;


font-weight:700;


transition:.2s;


}



.reserve-btn:hover{


background:var(--primary);


}






.skeleton{


height:320px;


background:

linear-gradient(
110deg,
#f1f5f9,
#ffffff,
#f1f5f9
);


animation:
loading 1.5s infinite;


}



@keyframes loading{


0%{
opacity:.5;
}


50%{
opacity:1;
}


100%{
opacity:.5;
}


}





.empty{


grid-column:1/-1;


text-align:center;


padding:80px;


}



.empty div{


font-size:50px;


}



.empty h3{


margin:10px;


}





@media(max-width:700px){


.search-box{

width:100%;

}


.slots{

grid-template-columns:
repeat(3,1fr);

}


}



</style>
