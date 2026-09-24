const campusTimezone = 'Asia/Jakarta';

export function formatDate(value) {
    return value ? new Date(value).toLocaleDateString('id-ID', { timeZone: campusTimezone, day: 'numeric', month: 'short', year: 'numeric' }) : '-';
}

export function formatTime(value) {
    return value ? new Date(value).toLocaleTimeString('id-ID', { timeZone: campusTimezone, hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }) : '-';
}

export function campusToday() {
    const parts = new Intl.DateTimeFormat('en-CA', { timeZone: campusTimezone, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
    const part = type => parts.find(item => item.type === type).value;
    return `${part('year')}-${part('month')}-${part('day')}`;
}

export function statusLabel(status) {
    return { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak', cancelled: 'Dibatalkan', new: 'Baru', in_progress: 'Diproses', resolved: 'Selesai', active: 'Aktif', inactive: 'Nonaktif', maintenance: 'Perbaikan' }[status] || status;
}
