<script setup>

import {
    ref,
    computed,
    onMounted
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

reservations:148,

usage:"78",

reports:7,

users:64

});








const kpi = computed(()=>[


{
title:"Total Reservasi",
value:
period.value==="week"
?"42"
:
period.value==="year"
?"820"
:
stats.value.reservations,

icon:"◷",

trend:"+12%",

type:"blue"

},


{
title:"Utilisasi Fasilitas",
value:
stats.value.usage+"%",

icon:"▦",

trend:"+5%",

type:"green"

},


{
title:"Laporan Kerusakan",
value:
stats.value.reports,

icon:"⚠",

trend:"-3%",

type:"yellow"

},


{
title:"Pengguna Aktif",

value:
stats.value.users,

icon:"◉",

trend:"+18%",

type:"purple"

}


]);







/*
|--------------------------------------------------------------------------
| Chart Dummy Data
|--------------------------------------------------------------------------
*/


const chart=[

{
day:"Sen",
value:40
},

{
day:"Sel",
value:65
},

{
day:"Rab",
value:45
},

{
day:"Kam",
value:85
},

{
day:"Jum",
value:70
},

{
day:"Sab",
value:55
},

{
day:"Min",
value:90
}

];






/*
|--------------------------------------------------------------------------
| Facility Management
|--------------------------------------------------------------------------
*/


const facilities = ref([]);

const types = ref([]);

const locations = ref([]);


const modal = ref(false);

const editing = ref(false);



const saving = ref(false);






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








async function loadFacilities(){


try{


const res =
await axios.get(
"/api/admin/facilities?per_page=100"
);



facilities.value =
res.data.data;


}

catch(err){

console.log(err);

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


});









function openCreate(){


editing.value=false;


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


saving.value=true;


try{


if(editing.value){


await axios.put(

`/api/admin/facilities/${form.value.id}`,

form.value

);


}

else{


await axios.post(

"/api/admin/facilities",

form.value

);


}



modal.value=false;


await loadFacilities();


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

@click="period=p.value"

>

{{p.label}}

</button>


</div>



</div>








<!-- KPI -->


<div class="stat-grid">


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


</div>



<span>

{{f.capacity}} User

</span>



<b

:class="f.status"

>

{{f.status}}

</b>



<div>


<button

@click="openEdit(f)"

>

Edit

</button>



<button

@click="toggleStatus(f)"

>

Toggle

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

/>



<input

v-model="form.name"

placeholder="Name"

/>





<select

v-model="form.facility_type_id"

>

<option

v-for="t in types"

:key="t.id"

:value="t.id"

>

{{t.name}}

</option>

</select>






<input

type="number"

v-model="form.capacity"

placeholder="Capacity"

/>




<div class="modal-action">


<button

@click="modal=false"

>

Cancel

</button>


<button

@click="saveFacility"

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
.modal select{


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





@media(max-width:900px){


.admin-grid{


grid-template-columns:1fr;


}



.facility-row{


grid-template-columns:1fr;


gap:10px;


}


}



</style>
