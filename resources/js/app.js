import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index.js';

// Mount the Vue app with vue-router registered as a plugin.
// The router plugin injects $router, $route, <RouterView>, and <RouterLink>
// globally — no per-component import needed for navigation primitives.
createApp(App)
    .use(router)
    .mount('#app');
