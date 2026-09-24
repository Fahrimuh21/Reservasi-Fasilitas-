// Run with: node tests/Browser/workflow.mjs (requires @playwright/test and Edge).
import { chromium, expect } from '@playwright/test';
import { spawn, spawnSync } from 'node:child_process';
import { mkdtemp, mkdir, unlink, rmdir } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import net from 'node:net';

const directory = await mkdtemp(join(tmpdir(), 'reservasi-browser-'));
const database = join(directory, 'testing.sqlite');
const env = { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: database, CACHE_STORE: 'array', SESSION_DRIVER: 'array', BCRYPT_ROUNDS: '4' };
const fixture = spawnSync('php', ['tests/Browser/prepare.php'], { env, encoding: 'utf8', windowsHide: true });
if (fixture.status !== 0) throw new Error(fixture.stderr || fixture.stdout);
const data = JSON.parse(fixture.stdout);
const portProbe = net.createServer();
await new Promise(resolve => portProbe.listen(0, '127.0.0.1', resolve));
const port = portProbe.address().port;
await new Promise(resolve => portProbe.close(resolve));
const baseURL = `http://127.0.0.1:${port}`;
const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', '.', '../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'], { cwd: 'public', env, windowsHide: true, stdio: 'ignore' });
const artifacts = 'storage/app/private/browser-check';
await mkdir(artifacts, { recursive: true });
let browser;
const failures = [];

async function session(email, route) {
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const response = await context.request.post(`${baseURL}/api/login`, { data: { email, password: 'password' } });
    expect(response.ok()).toBeTruthy();
    const body = await response.text();
    if (!body.trim().startsWith('{')) throw new Error(body.slice(0, 1600));
    const auth = JSON.parse(body);
    await context.addInitScript(({ token, user }) => {
        localStorage.setItem('reservasi_auth_token', token);
        localStorage.setItem('reservasi_auth_user', JSON.stringify(user));
    }, auth);
    const page = await context.newPage();
    page.setDefaultTimeout(10000);
    page.on('pageerror', error => failures.push(error.message));
    await page.goto(`${baseURL}/${route}`);
    return page;
}

async function requestBooking(page, start, end, purpose) {
    await page.getByRole('button', { name: 'Reservasi baru', exact: true }).click();
    const dialog = page.getByRole('dialog');
    await dialog.getByLabel('Fasilitas', { exact: true }).selectOption(String(data.facility_id));
    await dialog.getByLabel('Tanggal', { exact: true }).fill(data.date);
    await dialog.getByLabel('Mulai (WIB)', { exact: true }).fill(start);
    await dialog.getByLabel('Selesai (WIB)', { exact: true }).fill(end);
    await dialog.getByLabel('Keperluan').fill(purpose);
    await expect(dialog.getByRole('button', { name: 'Ajukan reservasi' })).toBeEnabled();
    await dialog.getByRole('button', { name: 'Ajukan reservasi' }).click();
    await expect(dialog).toHaveCount(0);
    await expect(page.getByRole('status')).toContainText('tersimpan');
}

async function noOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
    const content = page.locator('.workflow');
    const box = await content.boundingBox();
    expect(box.width).toBeGreaterThan(250);
    expect(box.x + box.width).toBeLessThanOrEqual(page.viewportSize().width + 1);
    if (page.viewportSize().width < 680) {
        await expect.poll(async () => (await page.locator('.sidebar').boundingBox()).width).toBe(page.viewportSize().width);
        expect((await page.locator('.sidebar').boundingBox()).height).toBeLessThan(110);
    }
}

try {
    await expect.poll(async () => {
        try { return (await fetch(`${baseURL}/api/facilities`)).status; } catch { return 0; }
    }, { timeout: 15000 }).toBe(200);
    browser = await chromium.launch({ channel: 'msedge', headless: true });
    const user = await session('browser-user@example.test', 'reservations');
    const officer = await session('browser-officer@example.test', 'officer');
    await requestBooking(user, '09:00', '10:00', 'Rapat verifikasi otomatis');
    const pending = officer.locator('article').filter({ hasText: 'Rapat verifikasi otomatis' });
    await expect(pending).toBeVisible({ timeout: 10000 });
    await pending.getByRole('button', { name: 'Konfirmasi', exact: true }).click();
    await officer.getByRole('dialog').getByRole('button', { name: 'Setujui reservasi' }).click();
    await expect(officer.getByRole('dialog')).toHaveCount(0);
    await user.getByRole('button', { name: 'Disetujui', exact: true }).click();
    await expect(user.locator('article').filter({ hasText: 'Rapat verifikasi otomatis' })).toContainText('Disetujui', { timeout: 10000 });
    await officer.getByRole('button', { name: /^Disetujui/ }).click();
    await noOverflow(officer);
    await officer.screenshot({ path: join(artifacts, 'officer-desktop.png'), fullPage: true });
    await user.reload();
    await expect(user.locator('article').filter({ hasText: 'Rapat verifikasi otomatis' })).toContainText('Disetujui');

    await requestBooking(user, '11:00', '12:00', 'Permintaan untuk ditolak');
    await officer.getByRole('button', { name: /^Menunggu/ }).click();
    const rejected = officer.locator('article').filter({ hasText: 'Permintaan untuk ditolak' });
    await expect(rejected).toBeVisible({ timeout: 10000 });
    await rejected.getByRole('button', { name: 'Tolak', exact: true }).click();
    await officer.getByRole('dialog').getByLabel('Catatan penolakan').fill('Jadwal kegiatan tidak sesuai');
    await officer.route('**/api/officer/reservations/*/reject', route => route.fulfill({ status: 500, contentType: 'application/json', body: '{}' }));
    await officer.getByRole('dialog').getByRole('button', { name: 'Tolak reservasi' }).click();
    await expect(officer.getByRole('dialog').getByRole('alert')).toContainText('Coba lagi');
    await officer.unroute('**/api/officer/reservations/*/reject');
    await officer.getByRole('dialog').getByRole('button', { name: 'Tolak reservasi' }).click();
    await expect(officer.getByRole('dialog')).toHaveCount(0);
    await user.getByRole('button', { name: 'Ditolak', exact: true }).click();
    await expect(user.locator('article').filter({ hasText: 'Permintaan untuk ditolak' })).toContainText('Jadwal kegiatan tidak sesuai', { timeout: 10000 });

    await requestBooking(user, '13:00', '14:00', 'Permintaan untuk dibatalkan');
    const cancelled = user.locator('article').filter({ hasText: 'Permintaan untuk dibatalkan' });
    await expect(cancelled).toBeVisible();
    await cancelled.getByRole('button', { name: 'Batalkan', exact: true }).click();
    await user.getByRole('dialog').getByLabel('Alasan pembatalan').fill('Jadwal berubah');
    await user.getByRole('dialog').getByRole('button', { name: 'Batalkan reservasi' }).click();
    await expect(user.getByRole('dialog')).toHaveCount(0);
    await officer.getByRole('button', { name: /^Dibatalkan/ }).click();
    await expect(officer.locator('article').filter({ hasText: 'Permintaan untuk dibatalkan' })).toContainText('Dibatalkan', { timeout: 10000 });

    await user.setViewportSize({ width: 390, height: 844 });
    await user.getByRole('button', { name: 'Semua', exact: true }).click();
    await noOverflow(user);
    await user.screenshot({ path: join(artifacts, 'user-mobile.png'), fullPage: true });
    await user.getByRole('button', { name: 'Reservasi baru', exact: true }).click();
    const mobileDialog = await user.getByRole('dialog').boundingBox();
    expect(mobileDialog.width).toBeLessThanOrEqual(390);
    await user.screenshot({ path: join(artifacts, 'booking-mobile.png') });
    await user.getByRole('dialog').getByRole('button', { name: 'Tutup', exact: true }).click();
    await officer.setViewportSize({ width: 390, height: 844 });
    await noOverflow(officer);
    await officer.screenshot({ path: join(artifacts, 'officer-mobile.png'), fullPage: true });

    await user.goto(`${baseURL}/report`);
    await user.getByRole('combobox', { name: 'Fasilitas', exact: true }).selectOption(String(data.facility_id));
    await user.getByRole('combobox', { name: 'Kategori', exact: true }).selectOption('Elektronik');
    await user.getByLabel('Deskripsi kerusakan').fill('Proyektor perlu perbaikan');
    await user.getByRole('button', { name: 'Kirim laporan' }).click();
    await expect(user.getByRole('status')).toContainText('tersimpan');
    await officer.getByRole('button', { name: /^Laporan/ }).click();
    const report = officer.locator('article').filter({ hasText: 'Proyektor perlu perbaikan' });
    await expect(report).toBeVisible({ timeout: 10000 });
    await report.getByRole('button', { name: 'Proses', exact: true }).click();
    await officer.getByRole('dialog').getByRole('button', { name: 'Simpan status' }).click();
    await expect(report).toContainText('Diproses', { timeout: 10000 });
    await report.getByRole('button', { name: 'Selesaikan', exact: true }).click();
    await officer.getByRole('dialog').getByLabel('Catatan (opsional)').fill('Kabel diganti');
    await officer.getByRole('dialog').getByRole('button', { name: 'Simpan status' }).click();
    await expect(user.locator('article').filter({ hasText: 'Proyektor perlu perbaikan' })).toContainText('Kabel diganti', { timeout: 10000 });
    expect(failures).toEqual([]);
    console.log('PASS: request, approval, rejection/retry, cancellation, persistence, automatic updates, reports, desktop/mobile layout.');
    console.log(`Screenshots: ${artifacts}`);
} catch (error) {
    if (browser) {
        for (const [index, context] of browser.contexts().entries()) {
            const page = context.pages()[0];
            if (!page) continue;
            await page.screenshot({ path: join(artifacts, `failure-${index}.png`), fullPage: true });
            console.log(await page.locator('body').innerText());
        }
    }
    console.log('Browser errors:', failures);
    throw error;
} finally {
    await browser?.close();
    const stopped = new Promise(resolve => server.once('exit', resolve));
    server.kill();
    await stopped;
    await unlink(database);
    await rmdir(directory);
}
