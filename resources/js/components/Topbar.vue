<script setup>

import {
    computed,
    ref,
    onMounted,
    onBeforeUnmount
} from "vue";

import {
    useRoute,
    useRouter
} from "vue-router";


import {
    getAuthUser
} from "../auth";


const route = useRoute();
const router = useRouter();



const search = ref("");

const showNotification = ref(false);
const showProfile = ref(false);



const user = computed(()=>getAuthUser());



/*
|--------------------------------------------------------------------------
| Dynamic Breadcrumb
|--------------------------------------------------------------------------
*/

const breadcrumb = computed(()=>{


    return route.meta?.breadcrumb
        ||
        route.name
            ?.toString()
            .replaceAll("-"," ")
            .replace(/\b\w/g,
                c=>c.toUpperCase()
            )
        ||
        "Dashboard";


});



/*
|--------------------------------------------------------------------------
| Notifications Mock
| nanti tinggal connect API
|--------------------------------------------------------------------------
*/


const notifications = ref([

{
    id:1,
    title:"Reservasi disetujui",
    desc:"Ruang Rapat Merapi",
    time:"5 menit lalu",
    type:"success"
},


{
    id:2,
    title:"Laporan baru masuk",
    desc:"AC Lab Komputer rusak",
    time:"20 menit lalu",
    type:"warning"
},


{
    id:3,
    title:"Maintenance selesai",
    desc:"Studio Kreatif aktif kembali",
    time:"1 jam lalu",
    type:"info"
}

]);




function toggleNotification(){


showNotification.value =
!showNotification.value;


showProfile.value=false;


}



function toggleProfile(){


showProfile.value =
!showProfile.value;


showNotification.value=false;


}




function goProfile(){

router.push({
    name:"profile"
}).catch(()=>{});

}




function logout(){

router.push({
    name:"login"
});


}




/*
|--------------------------------------------------------------------------
| Close popup outside
|--------------------------------------------------------------------------
*/


function closeDropdown(e){


if(
!e.target.closest(".notification-area")
&&
!e.target.closest(".profile-area")

){

showNotification.value=false;
showProfile.value=false;

}


}



onMounted(()=>{

document.addEventListener(
"click",
closeDropdown
);


});



onBeforeUnmount(()=>{


document.removeEventListener(
"click",
closeDropdown
);


});




</script>



<template>


<header class="topbar">


<!-- LEFT -->

<div class="topbar-left">


<div class="breadcrumb">


<span>
Workspace
</span>


<i>
/
</i>


<strong>
{{breadcrumb}}
</strong>


</div>



</div>





<!-- CENTER SEARCH -->


<div class="global-search">


<span>
⌕
</span>


<input

v-model="search"

placeholder="Cari fasilitas, reservasi, pengguna..."

>


</div>






<!-- RIGHT -->


<div class="topbar-actions">





<!-- Notification -->

<div
class="notification-area"
>


<button

class="icon-button"

@click.stop="toggleNotification"

>


<span>
🔔
</span>


<span
v-if="notifications.length"
class="badge"
>
{{notifications.length}}
</span>


</button>





<Transition name="dropdown">


<div

v-if="showNotification"

class="dropdown notification-dropdown"

>



<h3>
Notifikasi
</h3>



<div

v-for="item in notifications"

:key="item.id"

class="notification-item"

>


<div

:class="[
'notification-dot',
item.type
]"

>


</div>



<div>


<strong>
{{item.title}}
</strong>


<p>
{{item.desc}}
</p>


<small>
{{item.time}}
</small>


</div>


</div>



<button class="see-all">

Lihat semua

</button>



</div>


</Transition>



</div>







<!-- Profile -->

<div
class="profile-area"
>



<button

class="profile-button"

@click.stop="toggleProfile"

>


<div class="mini-avatar">

{{

(user?.name || "U")
.charAt(0)
.toUpperCase()

}}

</div>


<div class="mini-user">


<strong>

{{user?.name || "User"}}

</strong>


<small>

{{user?.role || "Member"}}

</small>


</div>



<span>
⌄
</span>


</button>







<Transition name="dropdown">


<div

v-if="showProfile"

class="dropdown profile-dropdown"

>


<button
@click="goProfile"
>

👤 Profile

</button>


<button
>

⚙ Settings

</button>


<button
class="danger"
@click="logout"
>

↪ Logout

</button>


</div>


</Transition>




</div>





</div>



</header>



</template>





<style scoped>


/* ===========================
 TOPBAR LAYOUT
=========================== */


.topbar{


position:relative;


}





.topbar-left{


display:flex;


align-items:center;


}





.breadcrumb{


display:flex;


align-items:center;


gap:10px;


font-size:14px;


}



.breadcrumb span{


color:#94a3b8;


}



.breadcrumb i{


font-style:normal;


color:#cbd5e1;


}



.breadcrumb strong{


color:#0f172a;


font-weight:700;


}






/* ===========================
 SEARCH
=========================== */


.global-search{


position:absolute;


left:50%;


transform:translateX(-50%);


width:380px;


height:42px;


display:flex;


align-items:center;


gap:10px;


background:#f8fafc;


border:1px solid #e2e8f0;


border-radius:14px;


padding:0 15px;


}



.global-search span{


font-size:20px;


color:#64748b;


}



.global-search input{


border:none;


outline:none;


background:transparent;


width:100%;


font-size:13px;


}






/* ===========================
 ACTION
=========================== */


.topbar-actions{


display:flex;


align-items:center;


gap:14px;


}



.icon-button{


width:42px;


height:42px;


border-radius:14px;


background:white;


border:1px solid #e2e8f0;


position:relative;


font-size:18px;


transition:.2s;


}



.icon-button:hover{


background:#eff6ff;


}



.badge{


position:absolute;


top:-5px;


right:-5px;


width:18px;


height:18px;


display:grid;


place-items:center;


border-radius:50%;


background:#ef4444;


color:white;


font-size:10px;


font-weight:800;


}






/* ===========================
 PROFILE
=========================== */


.profile-button{


display:flex;


align-items:center;


gap:10px;


background:white;


border:1px solid #e2e8f0;


padding:6px 12px 6px 6px;


border-radius:16px;


}



.mini-avatar{


width:34px;


height:34px;


border-radius:12px;


background:#2563eb;


color:white;


display:grid;


place-items:center;


font-weight:800;


}



.mini-user{


text-align:left;


}



.mini-user strong{


display:block;


font-size:12px;


}



.mini-user small{


display:block;


font-size:10px;


color:#64748b;


}






/* ===========================
 DROPDOWN
=========================== */


.dropdown{


position:absolute;


right:0;


top:55px;


background:white;


border:1px solid #e2e8f0;


border-radius:20px;


box-shadow:


0 25px 60px
rgba(15,23,42,.15);


z-index:100;


overflow:hidden;


}




.notification-dropdown{


width:330px;


padding:18px;


}




.notification-dropdown h3{


margin:0 0 14px;


font-size:16px;


}




.notification-item{


display:flex;


gap:12px;


padding:12px;


border-radius:14px;


transition:.2s;


}



.notification-item:hover{


background:#f8fafc;


}




.notification-item strong{


font-size:13px;


display:block;


}



.notification-item p{


margin:3px 0;


font-size:12px;


color:#64748b;


}



.notification-item small{


font-size:11px;


color:#94a3b8;


}



.notification-dot{


width:10px;


height:10px;


border-radius:50%;


margin-top:5px;


}


.notification-dot.success{


background:#22c55e;


}



.notification-dot.warning{


background:#f59e0b;


}



.notification-dot.info{


background:#3b82f6;


}





.see-all{


width:100%;


margin-top:10px;


padding:10px;


border-radius:12px;


background:#eff6ff;


color:#2563eb;


font-weight:700;


}







.profile-dropdown{


width:180px;


padding:10px;


}



.profile-dropdown button{


width:100%;


padding:11px;


border-radius:12px;


background:white;


text-align:left;


font-size:13px;


}



.profile-dropdown button:hover{


background:#f8fafc;


}



.profile-dropdown .danger{


color:#ef4444;


}






/* ===========================
 ANIMATION
=========================== */


.dropdown-enter-active,
.dropdown-leave-active{


transition:.2s;


}



.dropdown-enter-from,
.dropdown-leave-to{


opacity:0;


transform:
translateY(-10px);


}





@media(max-width:900px){


.global-search{


display:none;


}



.mini-user{


display:none;


}


}


</style>