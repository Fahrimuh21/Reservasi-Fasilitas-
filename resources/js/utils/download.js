function filenameFromDisposition(disposition, fallbackName) {
    const utf8 = disposition?.match(/filename\*=UTF-8''([^;]+)/i)?.[1];
    if (utf8) return decodeURIComponent(utf8.replace(/["']/g, ''));

    return disposition?.match(/filename="?([^";]+)"?/i)?.[1] || fallbackName;
}

export async function downloadCsvResponse(response, fallbackName) {
    const contentType = response.headers?.['content-type'] || '';
    if (!contentType.toLowerCase().includes('text/csv')) {
        throw new Error('Server tidak mengirim file CSV yang valid.');
    }

    const blob = response.data instanceof Blob
        ? response.data
        : new Blob([response.data], { type: contentType });
    const downloadUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filenameFromDisposition(response.headers?.['content-disposition'], fallbackName);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
}

export async function downloadErrorMessage(error, fallback) {
    const payload = error?.response?.data;
    if (payload instanceof Blob && payload.type.includes('json')) {
        try {
            const body = JSON.parse(await payload.text());
            return Object.values(body.errors || {}).flat().join(' ') || body.message || fallback;
        } catch {
            return fallback;
        }
    }

    if (payload instanceof Blob) return fallback;

    return error?.response?.data?.message || error?.message || fallback;
}
