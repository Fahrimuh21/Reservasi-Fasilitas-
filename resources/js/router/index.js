/**
 * Campus Facility Management System — Client-Side Router
 */

import { 
    createRouter, 
    createWebHistory 
} from 'vue-router';


import {
    isAuthenticated,
    hasRole
} from '../auth';


// Views

const LoginView        = () => import('../views/LoginView.vue');
const RegisterView     = () => import('../views/RegisterView.vue');

const FacilitiesView   = () => import('../views/FacilitiesView.vue');
const ReservationsView = () => import('../views/ReservationsView.vue');
const OfficerView      = () => import('../views/OfficerView.vue');
const ReportView       = () => import('../views/ReportView.vue');
const AdminView        = () => import('../views/AdminView.vue');





const routes = [



{
    path:'/login',

    name:'login',

    component:LoginView,

    meta:{
        title:'Login'
    }

},




{
    path:'/register',

    name:'register',

    component:RegisterView,

    meta:{
        title:'Register'
    }

},





{
    path:'/',

    redirect:()=>{

        return isAuthenticated()
        ?
        '/facilities'
        :
        '/login';

    }

},






// =====================
// USER + ADMIN + OFFICER
// =====================


{
    path:'/facilities',

    name:'facilities',

    component:FacilitiesView,

    meta:{

        title:'Katalog Fasilitas',

        roles:[
            'user',
            'officer',
            'admin'
        ]

    }

},





// =====================
// RESERVATION
// =====================


{
    path:'/reservations',

    name:'reservations',

    component:ReservationsView,

    meta:{

        title:'Reservasi Saya',

        roles:[

            'user',
            'admin'

        ]

    }

},






// =====================
// OFFICER
// =====================


{
    path:'/officer',

    name:'officer',

    component:OfficerView,

    meta:{

        title:'Dashboard Petugas',

        roles:[

            'officer',
            'admin'

        ]

    }

},






// =====================
// DAMAGE REPORT
// =====================


{
    path:'/report',

    name:'report',

    component:ReportView,

    meta:{

        title:'Laporan Kerusakan',

        roles:[

            'user',
            'admin'

        ]

    }

},






// =====================
// ADMIN
// =====================


{
    path:'/admin',

    name:'admin',

    component:AdminView,

    meta:{

        title:'Dashboard Admin',

        roles:[

            'admin'

        ]

    }

},






// 404

{
    path:'/:pathMatch(.*)*',

    redirect:()=>{

        return isAuthenticated()
        ?
        '/facilities'
        :
        '/login';

    }

}



];








const router = createRouter({


    history:createWebHistory(),


    routes,



    scrollBehavior(){

        return {
            top:0
        };

    }


});









router.beforeEach((to,from,next)=>{



    const publicPages=[

        'login',

        'register'

    ];





    // sudah login jangan balik login

    if(

        publicPages.includes(to.name)

        &&

        isAuthenticated()

    ){

        next({

            name:'facilities'

        });

        return;

    }








    // halaman butuh login


    if(

        !publicPages.includes(to.name)

        &&

        !isAuthenticated()

    ){

        next({

            name:'login'

        });

        return;

    }








    // cek role


    if(isAuthenticated()){



        const allowedRoles =
        to.meta?.roles;



        if(

            allowedRoles

            &&

            !hasRole(...allowedRoles)

        ){


            next({

                name:'facilities'

            });


            return;


        }


    }







    next();



});









router.afterEach((to)=>{


    const baseTitle='RuangKita';


    document.title =

    to.meta?.title

    ?

    `${to.meta.title} — ${baseTitle}`

    :

    baseTitle;



});








export default router;