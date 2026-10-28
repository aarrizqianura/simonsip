<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSipExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sip:check-expiry {--send-wa : Kirim notifikasi WhatsApp ke dokter} {--force : Abaikan jeda pengiriman notifikasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemeriksaan status kadaluarsa SIP Dokter harian (Early Warning System RSUD Dr. H. Moch. Ansari Saleh)';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $waService): int
    {
        $this->info("Memulai pengecekan status kadaluarsa SIP Dokter...");
        $today = Carbon::today();

        // Ambil semua SIP yang sudah terverifikasi (bukan menunggu_verifikasi)
        $records = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi'])
            ->where('is_verified', true)
            ->where('status', '!=', 'menunggu_verifikasi')
            ->get();

        $countAman = 0;
        $countWarning = 0;
        $countMendesak = 0;
        $countExpired = 0;
        $waSent = 0;

        foreach ($records as $sip) {
            $days = (int) $today->diffInDays($sip->tgl_kadaluarsa, false);
            $oldStatus = $sip->status;
            $newStatus = 'aktif';

            if ($days > 90) {
                $newStatus = 'aktif';
                $countAman++;
            } elseif ($days >= 61) {
                $newStatus = 'warning';
                $countWarning++;
            } elseif ($days >= 31) {
                $newStatus = 'mendesak';
                $countMendesak++;
            } else {
                $newStatus = 'expired';
                $countExpired++;
            }

            // Update status record jika berubah
            if ($sip->status !== $newStatus) {
                $sip->update(['status' => $newStatus]);
                $this->line("SIP #{$sip->no_sip} ({$sip->dokter->nama_lengkap}): {$oldStatus} -> {$newStatus} ({$days} hari)");
            }

            // Jika opsi --send-wa aktif, kirim pesan jika sesuai kriteria
            if ($this->option('send-wa')) {
                $shouldSend = false;
                $msgType = null;

                if ($newStatus === 'warning') {
                    $msgType = 'early_warning';
                } elseif ($newStatus === 'mendesak' || $newStatus === 'expired') {
                    $msgType = 'urgent';
                }

                if ($msgType) {
                    // Cek apakah sudah pernah dikirim dalam 7 hari terakhir untuk tipe ini
                    $recentNotice = NotificationLog::where('sip_record_id', $sip->id)
                        ->where('message_type', $msgType)
                        ->where('created_at', '>=', Carbon::now()->subDays(7))
                        ->exists();

                    if (!$recentNotice || $this->option('force')) {
                        if ($newStatus === 'warning') {
                            $waService->sendEarlyWarningTahap1($sip);
                        } elseif ($newStatus === 'mendesak') {
                            $waService->sendEarlyWarningTahap2($sip);
                        } else {
                            $waService->sendExpiredWarning($sip);
                        }
                        $waSent++;
                    }
                }
            }
        }

        $this->info("Pengecekan selesai:");
        $this->info("- Aman (>90 hari): {$countAman}");
        $this->info("- Peringatan (61-90 hari): {$countWarning}");
        $this->info("- Mendesak (31-60 hari): {$countMendesak}");
        $this->info("- Kadaluarsa (<=30 hari / expired): {$countExpired}");
        if ($this->option('send-wa')) {
            $this->info("- Notifikasi WhatsApp terkirim: {$waSent}");
        }

        return Command::SUCCESS;
    }
}
