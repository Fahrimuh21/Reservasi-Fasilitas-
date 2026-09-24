import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

export function apiError(error) {
    if (error.response?.status === 401) return 'Sesi berakhir. Silakan login kembali.';
    if (error.response?.status >= 500) return 'Server belum dapat memproses permintaan. Coba lagi.';
    const errors = Object.values(error.response?.data?.errors || {}).flat();
    return errors.join(' ') || error.response?.data?.message || 'Koneksi terputus. Periksa jaringan dan coba lagi.';
}

export function useLiveCollection(url, interval = 3000) {
    const items = ref([]);
    const loading = ref(true);
    const refreshing = ref(false);
    const error = ref('');
    const updatedAt = ref(null);
    let controller;
    let timer;
    let disposed = false;

    async function refresh() {
        if (disposed) return;
        controller?.abort();
        const request = new AbortController();
        controller = request;
        refreshing.value = true;
        try {
            const response = await axios.get(url, { signal: request.signal, timeout: 15000 });
            if (request.signal.aborted) return;
            items.value = response.data.data || [];
            error.value = '';
            updatedAt.value = new Date();
        } catch (failure) {
            if (!request.signal.aborted) error.value = apiError(failure);
        } finally {
            if (controller === request) {
                loading.value = false;
                refreshing.value = false;
            }
        }
    }

    function resume() {
        if (!document.hidden && !refreshing.value) refresh();
    }

    onMounted(() => {
        refresh();
        timer = window.setInterval(resume, interval);
        document.addEventListener('visibilitychange', resume);
        window.addEventListener('online', resume);
    });
    onUnmounted(() => {
        disposed = true;
        window.clearInterval(timer);
        controller?.abort();
        document.removeEventListener('visibilitychange', resume);
        window.removeEventListener('online', resume);
    });

    return { items, loading, refreshing, error, updatedAt, refresh };
}
