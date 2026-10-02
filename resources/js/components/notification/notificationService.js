import { reactive } from 'vue';

const DEFAULT_DURATION = 4200;
let nextToastId = 1;
let nextConfirmId = 1;
const timers = new Map();

export const notificationState = reactive({
    toasts: [],
    confirmation: null,
});

function dismiss(id) {
    const timer = timers.get(id);
    if (timer) window.clearTimeout(timer);
    timers.delete(id);
    const index = notificationState.toasts.findIndex(toast => toast.id === id);
    if (index !== -1) notificationState.toasts.splice(index, 1);
}

function show(type, message, options = {}) {
    // Alert umum ditampilkan satu per satu agar tidak menumpuk di tengah viewport.
    for (const toast of [...notificationState.toasts]) dismiss(toast.id);
    const id = nextToastId++;
    const toast = {
        id,
        type,
        message,
        title: options.title || '',
        duration: options.duration ?? DEFAULT_DURATION,
    };
    notificationState.toasts.push(toast);
    if (toast.duration > 0) timers.set(id, window.setTimeout(() => dismiss(id), toast.duration));
    return id;
}

function confirm(options) {
    if (notificationState.confirmation?.resolve) notificationState.confirmation.resolve(false);
    return new Promise(resolve => {
        notificationState.confirmation = {
            id: nextConfirmId++,
            title: options.title || 'Konfirmasi tindakan',
            message: options.message || 'Apakah Anda yakin ingin melanjutkan?',
            confirmLabel: options.confirmLabel || 'Konfirmasi',
            cancelLabel: options.cancelLabel || 'Batal',
            tone: options.tone || 'primary',
            action: options.onConfirm,
            busy: false,
            error: '',
            resolve,
        };
    });
}

function cancelConfirmation() {
    const current = notificationState.confirmation;
    if (!current || current.busy) return;
    current.resolve(false);
    notificationState.confirmation = null;
}

async function acceptConfirmation() {
    const current = notificationState.confirmation;
    if (!current || current.busy) return;
    current.busy = true;
    current.error = '';
    try {
        await current.action?.();
        current.resolve(true);
        notificationState.confirmation = null;
    } catch (error) {
        current.error = error?.response?.data?.message || error?.message || 'Tindakan gagal. Silakan coba lagi.';
        current.busy = false;
    }
}

export const notification = {
    success: (message, options) => show('success', message, options),
    error: (message, options) => show('error', message, options),
    warning: (message, options) => show('warning', message, options),
    info: (message, options) => show('info', message, options),
    dismiss,
    confirm,
    cancelConfirmation,
    acceptConfirmation,
};

export default notification;
