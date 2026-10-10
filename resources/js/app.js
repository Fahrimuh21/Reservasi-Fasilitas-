import './bootstrap';
import '../css/ui-refresh.css';

import { createApp } from 'vue';

import App from './App.vue';

import router from './router';

import {
    installAuthInterceptor,
    restoreAuthSession
}
from './auth';

async function bootstrapApplication() {
    installAuthInterceptor();
    await restoreAuthSession();

    createApp(App)
        .use(router)
        .mount('#app');
}

bootstrapApplication();
