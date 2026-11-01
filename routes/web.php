<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\DokterPortalController;
use App\Http\Controllers\KsmController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SpesialisasiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifikasiSipController;
use App\Http\Controllers\WhatsAppLogController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Must be logged in)
Route::middleware('auth')->group(function () {

    // ==========================================
    // 1. SDM & SUPER ADMIN PORTAL
    // ==========================================
    Route::middleware('role:super_admin,admin_sdm')->group(function () {
        // Dashboard Monitoring
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Master Data Dokter & Multi-SIP
        Route::get('/dokters/export-csv', [DokterController::class, 'exportCsv'])->name('dokters.export');
        Route::post('/dokters/import', [DokterController::class, 'import'])->name('dokters.import');
        Route::post('/dokters/{dokter}/sip', [DokterController::class, 'storeSip'])->name('dokters.sip.store');
        Route::resource('dokters', DokterController::class);

        // Master Data KSM & Spesialisasi
        Route::resource('ksm', KsmController::class)->except(['create', 'show', 'edit']);
        Route::resource('spesialisasi', SpesialisasiController::class)->except(['create', 'show', 'edit']);

        // Verifikasi SIP (Approval Queue)
        Route::get('/verifikasi-sip', [VerifikasiSipController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi-sip/{sipRecord}/preview', [VerifikasiSipController::class, 'previewPdf'])->name('verifikasi.preview');
        Route::get('/verifikasi-sip/{sipRecord}/download', [VerifikasiSipController::class, 'downloadPdf'])->name('verifikasi.download');
        Route::post('/verifikasi-sip/{sipRecord}/approve', [VerifikasiSipController::class, 'approve'])->name('verifikasi.approve');
        Route::post('/verifikasi-sip/{sipRecord}/reject', [VerifikasiSipController::class, 'reject'])->name('verifikasi.reject');

        // Matriks & Blast WhatsApp Gateway
        Route::get('/whatsapp', [WhatsAppLogController::class, 'index'])->name('whatsapp.index');
        Route::post('/whatsapp/manual', [WhatsAppLogController::class, 'sendManual'])->name('whatsapp.manual');
        Route::post('/whatsapp/trigger-cron', [WhatsAppLogController::class, 'triggerCron'])->name('whatsapp.trigger');
        Route::post('/whatsapp/resend/{log}', [WhatsAppLogController::class, 'resend'])->name('whatsapp.resend');

        // Laporan & Rekapitulasi Eksekutif / Akreditasi STARKES
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    });

    // ==========================================
    // 2. KHUSUS SUPER ADMIN (IT/SIRS)
    // ==========================================
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    });

    // ==========================================
    // 3. PORTAL DOKTER (MANDIRI)
    // ==========================================
    Route::middleware('role:dokter')->prefix('dokter')->name('dokter.')->group(function () {
        Route::get('/dashboard', [DokterPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/renewal', [DokterPortalController::class, 'renewal'])->name('renewal');
        Route::post('/renewal', [DokterPortalController::class, 'submitRenewal'])->name('renewal.post');
        Route::get('/riwayat', [DokterPortalController::class, 'riwayat'])->name('riwayat');
        Route::get('/profile', [DokterPortalController::class, 'profile'])->name('profile');
        Route::post('/profile/password', [DokterPortalController::class, 'updatePassword'])->name('password.update');
    });
});
