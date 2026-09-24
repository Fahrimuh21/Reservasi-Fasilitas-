<script setup>

import {
    ref,
    reactive
} from "vue";



const submitted = ref(false);

const ticketNumber = ref("");

const preview = ref(null);

const drag = ref(false);





const form = reactive({

facility:"",
category:"",
severity:"medium",
description:"",
photo:null

});





const facilities=[

"Ruang Rapat Merapi",

"Lab Komputer Rinjani",

"Studio Kreatif",

"Lapangan Futsal A",

"Ruang Seminar Bromo"

];



const categories=[

"Elektronik",

"AC / HVAC",

"Listrik",

"Kebersihan",

"Infrastruktur"

];



const severities=[

{
value:"low",
label:"Rendah",
icon:"🟢",
desc:"Tidak mengganggu aktivitas"
},


{
value:"medium",
label:"Sedang",
icon:"🟡",
desc:"Mengganggu sebagian fungsi"
},


{
value:"high",
label:"Kritis",
icon:"🔴",
desc:"Fasilitas tidak dapat digunakan"
}


];







const tickets = ref([


{
id:"TKT-2026-091",
title:"AC Lab Komputer mati",
facility:"Lab Komputer Rinjani",
category:"AC / HVAC",
status:"Diproses",
severity:"high",
date:"Hari ini"
},


{
id:"TKT-2026-090",
title:"Lampu Ruang Rapat redup",
facility:"Ruang Rapat Merapi",
category:"Listrik",
status:"Selesai",
severity:"low",
date:"Kemarin"
}


]);








function uploadFile(e){


const file =
e.target.files[0];


setPhoto(file);


}



function dropFile(e){


drag.value=false;


const file =
e.dataTransfer.files[0];


setPhoto(file);


}





function setPhoto(file){


if(!file)
return;


if(
file.type.startsWith("image/")
){


form.photo=file;


preview.value=
URL.createObjectURL(file);


}


}







function submitReport(){


ticketNumber.value =
"TKT-2026-"
+
(
tickets.value.length+92
)
.toString()
.padStart(3,"0");




tickets.value.unshift({

id:ticketNumber.value,

title:
form.description.substring(0,35)
+
"...",

facility:form.facility,

category:form.category,

severity:form.severity,

status:"Terbuka",

date:"Baru saja"


});



submitted.value=true;


}







function reset(){

submitted.value=false;

preview.value=null;


Object.assign(
form,
{
facility:"",
category:"",
severity:"medium",
description:"",
photo:null
}
);


}



const severityMap={

low:"Rendah",

medium:"Sedang",

high:"Kritis"

};



</script>









<template>


<section

class="content-wrap"

id="screen-report"

>



<div class="intro-row">


<div>

<p class="eyebrow">
DAMAGE REPORT
</p>


<h1>

Create Ticket

<span class="sun">
✦
</span>

</h1>


<p class="subheading">

Laporkan kerusakan fasilitas dengan bukti foto agar cepat ditangani.

</p>


</div>


</div>







<div class="report-grid">





<!-- FORM -->

<div class="report-card">



<div

v-if="submitted"

class="success"

>


<div class="success-icon">

✓

</div>


<h2>
Laporan berhasil dibuat
</h2>


<p>

Nomor tiket:

<strong>
{{ticketNumber}}
</strong>

</p>



<button

@click="reset"

>

Buat laporan baru

</button>



</div>








<form

v-else

@submit.prevent="submitReport"

>




<div class="field">


<label>
Fasilitas
</label>


<select v-model="form.facility" required>


<option value="">
Pilih fasilitas
</option>


<option

v-for="f in facilities"

:key="f"

>

{{f}}

</option>


</select>


</div>








<div class="field">


<label>
Kategori
</label>


<select v-model="form.category" required>


<option value="">
Pilih kategori
</option>


<option

v-for="c in categories"

:key="c"

>

{{c}}

</option>


</select>


</div>








<div class="field">


<label>
Level Kerusakan
</label>



<div class="severity">


<label

v-for="s in severities"

:key="s.value"

:class="[

'severity-item',

{
active:
form.severity===s.value
}

]"


>


<input

type="radio"

v-model="form.severity"

:value="s.value"

/>



<span>

{{s.icon}}

{{s.label}}

</span>


<small>

{{s.desc}}

</small>



</label>


</div>



</div>







<div class="field">


<label>
Deskripsi
</label>


<textarea

v-model="form.description"

rows="5"

placeholder="
Jelaskan kondisi kerusakan...
"

required

></textarea>


</div>







<div class="field">


<label>
Foto Bukti
</label>



<label

class="upload"

:class="{drag}"

@dragover.prevent="drag=true"

@dragleave.prevent="drag=false"

@drop.prevent="dropFile"

>


<img

v-if="preview"

:src="preview"

/>


<template v-else>


<strong>
📷 Upload Foto
</strong>


<span>
Tarik file atau klik untuk memilih
</span>


</template>



<input

type="file"

accept="image/*"

@change="uploadFile"

/>



</label>



</div>








<button

class="submit"

>

Kirim Laporan →

</button>



</form>


</div>








<!-- HISTORY -->

<div class="ticket-panel">



<div class="panel-title">


<h2>
Recent Tickets
</h2>


<p>
Status laporan terbaru
</p>


</div>





<div

v-for="ticket in tickets"

:key="ticket.id"

class="ticket"


>



<div class="ticket-top">


<strong>
{{ticket.id}}
</strong>


<span

:class="
'priority '+ticket.severity
"

>

{{severityMap[ticket.severity]}}

</span>


</div>




<h3>

{{ticket.title}}

</h3>



<p>

{{ticket.facility}}

</p>



<div class="ticket-footer">


<span>

{{ticket.category}}

</span>


<b>

{{ticket.status}}

</b>


</div>



</div>





</div>





</div>






</section>


</template>










<style scoped>


.report-grid{


display:grid;


grid-template-columns:
1.2fr
0.8fr;


gap:25px;


margin-top:35px;


}






.report-card,
.ticket-panel{


background:white;


border:1px solid var(--line);


border-radius:25px;


padding:28px;


}







.field{


margin-bottom:20px;


}



.field label{


display:block;


font-size:12px;


font-weight:800;


color:#64748b;


margin-bottom:8px;


text-transform:uppercase;


}





.field input,
.field select,
.field textarea{


width:100%;


border:1px solid #e2e8f0;


background:#f8fafc;


padding:13px;


border-radius:14px;


outline:none;


}





.field textarea:focus,
.field select:focus{


border-color:#2563eb;


background:white;


}








.severity{


display:grid;


grid-template-columns:
repeat(3,1fr);


gap:10px;


}




.severity-item{


padding:15px;


border:1px solid var(--line);


border-radius:16px;


cursor:pointer;


}



.severity-item input{


display:none;


}



.severity-item span{


display:block;


font-weight:800;


font-size:13px;


}



.severity-item small{


font-size:10px;


color:#64748b;


}



.severity-item.active{


border-color:#2563eb;


background:#eff6ff;


}








.upload{


height:170px;


border:2px dashed #cbd5e1;


border-radius:20px;


display:flex;


flex-direction:column;


align-items:center;


justify-content:center;


cursor:pointer;


gap:8px;


color:#64748b;


overflow:hidden;


}



.upload input{


display:none;


}



.upload img{


width:100%;


height:100%;


object-fit:cover;


}



.upload.drag{


border-color:#2563eb;


background:#eff6ff;


}






.submit{


width:100%;


padding:15px;


border-radius:15px;


background:#2563eb;


color:white;


font-weight:800;


}



.submit:hover{


background:#1d4ed8;


}







.ticket-panel{


background:#0f172a;


color:white;


}



.panel-title h2{


margin:0;


}



.panel-title p{


color:#94a3b8;


font-size:13px;


}





.ticket{


margin-top:16px;


padding:18px;


background:white;


color:#0f172a;


border-radius:18px;


}



.ticket-top{


display:flex;


justify-content:space-between;


}



.ticket-top strong{


font-size:12px;


color:#2563eb;


}



.priority{


font-size:10px;


padding:5px 10px;


border-radius:999px;


font-weight:800;


}



.priority.high{


background:#fee2e2;


color:#dc2626;


}


.priority.medium{


background:#fef3c7;


color:#b45309;


}



.priority.low{


background:#dcfce7;


color:#15803d;


}





.ticket h3{


font-size:14px;


margin:12px 0 5px;


}



.ticket p{


font-size:12px;


color:#64748b;


}



.ticket-footer{


display:flex;


justify-content:space-between;


font-size:11px;


}



.ticket-footer b{


color:#2563eb;


}





.success{


text-align:center;


padding:60px 20px;


}



.success-icon{


width:70px;


height:70px;


margin:auto;


border-radius:50%;


display:grid;


place-items:center;


background:#dcfce7;


color:#16a34a;


font-size:35px;


}



.success button{


margin-top:20px;


padding:12px 20px;


border-radius:12px;


background:#2563eb;


color:white;


}





@media(max-width:900px){


.report-grid{


grid-template-columns:1fr;


}


.severity{


grid-template-columns:1fr;


}


}



</style>