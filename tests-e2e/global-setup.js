// =====================================================================
// GLOBAL SETUP - dijalankan SEKALI sebelum semua test.
// 1) Membuat .env.testing dari .env (APP_KEY & kunci Reverb disalin, database diganti SQLite)
// 2) Memastikan koneksi testing benar-benar SQLite (pengaman agar MySQL tidak terhapus)
// 3) migrate:fresh + seeder -> setiap pengujian mulai dari data yang sama
// =====================================================================
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const PHP = process.env.PHP_BIN || 'php';

function artisan(args) {
    return execSync(`${PHP} artisan ${args} --env=testing`, { encoding: 'utf8', stdio: 'pipe' });
}

export default async function globalSetup() {
    const root = process.cwd();
    const envPath = path.join(root, '.env');

    if (!fs.existsSync(envPath)) {
        throw new Error('File .env tidak ditemukan. Jalankan "npm run test:e2e" dari folder project Laravel.');
    }

    const env = fs.readFileSync(envPath, 'utf8');
    const ambil = (kunci) => env.split(/\r?\n/).find((b) => b.startsWith(kunci + '='));

    // # Baris yang WAJIB disalin dari .env agar server testing & Reverb memakai kunci yang sama
    const wajib = ['APP_KEY', 'REVERB_APP_ID', 'REVERB_APP_KEY', 'REVERB_APP_SECRET', 'REVERB_HOST', 'REVERB_PORT', 'REVERB_SCHEME'];
    const hilang = wajib.filter((k) => !ambil(k));
    if (hilang.length) {
        throw new Error(`Baris berikut belum ada di .env: ${hilang.join(', ')}. Selesaikan Langkah 0 di panduan dulu.`);
    }

    // # Path absolut database testing. Garis miring "/" juga aman di Windows.
    const dbPath = (process.env.E2E_DB_PATH || path.join(root, 'database', 'testing.sqlite')).replace(/\\/g, '/');
    if (!process.env.E2E_DB_PATH) fs.writeFileSync(path.join(root, 'database', 'testing.sqlite'), '');

    const envTesting = [
        '# DIBUAT OTOMATIS oleh tests-e2e/global-setup.js - jangan diedit manual',
        'APP_NAME=Poliklinik',
        'APP_ENV=testing',
        ambil('APP_KEY'),
        'APP_DEBUG=true',
        'APP_URL=http://127.0.0.1:8001',
        'APP_LOCALE=id',
        '',
        'DB_CONNECTION=sqlite',
        `DB_DATABASE="${dbPath}"`,
        '',
        'SESSION_DRIVER=file',
        'CACHE_STORE=file',
        'QUEUE_CONNECTION=sync',
        'FILESYSTEM_DISK=local',
        '',
        'BROADCAST_CONNECTION=reverb',
        ...wajib.slice(1).map(ambil),
        'VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"',
        'VITE_REVERB_HOST="${REVERB_HOST}"',
        'VITE_REVERB_PORT="${REVERB_PORT}"',
        'VITE_REVERB_SCHEME="${REVERB_SCHEME}"',
        '',
    ].join('\n');
    fs.writeFileSync(path.join(root, '.env.testing'), envTesting);

    // # PENGAMAN: bersihkan cache config, lalu pastikan koneksi testing = sqlite.
    // # Kalau ternyata bukan sqlite, pengujian DIBATALKAN sebelum migrate:fresh dijalankan.
    artisan('config:clear');
    const koneksi = artisan('config:show database.default');
    if (!/sqlite/.test(koneksi)) {
        throw new Error('Koneksi database testing bukan SQLite. Pengujian dibatalkan agar database utama aman.\n' + koneksi);
    }

    console.log('\n[setup] Menyiapkan database testing (SQLite)...');
    artisan('migrate:fresh --seed --force');
    artisan('db:seed --class=DemoSeeder --force');
    console.log('[setup] Database testing siap.\n');
}
