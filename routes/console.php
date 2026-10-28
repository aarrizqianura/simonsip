<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Early Warning Engine (Cron Job Harian Pukul 07.00 WITA)
 * Sistem memeriksa masa berlaku SIP dan mengirimkan WhatsApp Reminder sesuai tahapan status
 */
Schedule::command('sip:check-expiry --send-wa')
    ->dailyAt('07:00')
    ->timezone('Asia/Makassar')
    ->name('simonsip-early-warning-daily');
