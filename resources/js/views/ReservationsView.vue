<script setup>

import {
    ref,
    computed
} from "vue";

import {
    useRouter
} from "vue-router";


const router = useRouter();



const search = ref("");

const showModal = ref(false);

const selectedDate = ref("12 Jun");





const reservations = ref([

{
id:1,
name:"Ruang Rapat Merapi",
type:"Meeting Room",
date:"12 Jun 2026",
time:"10:00 - 12:00",
status:"Confirmed",
capacity:20,
icon:"▦"
},


{
id:2,
name:"Lab Komputer Rinjani",
type:"Laboratorium",
date:"15 Jun 2026",
time:"13:00 - 15:00",
status:"Pending",
capacity:35,
icon:"💻"
},


{
id:3,
name:"Studio Kreatif",
type:"Creative Space",
date:"20 Jun 2026",
time:"09:00 - 11:00",
status:"Confirmed",
capacity:15,
icon:"✦"
}

]);





const filteredReservations = computed(()=>{


return reservations.value.filter(r=>


r.name
.toLowerCase()
.includes(
search.value.toLowerCase()
)


);


});






function cancelReservation(id){


const confirmCancel =
confirm(
"Batalkan reservasi ini?"
);


if(confirmCancel){


reservations.value =
reservations.value.filter(
r=>r.id!==id
);


}


}





function createReservation(){


reservations.value.unshift({

id:Date.now(),

name:"Ruang Diskusi Bromo",

type:"Meeting Room",

date:selectedDate.value,

time:"14:00 - 16:00",

status:"Confirmed",

capacity:10,

icon:"▦"


});


showModal.value=false;


}







function explore(){


router.push({
name:"facilities"
});


}





const calendarDays =
Array.from(
{length:30},
(_,i)=>i+1
);



</script>







<template>


<section
class="content-wrap"
id="screen-reservations"
>



<div class="intro-row">


<div>

<p class="eyebrow">
MY RESERVATIONS
</p>


<h1>
Your Workspace
<span class="sun">
✦
</span>
</h1>


<p class="subheading">

Kelola jadwal ruangan dan reservasi fasilitas Anda.

</p>


</div>




<button

class="primary-button"

@click="
showModal=true
"

>


＋ Booking Baru

</button>



</div>








<!-- SUMMARY -->


<div class="stat-grid">


<article class="stat-card">


<div class="stat-icon blue-bg">
◷
</div>


<div>

<span>
Upcoming
</span>


<strong>
{{reservations.length}}
</strong>


<small>
Booking aktif
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
{{

reservations.filter(
r=>r.status==="Confirmed"
).length

}}
</strong>


<small>
Disetujui
</small>


</div>


</article>





<article class="stat-card">


<div class="stat-icon yellow-bg">
⌂
</div>


<div>

<span>
Hours Reserved
</span>


<strong>
12.5
</strong>


<small>
Bulan ini
</small>


</div>


</article>


</div>









<div class="reservation-layout">





<!-- LEFT -->

<div>


<div class="section-head">


<div>

<h2>
Upcoming Booking
</h2>


<p>
Reservasi fasilitas Anda
</p>


</div>




<div class="search">


<span>
⌕
</span>


<input

v-model="search"

placeholder="
Cari reservasi...
"

>


</div>


</div>






<div class="booking-list">



<article

v-for="item in filteredReservations"

:key="item.id"

class="booking-card"

>



<div class="booking-icon">

{{item.icon}}

</div>



<div class="booking-info">


<h3>
{{item.name}}
</h3>


<p>
{{item.type}}
</p>


<div class="booking-meta">


<span>
📅 {{item.date}}
</span>


<span>
⏱ {{item.time}}
</span>


<span>
👥 {{item.capacity}}
orang
</span>


</div>



</div>





<div class="booking-actions">


<span

:class="[

'status',

item.status.toLowerCase()

]"

>


{{item.status}}


</span>



<button

@click="
cancelReservation(item.id)
"

>

Cancel

</button>


</div>






</article>






<div

v-if="
filteredReservations.length===0
"

class="empty"

>

<h3>
Belum ada reservasi
</h3>


<p>
Mulai booking fasilitas favoritmu.
</p>



<button

@click="explore"

>

Explore Facility

</button>


</div>



</div>


</div>








<!-- RIGHT CALENDAR -->

<div class="calendar-card">


<h2>
June 2026
</h2>


<p>
Pilih tanggal booking
</p>




<div class="calendar">


<div

v-for="d in calendarDays"

:key="d"

:class="[

'day',

{
active:d===12
}

]"

@click="
selectedDate=`${d} Jun 2026`
"


>


{{d}}


</div>


</div>






<div class="calendar-tip">


<strong>
Quick Tip
</strong>


<p>

Booking lebih awal meningkatkan peluang mendapatkan ruangan.

</p>



</div>


</div>






</div>







<!-- MODAL -->


<Teleport to="body">


<div

v-if="showModal"

class="modal-bg"

@click.self="
showModal=false
"

>


<div class="modal">


<button

class="close"

@click="
showModal=false
"

>

×


</button>



<h2>
New Reservation
</h2>


<p>
Tanggal:
<strong>
{{selectedDate}}
</strong>
</p>



<button

class="primary-button full"

@click="
createReservation
"

>

Confirm Booking →

</button>



</div>


</div>


</Teleport>





</section>


</template>








<style scoped>



.reservation-layout{


display:grid;


grid-template-columns:
1.3fr
0.7fr;


gap:24px;


margin-top:30px;


}






.section-head{


display:flex;


justify-content:space-between;


align-items:center;


margin-bottom:20px;


}



.section-head h2{


margin:0;


font-size:20px;


}





.booking-list{


display:grid;


gap:14px;


}



.booking-card{


display:flex;


align-items:center;


gap:16px;


background:white;


padding:20px;


border-radius:22px;


border:1px solid var(--line);


transition:.25s;


}



.booking-card:hover{


transform:
translateY(-4px);


box-shadow:
0 20px 40px
rgba(15,23,42,.08);


}





.booking-icon{


width:52px;


height:52px;


border-radius:18px;


background:
var(--primary-soft);


display:grid;


place-items:center;


font-size:24px;


}





.booking-info{


flex:1;


}



.booking-info h3{


margin:0;


font-size:15px;


}



.booking-info p{


margin:4px 0;


color:#64748b;


font-size:12px;


}




.booking-meta{


display:flex;


gap:15px;


font-size:11px;


color:#64748b;


}





.booking-actions{


display:flex;


flex-direction:column;


gap:10px;


align-items:end;


}



.booking-actions button{


background:#fee2e2;


color:#dc2626;


padding:7px 12px;


border-radius:10px;


font-size:11px;


font-weight:700;


}





.status{


padding:6px 12px;


border-radius:999px;


font-size:10px;


font-weight:800;


}



.status.confirmed{


background:#dcfce7;


color:#15803d;


}


.status.pending{


background:#fef3c7;


color:#b45309;


}








.calendar-card{


background:#0f172a;


color:white;


border-radius:26px;


padding:26px;


height:max-content;


}



.calendar-card p{


color:#94a3b8;


font-size:13px;


}





.calendar{


display:grid;


grid-template-columns:
repeat(7,1fr);


gap:8px;


margin-top:25px;


}



.day{


height:38px;


display:grid;


place-items:center;


border-radius:10px;


font-size:12px;


cursor:pointer;


background:
rgba(255,255,255,.05);


}



.day:hover,
.day.active{


background:
#2563eb;


}





.calendar-tip{


margin-top:25px;


background:
rgba(255,255,255,.08);


padding:15px;


border-radius:15px;


}





.empty{


padding:50px;


text-align:center;


}



.empty button{


margin-top:15px;


padding:12px 20px;


border-radius:12px;


background:#2563eb;


color:white;


}






.modal-bg{


position:fixed;


inset:0;


background:
rgba(15,23,42,.45);


display:grid;


place-items:center;


z-index:200;


}



.modal{


background:white;


padding:35px;


border-radius:25px;


width:360px;


position:relative;


}



.close{


position:absolute;


right:15px;


top:15px;


background:none;


font-size:25px;


}



.full{


width:100%;


margin-top:20px;


}




@media(max-width:900px){


.reservation-layout{


grid-template-columns:1fr;


}



.booking-card{


flex-direction:column;


align-items:flex-start;


}


}


</style>