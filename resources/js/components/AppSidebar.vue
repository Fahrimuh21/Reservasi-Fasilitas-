<script setup>

import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

import {
    Building2,
    CalendarDays,
    UserCog,
    AlertTriangle,
    LayoutDashboard,
    LogOut,
    Menu,
    CircleHelp
} from 'lucide-vue-next';


import {
    clearAuthSession,
    authUser,
    getToken
} from '../auth';



const router = useRouter();


const mobileOpen = ref(false);


const user = authUser;



/*
|--------------------------------------------------------------------------
| Navigation Items
|--------------------------------------------------------------------------
*/


const navItems = [

    {
        name:'facilities',

        label:'Katalog Fasilitas',

        icon:Building2,

        roles:[
            'user',
            'officer',
            'admin'
        ],

        screenId:
        '9b4eaf60a859485ea4a2057a9eba4306',

    },


    {
        name:'reservations',

        label:'Reservasi Saya',

        icon:CalendarDays,

        roles:[
            'user'
        ],

        screenId:
        'afa48fa655154f92b6194d75a21aa27b',

    },


    {
        name:'officer',

        label:'Dashboard Petugas',

        icon:UserCog,

        roles:[
            'officer'
        ],

        screenId:
        '27f9c22580c44370a6c9f89785168a63',

    },


    {
        name:'report',

        label:'Laporan Kerusakan',

        icon:AlertTriangle,

        roles:[
            'user'
        ],

        screenId:
        '55597836a1f445e4ab3cd358e0c6155b',

    },


    {
        name:'admin',

        label:'Dashboard Admin',

        icon:LayoutDashboard,

        roles:[
            'admin'
        ],

        screenId:
        '9400ab25f7044638a44066516e2c8bfd',

    },

];



const visibleNavItems = computed(()=>{

    const role = user.value?.role;

    return navItems.filter(
        item =>
        item.roles.includes(role) ||
        (!role && item.name === 'facilities')
    );

});



/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/


async function handleLogout(){
    const token = getToken();

    // Clear the client session immediately so logout never depends on API availability.
    clearAuthSession();
    router.push({ name:'login' });

    try{

        if (token) {
            await axios.post('/api/logout', {}, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
        }


    }catch(error){

        console.warn(
            'Logout API failed:',
            error
        );


    }finally{


    }

}



</script>



<template>


<!-- SIDEBAR -->

<aside

    class="sidebar"

    :class="{
        'sidebar--mobile-open':mobileOpen
    }"

>


    <!-- BRAND -->

    <div class="brand">


        <span class="brand-mark">

            R

        </span>


        <span class="brand-name">

            RuangKita

        </span>


    </div>





    <!-- LABEL -->


    <div class="workspace-label">

        WORKSPACE

    </div>





    <!-- MENU -->


    <nav

        class="side-nav"

        aria-label="Primary navigation"

    >


        <RouterLink


            v-for="item in visibleNavItems"


            :key="item.name"


            :to="{
                name:item.name
            }"


            class="nav-item"


            active-class="nav-item--active"


            :data-screen-id="item.screenId"


            :title="item.label"


            @click="
                mobileOpen=false
            "

        >



            <component

                :is="item.icon"

                class="nav-icon"

            />



            <span class="nav-label">

                {{ item.label }}

            </span>



        </RouterLink>



    </nav>







    <!-- BOTTOM -->


    <div class="sidebar-bottom">



        <!-- LOGOUT -->


        <button

            class="logout-button"

            @click="handleLogout"

        >


            <LogOut

                class="nav-icon"

            />


            <span class="nav-label">

                Logout

            </span>


        </button>





        <!-- USER PROFILE -->


        <div class="user-card">


            <div class="avatar">


                {{
                    (
                        user?.name ||
                        'U'
                    )
                    .charAt(0)
                    .toUpperCase()
                }}


            </div>




            <div class="nav-label">


                <strong>

                    {{
                        user?.name ||
                        'Tamu'
                    }}

                </strong>



                <small>

                    {{
                        user?.role ||
                        'guest'
                    }}

                </small>


            </div>




            <span class="dots">

                •••

            </span>



        </div>



    </div>




</aside>






<!-- TOPBAR -->


<div class="topbar-wrapper">


<header class="topbar">


    <button

        class="mobile-menu-button"

        @click="
            mobileOpen=!mobileOpen
        "

        aria-label="Menu"

    >


        <Menu

            size="20"

        />


    </button>




    <div

        class="breadcrumb-spacer"

    ></div>




    <button

        class="help-button"

        id="btn-help"

        aria-label="Help"

    >


        <CircleHelp

            size="18"

        />


    </button>



</header>



<slot />


</div>



</template>