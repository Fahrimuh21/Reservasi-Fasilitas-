<script setup>

import {
    ref,
    computed,
    onMounted
} from "vue";

import axios from "axios";





/*
|--------------------------------------------------------------------------
| Reservation Approval Queue
|--------------------------------------------------------------------------
*/


const activeTab = ref("pending");



const reservations = ref([

{
id:"REQ-001",
user:"Budi Santoso",
facility:"Ruang Rapat Merapi",
date:"Hari ini • 13:00",
status:"pending",
avatar:"BS"
},


{
id:"REQ-002",
user:"Rina Wijaya",
facility:"Studio Kreatif",
date:"Besok • 10:00",
status:"pending",
avatar:"RW"
},


{
id:"REQ-003",
user:"Andi Saputra",
facility:"Lapangan Futsal",
date:"Kemarin",
status:"approved",
avatar:"AS"
},


{
id:"REQ-004",
user:"Siti Rahayu",
facility:"Lab Komputer",
date:"Kemarin",
status:"rejected",
avatar:"SR"
}

]);






const tabs=[

{
value:"pending",
label:"Menunggu"
},

{
value:"approved",
label:"Disetujui"
},

{
value:"rejected",
label:"Ditolak"
}

];






const filteredReservations =
computed(()=>{


return reservations.value.filter(

r=>r.status===activeTab.value

);


});






const countPending =
computed(()=>


reservations.value.filter(
r=>r.status==="pending"
).length


);


const countApproved =
computed(()=>


reservations.value.filter(
r=>r.status==="approved"
).length


);


const countRejected =
computed(()=>


reservations.value.filter(
r=>r.status==="rejected"
).length


);







function approve(id){


const item =
reservations.value.find(
x=>x.id===id
);


if(item)
item.status="approved";


}




function reject(id){


const item =
reservations.value.find(
x=>x.id===id
);


if(item)
item.status="rejected";


}









/*
|--------------------------------------------------------------------------
| Facility Maintenance
|--------------------------------------------------------------------------
*/


const facilities = ref([]);

const loading = ref(false);



async function loadFacilities(){


try{


const res =
await axios.get(
"/api/officer/facilities?per_page=100"
);



facilities.value =
res.data.data;


}

catch(err){

console.error(err);

}


}





onMounted(loadFacilities);






async function maintenance(item){


if(
!confirm(
`Set ${item.name} menjadi maintenance?`
)
)
return;



loading.value=true;



try{


await axios.patch(

`/api/officer/facilities/${item.id}/set-maintenance`

);



await loadFacilities();


}


finally{


loading.value=false;


}


}








async function restore(item){


if(
!confirm(
`Aktifkan kembali ${item.name}?`
)
)
return;



loading.value=true;



try{


await axios.patch(

`/api/officer/facilities/${item.id}/complete-maintenance`

);



await loadFacilities();


}


finally{


loading.value=false;


}


}





</script>








<template>


<section

class="content-wrap"

id="screen-officer"

>



<div class="intro-row">


<div>


<p class="eyebrow">

OFFICER CONTROL CENTER

</p>


<h1>

Operations Dashboard

<span class="sun">

✦

</span>


</h1>


<p class="subheading">

Kelola approval reservasi dan kondisi fasilitas kampus.

</p>


</div>



</div>









<!-- KPI -->


<div class="stat-grid">



<article class="stat-card">


<div class="stat-icon yellow-bg">

◷

</div>


<div>

<span>

Pending Approval

</span>


<strong>

{{countPending}}

</strong>


<small>

Menunggu tindakan

</small>


</div>


</article>





<article class="stat-card">


<div class="stat-icon green-bg">

✓

</div>


<div>

<span>

Approved

</span>


<strong>

{{countApproved}}

</strong>


<small>

Hari ini

</small>


</div>


</article>





<article class="stat-card">


<div class="stat-icon coral-bg">

⚠

</div>


<div>

<span>

Rejected

</span>


<strong>

{{countRejected}}

</strong>


<small>

Ditolak

</small>


</div>


</article>


</div>









<div class="officer-grid">







<!-- APPROVAL -->

<div class="panel">


<div class="panel-header">


<div>

<h2>

Reservation Queue

</h2>


<p>

Review permintaan pengguna

</p>


</div>



</div>






<div class="tabs">


<button

v-for="t in tabs"

:key="t.value"

:class="{

active:
activeTab===t.value

}"

@click="
activeTab=t.value
"


>

{{t.label}}

</button>


</div>







<TransitionGroup

name="list"

class="queue"

>



<div

v-for="item in filteredReservations"

:key="item.id"

class="request"

>



<div class="avatar">

{{item.avatar}}

</div>





<div class="request-info">


<strong>

{{item.facility}}

</strong>


<p>

{{item.user}}

</p>


<small>

{{item.date}}

</small>


</div>







<span

:class="[

'status',

item.status

]"

>


{{item.status}}

</span>







<div

v-if="
item.status==='pending'
"

class="actions"


>


<button

class="approve"

@click="
approve(item.id)
"

>

✓

</button>


<button

class="reject"

@click="
reject(item.id)
"

>

×


</button>


</div>





</div>



</TransitionGroup>





<div

v-if="
!filteredReservations.length
"

class="empty"

>

Tidak ada request.

</div>



</div>









<!-- FACILITY -->

<div class="panel dark">



<div class="panel-header">


<div>

<h2>

Facility Health

</h2>


<p>

Monitoring kondisi sarana

</p>


</div>


</div>






<div class="facility-list">



<div

v-for="f in facilities"

:key="f.id"

class="facility"

>



<div>


<strong>

{{f.name}}

</strong>


<p>

{{

typeof f.location==="string"

?

f.location

:

f.location?.name

}}

</p>


</div>






<span

:class="[

'facility-status',

f.status

]"

>


{{f.status}}

</span>







<button

v-if="
f.status==='active'
"

@click="
maintenance(f)
"

>

🔧

</button>




<button

v-else

@click="
restore(f)
"

>

✓

</button>




</div>





</div>


</div>








</div>







</section>


</template>










<style scoped>



.officer-grid{


display:grid;


grid-template-columns:
1fr
1fr;


gap:24px;


margin-top:30px;


}







.panel{


background:white;


border:1px solid var(--line);


border-radius:25px;


padding:25px;


}



.panel.dark{


background:#0f172a;


color:white;


border:none;


}



.panel-header h2{


margin:0;


font-size:20px;


}



.panel-header p{


margin-top:5px;


font-size:12px;


color:#94a3b8;


}






.tabs{


display:flex;


gap:8px;


margin:20px 0;


}



.tabs button{


padding:8px 15px;


border-radius:999px;


background:#f8fafc;


font-size:12px;


font-weight:700;


}



.tabs button.active{


background:#2563eb;


color:white;


}








.queue{


display:grid;


gap:12px;


}



.request{


display:flex;


align-items:center;


gap:14px;


padding:16px;


border-radius:18px;


background:#f8fafc;


}



.avatar{


width:42px;


height:42px;


border-radius:14px;


background:#2563eb;


color:white;


display:grid;


place-items:center;


font-weight:800;


}



.request-info{


flex:1;


}



.request-info strong{


font-size:13px;


}



.request-info p{


margin:3px 0;


font-size:12px;


}



.request-info small{


color:#94a3b8;


}





.status{


padding:6px 10px;


font-size:10px;


border-radius:999px;


font-weight:800;


}



.status.pending{


background:#fef3c7;


color:#b45309;


}



.status.approved{


background:#dcfce7;


color:#15803d;


}



.status.rejected{


background:#fee2e2;


color:#dc2626;


}







.actions{


display:flex;


gap:5px;


}



.actions button{


width:32px;


height:32px;


border-radius:10px;


font-weight:bold;


}



.approve{


background:#dcfce7;


color:#15803d;


}



.reject{


background:#fee2e2;


color:#dc2626;


}








.facility-list{


display:grid;


gap:12px;


margin-top:20px;


}



.facility{


display:flex;


align-items:center;


gap:12px;


padding:15px;


background:
rgba(255,255,255,.06);


border-radius:16px;


}



.facility div{


flex:1;


}



.facility p{


margin:3px 0;


font-size:12px;


color:#94a3b8;


}



.facility button{


background:white;


border-radius:10px;


width:35px;


height:35px;


}



.facility-status{


font-size:10px;


padding:5px 10px;


border-radius:999px;


}



.facility-status.active{


background:#dcfce7;


color:#15803d;


}



.facility-status.maintenance{


background:#fef3c7;


color:#b45309;


}





.empty{


padding:40px;


text-align:center;


color:#94a3b8;


}





.list-enter-active,
.list-leave-active{


transition:.25s;


}



.list-enter-from{


opacity:0;


transform:translateX(-15px);


}





@media(max-width:900px){


.officer-grid{


grid-template-columns:1fr;


}


}



</style>