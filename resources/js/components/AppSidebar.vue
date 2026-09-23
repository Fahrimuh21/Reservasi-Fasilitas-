<script setup>

import {
    computed,
    ref
} from "vue";

import {
    useRoute,
    useRouter
} from "vue-router";

import axios from "axios";

import {
    clearAuthSession,
    getAuthUser
} from "../auth";



const router = useRouter();
const route = useRoute();


const collapsed = ref(false);


const user = computed(()=>getAuthUser());



const menus=[

{
    name:"facilities",
    label:"Fasilitas",
    description:"Katalog fasilitas kampus",
    icon:"▦",
    roles:[
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
        "user",
        "admin"
    ]
},


{
    name:"officer",
    label:"Petugas",
    description:"Approval reservasi",
    icon:"✓",
    roles:[
        "officer",
        "admin"
    ]
},


{
    name:"report",
    label:"Laporan Kerusakan",
    description:"Kerusakan fasilitas",
    icon:"⚠",
    roles:[
        "user",
        "admin"
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


const role = user.value?.role;


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






async function logout(){


try{

await axios.post("/api/logout");

}

catch(error){

console.warn(
"Logout API failed",
error
);

}

finally{


clearAuthSession();


router.push({
    name:"login"
});


}


}




</script>





<template>


<aside

class="sidebar"

:class="{
'is-collapsed':collapsed
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


:class="{
active:isActive(item.name)
}"


>



<div class="nav-icon">

{{item.icon}}

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

@click="collapsed=!collapsed"

>


{{collapsed?'›':'‹'}}


</button>










<div class="sidebar-bottom">





<button

class="logout"

@click="logout"

>


<span>

↪

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