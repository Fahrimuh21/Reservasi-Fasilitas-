<script setup>

import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

import {
    Clock3,
    Building2,
    CheckCircle2,
    Dumbbell,
    Palette,
    DoorOpen,
    Plus,
    Search,
    MoreHorizontal,
    ArrowRight,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';



const router = useRouter();


const selectedDate = ref('12 Jun');

const showModal = ref(false);

const search = ref('');



const reservations = ref([

    {
        name:'Ruang Rapat Merapi',
        type:'Meeting room',
        date:'Today, 10:00 - 12:00',
        color:'coral',
        status:'Confirmed'
    },

    {
        name:'Lapangan Futsal A',
        type:'Sports facility',
        date:'Thu, 13 Jun, 16:00 - 18:00',
        color:'blue',
        status:'Confirmed'
    },

    {
        name:'Studio Kreatif',
        type:'Creative space',
        date:'Sat, 15 Jun, 09:00 - 11:00',
        color:'yellow',
        status:'Pending'
    }

]);



const filteredReservations = computed(()=>{

    return reservations.value.filter(item =>

        item.name
        .toLowerCase()
        .includes(
            search.value.toLowerCase()
        )

    );

});



function addReservation(){

    reservations.value.unshift({

        name:'Ruang Diskusi Bromo',

        type:'Meeting room',

        date:
        `${selectedDate.value}, 14:00 - 16:00`,

        color:'green',

        status:'Confirmed'

    });


    showModal.value=false;

}



function goToFacilities(){

    router.push({
        name:'facilities'
    });

}



const iconMap = {

    'Meeting room': DoorOpen,

    'Sports facility': Dumbbell,

    'Creative space': Palette

};


</script>




<template>


<section

class="reservation-page content-wrap"

id="screen-reservations"

data-screen-id="afa48fa655154f92b6194d75a21aa27b"

>



<!-- HEADER -->

<div class="reservation-header">


<div>


<p class="eyebrow">
THURSDAY, 12 JUNE 2025
</p>


<h1>
Reservasi Saya
</h1>


<p class="subheading">
Kelola dan pantau seluruh reservasi fasilitas Anda.
</p>


</div>




<button

class="primary-button"

@click="showModal=true"

>


<Plus size="18"/>


Reservasi Baru


</button>


</div>








<!-- STATISTICS -->


<div class="reservation-stat-grid">



<article class="reservation-stat-card">


<div class="reservation-stat-icon coral-bg">

<Clock3/>

</div>


<div>


<span>
Upcoming reservations
</span>


<strong>
{{ reservations.length.toString().padStart(2,'0') }}
</strong>


<small>
+1 from last week
</small>


</div>


</article>





<article class="reservation-stat-card">


<div class="reservation-stat-icon blue-bg">

<Building2/>

</div>


<div>


<span>
Available facilities
</span>


<strong>
12
</strong>


<small>
Across 4 locations
</small>


</div>


</article>





<article class="reservation-stat-card">


<div class="reservation-stat-icon yellow-bg">

<CheckCircle2/>

</div>


<div>


<span>
Hours reserved
</span>


<strong>
18.5
</strong>


<small>
This month
</small>


</div>


</article>



</div>







<!-- LIST HEADER -->


<div class="reservation-toolbar">


<div>


<h2>
Upcoming reservations
</h2>


<p>
Your confirmed and pending bookings.
</p>


</div>



<label class="reservation-search">


<Search size="18"/>


<input

v-model="search"

placeholder="Search reservations"

/>


</label>



</div>








<!-- RESERVATION LIST -->


<div class="reservation-card-list">



<article

v-for="reservation in filteredReservations"

:key="reservation.name"

class="reservation-item"

>



<div

:class="[
'reservation-item-icon',
reservation.color
]"


>


<component

:is="iconMap[reservation.type]"

size="22"

/>


</div>





<div class="reservation-item-info">


<strong>

{{ reservation.name }}

</strong>


<span>

{{ reservation.type }}

</span>


</div>






<div class="reservation-item-date">


<label>
DATE & TIME
</label>


<strong>

{{ reservation.date }}

</strong>


</div>





<span

:class="[
'reservation-status',
reservation.status.toLowerCase()
]"

>


{{ reservation.status }}


</span>






<button

class="reservation-more"

>


<MoreHorizontal size="20"/>


</button>




</article>




<div

v-if="filteredReservations.length===0"

class="empty-state"

>

No reservations found.

</div>



</div>









<!-- LOWER SECTION -->


<div class="reservation-lower-grid">





<section class="calendar-panel">


<div class="panel-heading">


<div>


<h2>
June 2025
</h2>


<p>
Choose a date to see facility availability.
</p>


</div>




<div>


<button class="calendar-arrow">

<ChevronLeft/>

</button>


<button class="calendar-arrow">

<ChevronRight/>

</button>


</div>



</div>





<div class="weekdays">


<span
v-for="day in [
'MON',
'TUE',
'WED',
'THU',
'FRI',
'SAT',
'SUN'
]"
:key="day"
>

{{day}}

</span>


</div>





<div class="dates">


<button

v-for="day in 30"

:key="day"


:class="{

selected:day===12,

muted:day<5

}"


@click="selectedDate=`${day} Jun`"


>

{{day}}

</button>


</div>



</section>








<section class="tip-panel">


<div class="tip-art">

<Building2/>

</div>



<div>


<p class="eyebrow">
QUICK TIP
</p>


<h2>
Plan ahead, stay productive.
</h2>


<p>
Reserve your favorite space early to make sure it is ready when you need it.
</p>



<button

class="text-button"

@click="goToFacilities"

>


Explore facilities


<ArrowRight size="16"/>


</button>



</div>


</section>





</div>








<!-- MODAL -->


<Teleport to="body">


<div

v-if="showModal"

class="modal-backdrop"


@click.self="showModal=false"

>


<div class="modal">


<button

class="modal-close"

@click="showModal=false"

>

×


</button>



<p class="eyebrow">
NEW BOOKING
</p>



<h2>
Reserve a space
</h2>



<p>

Selecting a space for

<strong>

{{selectedDate}}

</strong>

</p>




<button

class="primary-button full"

@click="addReservation"

>


Confirm reservation


</button>



</div>


</div>


</Teleport>






</section>


</template>