<script setup>

import {ref} from "vue";
import {useRouter} from "vue-router";
import axios from "axios";

import {
    setAuthSession
} from "../auth";


const router = useRouter();


const form = ref({

    email:"",
    password:""

});


const isSubmitting = ref(false);

const errorMessage = ref("");




async function submitLogin(){


isSubmitting.value=true;

errorMessage.value="";



try{


const response =
await axios.post(
"/api/login",
form.value
);



const {
token,
user
}
=
response.data;



setAuthSession(
token,
user
);



const redirect={

admin:"admin",

officer:"officer",

user:"facilities"

};



router.push({

name:
redirect[user.role]
||
"facilities"

});



}

catch(error){


errorMessage.value =

error?.response?.data?.message

||

"Login gagal";



}

finally{


isSubmitting.value=false;


}



}


</script>





<template>


<main class="login-wrapper">


<div class="auth-panel">



<div class="auth-illustration">


<div class="illustration-badge">

RUANGKITA

</div>


<div class="illustration-circle">


<div class="mini-card top">

📅 Reservasi

</div>



<div class="mini-card mid">

✅ Cek Status

</div>



<div class="mini-card bottom">

📍 Fasilitas

</div>


</div>




<h2>

Kelola fasilitas kampus lebih mudah.

</h2>



<p>

Reservasi ruang, pantau status fasilitas,
dan laporkan kerusakan dengan satu platform.

</p>



</div>







<div class="auth-card">



<div class="brand-badge">

R

</div>



<h1>

Masuk ke RuangKita

</h1>



<p class="subtitle">

Kelola reservasi dan laporan fasilitas kampus.

</p>




<form
@submit.prevent="submitLogin"
>



<label>

Email

<input

v-model="form.email"

type="email"

placeholder="email@gmail.com"

/>

</label>



<label>

Password

<input

v-model="form.password"

type="password"

placeholder="Password"

/>

</label>



<div
v-if="errorMessage"
class="error-box"
>

{{errorMessage}}

</div>




<button
class="primary-button"
:disabled="isSubmitting"
>


{{

isSubmitting
?
"Memproses..."
:
"Masuk"

}}



</button>




</form>




<div class="auth-footer">

Belum punya akun?

<RouterLink
:to="{name:'register'}"
>

Daftar sekarang

</RouterLink>


</div>


</div>



</div>


</main>


</template>





<style scoped>

.login-wrapper{


position:fixed;

inset:0;


display:flex;

align-items:center;

justify-content:center;


background:

linear-gradient(
135deg,
#eff6ff,
#f8fafc
);


padding:30px;


}



.auth-panel{


width:1100px;

max-width:95%;


display:grid;

grid-template-columns:1fr .9fr;


background:white;


border-radius:30px;


overflow:hidden;


box-shadow:

0 30px 80px
rgba(0,0,0,.12);


}



.auth-illustration{


padding:60px;


background:

linear-gradient(
135deg,
#dbeafe,
#eff6ff
);


}



.illustration-badge{


display:inline-block;

padding:8px 15px;

border-radius:30px;


background:white;

color:#2563eb;


font-weight:700;


}


.illustration-circle{


height:250px;

margin:40px 0;


border-radius:30px;


background:
rgba(255,255,255,.4);


position:relative;


}



.mini-card{


position:absolute;

background:white;


padding:12px 18px;


border-radius:14px;


box-shadow:
0 10px 30px rgba(0,0,0,.08);


font-weight:700;


}



.top{

top:30px;

left:30px;

}



.mid{

top:110px;

right:30px;

}



.bottom{

bottom:30px;

left:80px;

}





.auth-illustration h2{


font-size:42px;

line-height:1.1;


color:#0f172a;


}



.auth-illustration p{


color:#64748b;

line-height:1.7;


}




.auth-card{


padding:60px;


display:flex;

flex-direction:column;

justify-content:center;


}



.brand-badge{


width:60px;

height:60px;


display:grid;

place-items:center;


background:#2563eb;


color:white;


font-size:26px;

font-weight:900;


border-radius:18px;


margin-bottom:25px;


}



.auth-card h1{


font-size:40px;


margin:0;


}




.subtitle{


color:#64748b;

margin-bottom:30px;


}




form{


display:flex;

flex-direction:column;

gap:18px;


}



label{


display:flex;

flex-direction:column;

gap:8px;

font-weight:700;


}



input{


height:50px;


border-radius:12px;


border:1px solid #dbe3ee;


padding:0 15px;


background:#f8fafc;


}



input:focus{


outline:none;


border-color:#2563eb;


}




.primary-button{


height:52px;


border:none;


border-radius:14px;


background:#2563eb;


color:white;


font-weight:800;


cursor:pointer;


}



.error-box{


padding:12px;


border-radius:12px;


background:#fee2e2;


color:#991b1b;


}




.auth-footer{


margin-top:25px;


text-align:center;


}



.auth-footer a{
color:#2563eb;
font-weight:700;
}




@media (max-width:700px){
    .login-wrapper{
        position:relative;
        min-height:100dvh;
        padding:24px 14px;
        overflow:auto;
    }

    .auth-panel{
        width:min(100%, 460px);
        max-width:100%;
        grid-template-columns:1fr;
        border-radius:24px;
    }

    .auth-illustration{
        display:none;
    }

    .auth-card{
        padding:36px 24px 30px;
    }

    .auth-card h1{
        font-size:34px;
        line-height:1.08;
    }

    .brand-badge{
        margin-bottom:20px;
    }
}

@media (max-width:380px){
    .login-wrapper{
        padding:16px 10px;
    }

    .auth-card{
        padding:28px 18px 24px;
    }

    .auth-card h1{
        font-size:30px;
    }
}


</style>
