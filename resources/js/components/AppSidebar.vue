<script setup>

import {
    computed,
    ref,
    onUnmounted
} from "vue";

import {
    useRoute,
    useRouter
} from "vue-router";

import axios from "axios";
import { Building2, CalendarDays, ClipboardCheck, Wrench, Settings, ChevronLeft, ChevronRight, LogOut, Menu, X as CloseIcon } from 'lucide-vue-next';

import {
    clearAuthSession,
    authUser,
    getToken
} from "../auth";



const router = useRouter();
const route = useRoute();


const collapsed = ref(false);
const mobileOpen = ref(false);
const showMobileClose = ref(false);
let mobileAnimationTimer;


const user = authUser;
const menuIcons = { facilities: Building2, reservations: CalendarDays, officer: ClipboardCheck, report: Wrench, admin: Settings };



const menus=[

{
    name:"facilities",
    label:"Fasilitas",
    description:"Katalog fasilitas kampus",
    icon:"▦",
    roles:[
        "guest",
        "user",
        "officer",
        "admin"
    ]
},


{
    name:"reservations",
    label:"Reservasi Saya",
    description:"Riwayat peminjaman",
    icon:"◷",
    roles:[
        "user"
    ]
},


{
    name:"officer",
    label:"Petugas",
    description:"Approval reservasi",
    icon:"✓",
    roles:[
        "officer"
    ]
},


{
    name:"report",
    label:"Laporan Kerusakan",
    description:"Kerusakan fasilitas",
    icon:"⚠",
    roles:[
        "user"
    ]
},


{
    name:"admin",
    label:"Admin Panel",
    description:"Manajemen sistem",
    icon:"◫",
    roles:[
        "admin"
    ]
}

];



const visibleMenus = computed(()=>{


const role = user.value?.role || "guest";


return menus.filter(menu =>

    menu.roles.includes(role)

);


});




const roleLabel=computed(()=>{


return user.value?.role
?
user.value.role.toUpperCase()
:
"GUEST";


});





function isActive(name){


return route.name===name;


}

function closeMobile(){
    window.clearTimeout(mobileAnimationTimer);
    showMobileClose.value = false;
    mobileOpen.value = false;
}

function toggleMobile(){
    if (mobileOpen.value) {
        closeMobile();
        return;
    }

    mobileOpen.value = true;
    window.clearTimeout(mobileAnimationTimer);
    mobileAnimationTimer = window.setTimeout(() => {
        showMobileClose.value = true;
    }, 250);
}

onUnmounted(() => window.clearTimeout(mobileAnimationTimer));






async function logout(){
const token = getToken();
clearAuthSession();
await router.replace({
    name:"landing"
});


try{

if(token){
    await axios.post("/api/logout", {}, {
        headers:{
            Authorization:`Bearer ${token}`
        }
    });
}

}

catch(error){

console.warn(
"Logout API failed",
error
);

}


}




</script>





<template>


<button
class="mobile-sidebar-toggle"
:class="{ 'is-open':mobileOpen }"
type="button"
title="Buka menu"
aria-label="Buka menu"
@click="toggleMobile"
>
<Menu v-if="!showMobileClose" :size="21" />
<CloseIcon v-else :size="21" />
</button>


<div
v-if="mobileOpen"
class="mobile-sidebar-backdrop"
aria-hidden="true"
@click="closeMobile"
></div>


<aside

class="sidebar"

:class="{
'is-collapsed':collapsed,
'mobile-open':mobileOpen
}"

>




<!-- BRAND -->

<div class="brand">


<div class="brand-logo">

R

</div>




<div
v-if="!collapsed"
class="brand-info"
>


<strong>

RuangKita

</strong>


<span>

Campus Facility

</span>


</div>


</div>






<div
v-if="!collapsed"
class="section-title"
>

WORKSPACE

</div>








<nav>



<RouterLink


v-for="item in visibleMenus"


:key="item.name"


:to="{
name:item.name
}"


class="nav-link"
:title="item.label"
:aria-label="item.label"


:class="{
active:isActive(item.name)
}"
@click="closeMobile"


>



<div class="nav-icon">

<component :is="menuIcons[item.name]" :size="20" />

</div>





<div
v-if="!collapsed"
class="nav-content"
>


<strong>

{{item.label}}

</strong>


<small>

{{item.description}}

</small>


</div>



</RouterLink>



</nav>









<button

class="collapse"
:title="collapsed ? 'Perluas menu' : 'Ciutkan menu'"
:aria-label="collapsed ? 'Perluas menu' : 'Ciutkan menu'"

@click="collapsed=!collapsed"

>


<ChevronRight v-if="collapsed" :size="18" /><ChevronLeft v-else :size="18" />


</button>










<div class="sidebar-bottom">





<button

class="logout"
v-if="user"
title="Keluar"
aria-label="Keluar"

@click="logout"

>


<span>

<LogOut :size="18" />

</span>


<span v-if="!collapsed">

Logout

</span>


</button>









<div class="user-box">


<div class="avatar">


{{

(user?.name || "U")
.charAt(0)
.toUpperCase()

}}



</div>







<div
v-if="!collapsed"
class="user-detail"
>



<strong>

{{user?.name}}

</strong>



<span>

{{roleLabel}}

</span>



</div>




</div>





</div>







</aside>



</template>
