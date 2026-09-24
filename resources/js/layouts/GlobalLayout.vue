<script setup>

import {
    computed
} from "vue";

import {
    useRoute
} from "vue-router";


import AppSidebar from "../components/AppSidebar.vue";
import Topbar from "../components/Topbar.vue";


const route = useRoute();



const authPages = [
    "landing",
    "login",
    "register"
];



const isAuthPage = computed(()=>{

return authPages.includes(
    route.name
);

});


</script>





<template>



<!-- =========================
AUTH SCREEN
========================= -->


<div

v-if="isAuthPage"

:key="`auth-${route.fullPath}`"

class="auth-layout"

>


<RouterView />


</div>








<!-- =========================
APPLICATION SHELL
========================= -->


<div

v-else

:key="`app-${route.fullPath}`"

class="app-shell"

>



<!-- SIDEBAR -->


<AppSidebar />





<!-- RIGHT AREA -->


<section class="workspace">





<!-- TOP HEADER -->


<Topbar />







<!-- PAGE CONTENT -->


<main

class="main-content"

>


<RouterView

v-slot="{
Component,
route
}"

>


<Transition

name="page"

mode="out-in"

>


<component

:is="Component"

:key="route.fullPath"

/>


</Transition>


</RouterView>



</main>



</section>





</div>



</template>







<style scoped>


/*
 Page Transition
*/

.page-enter-active,
.page-leave-active{

transition:
opacity .25s ease,
transform .25s ease;

}



.page-enter-from{

opacity:0;

transform:
translateY(18px);

}



.page-leave-to{

opacity:0;

transform:
translateY(-18px);

}





</style>