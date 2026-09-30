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
    await page.goto(`${baseURL}/${route}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
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
    await expect(dialog).toHaveCount(0, { timeout: 20000 });
    await expect(page.getByRole('status')).toContainText('tersimpan');
}

async function noOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
    expect(await page.evaluate(() => document.documentElement.scrollHeight <= window.innerHeight + 1)).toBeTruthy();
    const content = page.locator('.workflow');
    const box = await content.boundingBox();
    expect(box.width).toBeGreaterThan(250);
    expect(box.x + box.width).toBeLessThanOrEqual(page.viewportSize().width + 1);
    expect(await page.locator('.sidebar').evaluate(element => ({
        fits: element.scrollHeight <= element.clientHeight + 1,
        overflowY: getComputedStyle(element).overflowY,
    }))).toEqual({ fits: true, overflowY: 'hidden' });
    const sidebarTop = (await page.locator('.sidebar').boundingBox()).y;
    await page.locator('.main-content').evaluate(element => { element.scrollTop = element.scrollHeight; });
    expect((await page.locator('.sidebar').boundingBox()).y).toBe(sidebarTop);
    expect(await page.evaluate(() => window.scrollY)).toBe(0);
    await page.locator('.main-content').evaluate(element => { element.scrollTop = 0; });
    if (page.viewportSize().width <= 900) {
        const toggle = page.getByRole('button', { name: 'Buka menu' });
        await expect(toggle).toBeVisible();
        await expect.poll(async () => {
            const sidebar = await page.locator('.sidebar').boundingBox();
            return sidebar.x;
        }).toBeGreaterThanOrEqual(page.viewportSize().width - 1);
        await toggle.click();
        await expect(page.getByRole('button', { name: 'Tutup menu' })).toBeVisible();
        await expect.poll(async () => {
            const sidebar = await page.locator('.sidebar').boundingBox();
            return sidebar.x + sidebar.width;
        }).toBeLessThanOrEqual(page.viewportSize().width + 1);
        expect((await page.locator('.sidebar').boundingBox()).width).toBeLessThanOrEqual(page.viewportSize().width);
        await page.keyboard.press('Escape');
        await expect(page.getByRole('button', { name: 'Buka menu' })).toBeVisible();
        await expect.poll(async () => {
            const sidebar = await page.locator('.sidebar').boundingBox();
            return sidebar.x;
        }).toBeGreaterThanOrEqual(page.viewportSize().width - 1);
    } else {
        const sidebar = await page.locator('.sidebar').boundingBox();
        expect(sidebar.width).toBeGreaterThanOrEqual(240);
        expect(sidebar.width).toBeLessThanOrEqual(290);
    }
}

async function noDocumentOverflow(page, selector) {
    await expect(page.locator(selector)).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
    if (await page.locator('.auth-screen--register').count()) {
        expect(await page.evaluate(() => document.documentElement.scrollHeight <= window.innerHeight + 1)).toBeTruthy();
        const panel = await page.locator('.auth-screen__panel').boundingBox();
        expect(panel.y).toBeGreaterThanOrEqual(0);
        expect(panel.y + panel.height).toBeLessThanOrEqual(page.viewportSize().height + 1);
    }
    if (await page.locator('.sidebar').count()) {
        expect(await page.evaluate(() => document.documentElement.scrollHeight <= window.innerHeight + 1)).toBeTruthy();
        expect(await page.locator('.sidebar').evaluate(element => ({
            fits: element.scrollHeight <= element.clientHeight + 1,
            overflowY: getComputedStyle(element).overflowY,
        }))).toEqual({ fits: true, overflowY: 'hidden' });
    }
}

try {
    await expect.poll(async () => {
        try { return (await fetch(`${baseURL}/api/facilities`)).status; } catch { return 0; }
    }, { timeout: 15000 }).toBe(200);
    browser = await chromium.launch({ channel: 'msedge', headless: true });
    const user = await session('browser-user@example.test', 'reservations');
    const officer = await session('browser-officer@example.test', 'officer');
    const admin = await session('browser-admin@example.test', 'admin');
    const publicContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const publicPage = await publicContext.newPage();
    publicPage.setDefaultTimeout(15000);
    publicPage.on('pageerror', error => failures.push(error.message));

    for (const width of [320, 360, 390, 768, 1024, 1440]) {
        const height = width <= 390 ? 844 : 900;
        await publicPage.setViewportSize({ width, height });
        await publicPage.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await expect(publicPage.locator('.landing-nav')).toBeVisible();
        expect(await publicPage.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
        if (width === 390 || width === 1440) await publicPage.waitForTimeout(800);
        if (width === 390) await publicPage.screenshot({ path: join(artifacts, 'landing-mobile.png'), fullPage: true });
        if (width === 1440) await publicPage.screenshot({ path: join(artifacts, 'landing-desktop.png'), fullPage: true });
        if (width <= 840) {
            const menuButton = publicPage.getByRole('button', { name: 'Buka menu navigasi' });
            await expect(menuButton).toBeVisible();
            expect((await menuButton.boundingBox()).width).toBeGreaterThanOrEqual(44);
            await menuButton.click();
            await expect(menuButton).toHaveAttribute('aria-expanded', 'true');
            await expect(publicPage.getByRole('dialog', { name: 'Menu navigasi' })).toBeVisible();
            if (width === 390) await publicPage.screenshot({ path: join(artifacts, 'landing-menu-mobile.png') });
            expect(await publicPage.evaluate(() => getComputedStyle(document.body).overflow)).toBe('hidden');
            await publicPage.keyboard.press('Escape');
            await expect(publicPage.getByRole('dialog', { name: 'Menu navigasi' })).toHaveCount(0);
            await expect(menuButton).toBeFocused();
            expect(await publicPage.evaluate(() => document.body.classList.contains('landing-menu-open'))).toBeFalsy();
            if (width === 390) {
                await menuButton.click();
                await publicPage.locator('.mobile-nav-overlay').click({ position: { x: 5, y: 5 } });
                await expect(publicPage.getByRole('dialog', { name: 'Menu navigasi' })).toHaveCount(0);
                await menuButton.click();
                await publicPage.getByRole('dialog', { name: 'Menu navigasi' }).getByRole('link', { name: 'Fasilitas', exact: true }).click();
                await expect(publicPage.getByRole('dialog', { name: 'Menu navigasi' })).toHaveCount(0);
            }
        } else {
            await expect(publicPage.getByRole('button', { name: 'Buka menu navigasi' })).toBeHidden();
        }
        await publicPage.goto(`${baseURL}/login`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await noDocumentOverflow(publicPage, '.auth-screen__panel');
        await publicPage.goto(`${baseURL}/register`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await noDocumentOverflow(publicPage, '.auth-screen__panel');
        await publicPage.goto(`${baseURL}/facilities`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await noDocumentOverflow(publicPage, '#screen-facilities');
        await admin.setViewportSize({ width, height });
        await noDocumentOverflow(admin, '#screen-admin');
    }
    await publicPage.setViewportSize({ width: 768, height: 900 });
    await publicPage.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 20000 });
    await publicPage.getByRole('button', { name: 'Buka menu navigasi' }).click();
    await publicPage.setViewportSize({ width: 1024, height: 900 });
    await expect(publicPage.getByRole('dialog', { name: 'Menu navigasi' })).toHaveCount(0);
    await expect.poll(() => publicPage.evaluate(() => document.body.classList.contains('landing-menu-open'))).toBeFalsy();
    for (const rolePage of [user, officer]) {
        await rolePage.setViewportSize({ width: 1024, height: 650 });
        await noOverflow(rolePage);
        await rolePage.setViewportSize({ width: 1440, height: 1000 });
    }
    await admin.setViewportSize({ width: 1024, height: 650 });
    await noDocumentOverflow(admin, '#screen-admin');
    await publicPage.setViewportSize({ width: 390, height: 844 });
    await publicPage.goto(`${baseURL}/register`, { waitUntil: 'domcontentloaded', timeout: 20000 });
    await publicPage.screenshot({ path: join(artifacts, 'register-mobile.png'), fullPage: true });
    await admin.setViewportSize({ width: 390, height: 844 });
    await expect.poll(async () => {
        const sidebar = await admin.locator('.sidebar').boundingBox();
        return sidebar.x;
    }).toBeGreaterThanOrEqual(389);
    await admin.locator('.main-content').evaluate(element => { element.scrollTop = 0; });
    await admin.screenshot({ path: join(artifacts, 'admin-mobile.png') });
    await admin.locator('.main-content').evaluate(element => { element.scrollTop = element.scrollHeight; });
    await admin.screenshot({ path: join(artifacts, 'admin-mobile-bottom.png') });
    await admin.locator('.main-content').evaluate(element => { element.scrollTop = 0; });
    await admin.getByRole('button', { name: 'Tambah fasilitas' }).click();
    await noDocumentOverflow(admin, '[role="dialog"]');
    await admin.screenshot({ path: join(artifacts, 'admin-modal-mobile.png') });
    await admin.getByRole('button', { name: 'Batal' }).click();

    await requestBooking(user, '09:00', '10:00', 'Rapat verifikasi otomatis');
    const pending = officer.locator('article').filter({ hasText: 'Rapat verifikasi otomatis' });
    await expect(pending).toBeVisible({ timeout: 10000 });
    await pending.getByRole('button', { name: 'Konfirmasi', exact: true }).click();
    await officer.getByRole('dialog').getByRole('button', { name: 'Setujui reservasi' }).click();
    await expect(officer.getByRole('dialog')).toHaveCount(0, { timeout: 20000 });
    await user.getByRole('button', { name: 'Disetujui', exact: true }).click();
    await expect(user.locator('article').filter({ hasText: 'Rapat verifikasi otomatis' })).toContainText('Disetujui', { timeout: 10000 });
    await officer.getByRole('button', { name: /^Disetujui/ }).click();
    await noOverflow(officer);
    await officer.screenshot({ path: join(artifacts, 'officer-desktop.png'), fullPage: true });
    await user.reload();
    await expect(user.locator('article').filter({ hasText: 'Rapat verifikasi otomatis' })).toContainText('Disetujui');

    await requestBooking(user, '11:00', '12:00', 'Permintaan untuk ditolak');
    await expect(officer.getByRole('button', { name: 'Perbarui data' })).toBeEnabled();
    await officer.getByRole('button', { name: 'Perbarui data' }).click();
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
    await expect(officer.getByRole('dialog')).toHaveCount(0, { timeout: 20000 });
    await user.getByRole('button', { name: 'Ditolak', exact: true }).click();
    await expect(user.locator('article').filter({ hasText: 'Permintaan untuk ditolak' })).toContainText('Jadwal kegiatan tidak sesuai', { timeout: 10000 });

    await requestBooking(user, '13:00', '14:00', 'Permintaan untuk dibatalkan');
    const cancelled = user.locator('article').filter({ hasText: 'Permintaan untuk dibatalkan' });
    await expect(cancelled).toBeVisible();
    await cancelled.getByRole('button', { name: 'Batalkan', exact: true }).click();
    await user.getByRole('dialog').getByLabel('Alasan pembatalan').fill('Jadwal berubah');
    await user.getByRole('dialog').getByRole('button', { name: 'Batalkan reservasi' }).click();
    await expect(user.getByRole('dialog')).toHaveCount(0, { timeout: 20000 });
    await officer.getByRole('button', { name: /^Dibatalkan/ }).click();
    await expect(officer.locator('article').filter({ hasText: 'Permintaan untuk dibatalkan' })).toContainText('Dibatalkan', { timeout: 10000 });

    await user.getByRole('button', { name: 'Semua', exact: true }).click();
    for (const width of [320, 360, 390, 768, 1024, 1440]) {
        await user.setViewportSize({ width, height: width <= 390 ? 844 : 900 });
        await noOverflow(user);
    }
    await user.setViewportSize({ width: 390, height: 844 });
    await noOverflow(user);
    await user.screenshot({ path: join(artifacts, 'user-mobile.png'), fullPage: true });
    await user.getByRole('button', { name: 'Reservasi baru', exact: true }).click();
    const mobileDialog = await user.getByRole('dialog').boundingBox();
    expect(mobileDialog.width).toBeLessThanOrEqual(390);
    const bookingDialog = user.getByRole('dialog');
    await bookingDialog.getByRole('button', { name: '15:00', exact: true }).click();
    await expect(bookingDialog.getByLabel('Mulai (WIB)', { exact: true })).toHaveValue('15:00');
    await expect(bookingDialog.getByLabel('Selesai (WIB)', { exact: true })).toHaveValue('');
    await bookingDialog.getByRole('button', { name: '15:00', exact: true }).click();
    await expect(bookingDialog.getByLabel('Mulai (WIB)', { exact: true })).toHaveValue('');
    await bookingDialog.getByRole('button', { name: '15:00', exact: true }).click();
    await bookingDialog.getByRole('button', { name: '15:30', exact: true }).click();
    await expect(bookingDialog.getByLabel('Mulai (WIB)', { exact: true })).toHaveValue('15:00');
    await expect(bookingDialog.getByLabel('Selesai (WIB)', { exact: true })).toHaveValue('15:30');
    await expect(bookingDialog.getByRole('button', { name: '15:00', exact: true })).toHaveAttribute('aria-pressed', 'true');
    await expect(bookingDialog.getByRole('button', { name: '15:30', exact: true })).toHaveAttribute('aria-pressed', 'true');
    await user.screenshot({ path: join(artifacts, 'booking-mobile.png') });
    await user.getByRole('dialog').getByRole('button', { name: 'Tutup', exact: true }).click();
    await officer.setViewportSize({ width: 390, height: 844 });
    await noOverflow(officer);
    await officer.screenshot({ path: join(artifacts, 'officer-mobile.png'), fullPage: true });

    await user.goto(`${baseURL}/report`, { waitUntil: 'domcontentloaded', timeout: 20000 });
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
    await officer.setViewportSize({ width: 1440, height: 1000 });
    await officer.locator('.sidebar').getByRole('button', { name: 'Keluar' }).click();
    await expect(officer).toHaveURL(`${baseURL}/`);
    await admin.setViewportSize({ width: 1440, height: 1000 });
    await admin.locator('.main-content').evaluate(element => { element.scrollTop = 0; });
    await admin.screenshot({ path: join(artifacts, 'admin-desktop.png') });
    await admin.locator('.app-topbar').getByRole('button', { name: 'Keluar' }).click();
    await expect(admin).toHaveURL(`${baseURL}/`);
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
