// =====================================================================
// KONFIGURASI PENGUJIAN OTOMATIS (Playwright)
// Jalankan  : npm run test:e2e
// Laporan   : npm run test:e2e:report
//
// # Pengujian memakai SERVER & DATABASE TERPISAH:
// #   - Laravel dijalankan di port 8001 dengan APP_ENV=testing -> membaca .env.testing
// #   - .env.testing (dibuat otomatis oleh global-setup) memakai SQLite database/testing.sqlite
// #   -> database MySQL yang kamu pakai sehari-hari TIDAK disentuh sama sekali.
// =====================================================================
import { defineConfig, devices } from '@playwright/test';

const PHP = process.env.PHP_BIN || 'php';
const BASE_URL = 'http://127.0.0.1:8001';

export default defineConfig({
    testDir: './tests-e2e',
    globalSetup: './tests-e2e/global-setup.js',

    // # Alur fitur saling bergantung (daftar -> periksa -> bayar -> verifikasi),
    // # jadi test dijalankan berurutan oleh 1 worker, tidak paralel.
    fullyParallel: false,
    workers: 1,
    timeout: 60_000,
    expect: { timeout: 8_000 },

    reporter: [['list'], ['html', { open: 'never' }]],

    use: {
        baseURL: BASE_URL,
        viewport: { width: 1366, height: 800 },
        screenshot: 'only-on-failure', // foto per test dilampirkan sendiri di fitur.spec.js (afterEach)
        trace: 'retain-on-failure',    // rekaman langkah demi langkah jika gagal
        acceptDownloads: true,
    },

    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

    // # Playwright menyalakan server yang belum berjalan.
    // # reuseExistingServer: kalau Reverb (8080) / Vite (5173) sudah kamu jalankan, dipakai ulang.
    webServer: process.env.E2E_NO_WEBSERVER ? undefined : [
        {
            command: `${PHP} artisan serve --host=127.0.0.1 --port=8001`,
            url: `${BASE_URL}/login`,
            env: { APP_ENV: 'testing' },   // -> Laravel membaca .env.testing
            reuseExistingServer: true,
            timeout: 60_000,
        },
        {
            command: `${PHP} artisan reverb:start`,
            port: 8080,
            reuseExistingServer: true,
            timeout: 60_000,
        },
        {
            command: 'npm run dev',
            port: 5173,
            reuseExistingServer: true,
            timeout: 60_000,
        },
    ],
});
