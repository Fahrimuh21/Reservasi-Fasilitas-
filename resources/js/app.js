import './bootstrap';
import '../css/ui-refresh.css';

import { createApp } from 'vue';

import App from './App.vue';

import router from './router';

import {
    restoreAuthSession
}
from './auth';



restoreAuthSession();



createApp(App)

.use(router)

.mount('#app');
