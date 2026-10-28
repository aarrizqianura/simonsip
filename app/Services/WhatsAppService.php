<?php

namespace App\Services;

use App\Models\Dokter;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $provider;
    protected string $apiUrl;
    protected ?string $token;
    protected bool $simulate;
    protected string $sdmPhone;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', env('WA_GATEWAY_PROVIDER', 'fonnte'));
        $this->apiUrl = config('services.whatsapp.api_url', env('WA_GATEWAY_API_URL', 'https://api.fonnte.com/send'));
        $this->token = config('services.whatsapp.token', env('WA_GATEWAY_TOKEN', ''));
        $this->simulate = (bool) env('WA_GATEWAY_SIMULATE', true);
        $this->sdmPhone = env('WA_SDM_PHONE', '081234567890');
    }

    /**
     * Send WA message and log to database
     */
    public function sendMessage(string $phone, string $message, ?SipRecord $sipRecord = null, string $messageType = 'early_warning'): array
    {
        $cleanPhone = $this->formatPhoneNumber($phone);
        $status = 'pending';
        $sentAt = null;

        if ($this->simulate || empty($this->token) || $this->token === 'SIMONSIP_DEMO_TOKEN') {
            // Simulated delivery
            $status = 'sent';
            $sentAt = Carbon::now();
            Log::info("[SIMONSIP WA Gateway Sim] To: {$cleanPhone} | Type: {$messageType}\nMessage:\n{$message}");
        } else {
            try {
                if ($this->provider === 'fonnte') {
                    $response = Http::withHeaders([
                        'Authorization' => $this->token,
                    ])->post($this->apiUrl, [
                        'target' => $cleanPhone,
                        'message' => $message,
                    ]);

                    if ($response->successful()) {
                        $status = 'sent';
                        $sentAt = Carbon::now();
                    } else {
                        $status = 'failed';
                        Log::error("[SIMONSIP WA Gateway Error] HTTP {$response->status()}: " . $response->body());
                    }
                } else {
                    // Generic Webhook/Provider
                    $response = Http::post($this->apiUrl, [
                        'phone' => $cleanPhone,
                        'message' => $message,
                    ]);
                    $status = $response->successful() ? 'sent' : 'failed';
                    $sentAt = $response->successful() ? Carbon::now() : null;
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                Log::error("[SIMONSIP WA Gateway Exception]: " . $e->getMessage());
            }
        }

        // Record log if SipRecord exists
        if ($sipRecord) {
            NotificationLog::create([
                'sip_record_id' => $sipRecord->id,
                'recipient_phone' => $cleanPhone,
                'channel' => 'whatsapp',
                'message_type' => $messageType,
                'payload' => $message,
                'status' => $status,
                'sent_at' => $sentAt,
            ]);
        }

        return [
            'status' => $status,
            'phone' => $cleanPhone,
            'message' => $message,
        ];
    }

    /**
     * Format phone number to Indonesian format (e.g., 0812... -> 62812... or 08...)
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }
        return $clean;
    }

    /**
     * Template 1: Notifikasi Akun Baru + Kredensial Login
     */
    public function sendAccountCreatedNotification(Dokter $dokter, User $user, string $plainPassword, SipRecord $sipRecord): array
    {
        $loginUrl = url('/login');
        $tglKadaluarsa = $sipRecord->tgl_kadaluarsa ? $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y') : '-';

        $message = "🏥 *RSUD DR. H. MOCH. ANSARI SALEH BANJARMASIN*\n"
            . "📋 *Sistem Monitoring SIP Dokter (SIMONSIP)*\n\n"
            . "Yth. *{$dokter->nama_lengkap}*,\n\n"
            . "Akun portal SIMONSIP Anda telah berhasil didaftarkan oleh Admin SDM Kepegawaian.\n\n"
            . "🔐 *Informasi Kredensial Login:*\n"
            . "• URL Login: {$loginUrl}\n"
            . "• Username: *{$user->username}*\n"
            . "• Password Default: *{$plainPassword}*\n\n"
            . "📄 *Data Legalitas SIP Terdaftar:*\n"
            . "• No. SIP: *{$sipRecord->no_sip}*\n"
            . "• Tempat Praktik: Ke-{$sipRecord->tempat_praktik_ke} (RSUD Dr. H. Moch. Ansari Saleh)\n"
            . "• Masa Berlaku: s/d *{$tglKadaluarsa}*\n"
            . "• Status STR: " . ($dokter->is_str_seumur_hidup ? 'Seumur Hidup' : ($dokter->tgl_kadaluarsa_str?->format('d-m-Y') ?? '-')) . "\n\n"
            . "⚠️ *Penting:* Demi keamanan akun, segera login dan ubah password default Anda pada menu Pengaturan Akun.\n\n"
            . "_Pesan otomatis oleh SIMONSIP RSUD Dr. H. Moch. Ansari Saleh_";

        return $this->sendMessage($dokter->no_whatsapp, $message, $sipRecord, 'account_created');
    }

    /**
     * Template 2: Early Warning Tahap 1 (61-90 Hari)
     */
    public function sendEarlyWarningTahap1(SipRecord $sipRecord): array
    {
        $dokter = $sipRecord->dokter;
        $renewalUrl = url('/dokter/renewal');
        $tglExp = $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y');

        $message = "🟡 *PERINGATAN MASA BERLAKU SIP (TAHAP 1)*\n"
            . "🏥 *RSUD Dr. H. Moch. Ansari Saleh Banjarmasin*\n\n"
            . "Yth. *{$dokter->nama_lengkap}*,\n\n"
            . "Pemberitahuan bahwa Surat Izin Praktik (SIP) Anda di RSUD Dr. H. Moch. Ansari Saleh akan memasuki masa kadaluarsa dalam waktu dekat:\n\n"
            . "• No. SIP: *{$sipRecord->no_sip}*\n"
            . "• Praktik Ke-: {$sipRecord->tempat_praktik_ke}\n"
            . "• Tanggal Kadaluarsa: *{$tglExp}*\n"
            . "• Sisa Masa Berlaku: *{$sipRecord->sisa_hari} Hari*\n"
            . "• Status: 🟡 *Peringatan Dini*\n\n"
            . "Mohon segera mempersiapkan berkas perpanjangan SIP (rekomendasi IDI/OP, STR aktif, dll). Anda dapat mengajukan perpanjangan secara mandiri melalui tautan berikut:\n"
            . "👉 {$renewalUrl}\n\n"
            . "_Bagian Kepegawaian & SDM RSUD Dr. H. Moch. Ansari Saleh_";

        return $this->sendMessage($dokter->no_whatsapp, $message, $sipRecord, 'early_warning');
    }

    /**
     * Template 3: Early Warning Tahap 2 (31-60 Hari)
     */
    public function sendEarlyWarningTahap2(SipRecord $sipRecord): array
    {
        $dokter = $sipRecord->dokter;
        $renewalUrl = url('/dokter/renewal');
        $tglExp = $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y');

        $message = "🟠 *PERINGATAN MASA BERLAKU SIP - MENDESAK (TAHAP 2)*\n"
            . "🏥 *RSUD Dr. H. Moch. Ansari Saleh Banjarmasin*\n\n"
            . "Yth. *{$dokter->nama_lengkap}*,\n\n"
            . "Masa berlaku Surat Izin Praktik (SIP) Anda tersisa *{$sipRecord->sisa_hari} hari* lagi:\n\n"
            . "• No. SIP: *{$sipRecord->no_sip}*\n"
            . "• Praktik Ke-: {$sipRecord->tempat_praktik_ke}\n"
            . "• Tanggal Kadaluarsa: *{$tglExp}*\n"
            . "• Status: 🟠 *Mendesak*\n\n"
            . "Harap segera mengunggah dokumen perpanjangan SIP baru Anda ke SIMONSIP agar pelayanan medis tidak terkendala regulasi STARKES/KARS:\n"
            . "👉 Unggah berkas: {$renewalUrl}\n\n"
            . "_Pesan otomatis SIMONSIP - Bagian SDM RSUD Ansari Saleh_";

        return $this->sendMessage($dokter->no_whatsapp, $message, $sipRecord, 'urgent');
    }

    /**
     * Template 4: Warning Urgent / Kadaluarsa (<= 30 Hari atau Expired)
     */
    public function sendExpiredWarning(SipRecord $sipRecord): array
    {
        $dokter = $sipRecord->dokter;
        $renewalUrl = url('/dokter/renewal');
        $tglExp = $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y');
        $sisa = $sipRecord->sisa_hari;
        $sisaText = $sisa < 0 ? "TELAH KADALUARSA (" . abs($sisa) . " hari lalu)" : "TINGGAL {$sisa} HARI LAGI";

        $message = "🔴 *PERINGATAN KRITIS: SIP {$sisaText}*\n"
            . "🏥 *RSUD Dr. H. Moch. Ansari Saleh Banjarmasin*\n\n"
            . "Yth. *{$dokter->nama_lengkap}*,\n\n"
            . "Surat Izin Praktik (SIP) Anda:\n"
            . "• No. SIP: *{$sipRecord->no_sip}*\n"
            . "• Praktik Ke-: {$sipRecord->tempat_praktik_ke}\n"
            . "• Batas Waktu: *{$tglExp}*\n"
            . "• Status: 🔴 *KADALUARSA / KRITIS*\n\n"
            . "Sesuai regulasi undang-undang kesehatan, dokter wajib memiliki SIP aktif dalam memberikan pelayanan medis. Mohon segera hubungi Subbagian SDM Kepegawaian RSUD Ansari Saleh atau unggah SIP baru Anda sekarang:\n"
            . "👉 {$renewalUrl}\n\n"
            . "_Pemberitahuan Darurat SIMONSIP RSUD Ansari Saleh_";

        $res = $this->sendMessage($dokter->no_whatsapp, $message, $sipRecord, 'urgent');

        // Juga kirim summary ke Admin SDM
        if ($this->sdmPhone) {
            $sdmMsg = "🚨 *NOTIFIKASI SDM: SIP DOKTER KRITIS/EXPIRED*\n"
                . "Dokter: *{$dokter->nama_lengkap}* ({$dokter->nip_nik})\n"
                . "KSM: {$dokter->ksm?->nama_ksm}\n"
                . "No. SIP: {$sipRecord->no_sip}\n"
                . "Kadaluarsa: {$tglExp} ({$sisaText})\n"
                . "No. WA Dokter: {$dokter->no_whatsapp}";
            $this->sendMessage($this->sdmPhone, $sdmMsg, null, 'urgent');
        }

        return $res;
    }

    /**
     * Template 5: Approval Verifikasi Berkas SIP Baru
     */
    public function sendVerificationApproved(SipRecord $sipRecord): array
    {
        $dokter = $sipRecord->dokter;
        $tglExp = $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y');

        $message = "🟢 *VERIFIKASI SIP BERHASIL DISETUJUI*\n"
            . "🏥 *RSUD Dr. H. Moch. Ansari Saleh Banjarmasin*\n\n"
            . "Yth. *{$dokter->nama_lengkap}*,\n\n"
            . "Pengajuan perpanjangan/pembaruan SIP Anda telah selesai diperiksa dan *DISETUJUI* oleh Tim SDM Kepegawaian:\n\n"
            . "• No. SIP: *{$sipRecord->no_sip}*\n"
            . "• Praktik Ke-: {$sipRecord->tempat_praktik_ke}\n"
            . "• Masa Berlaku Baru s/d: *{$tglExp}*\n"
            . "• Status Legalitas: 🟢 *Aman / Terverifikasi*\n\n"
            . "Terima kasih atas kepatuhan Anda terhadap standar legalitas staf medis RSUD Dr. H. Moch. Ansari Saleh.\n\n"
            . "_SIMONSIP Kepegawaian RSUD Ansari Saleh_";

        return $this->sendMessage($dokter->no_whatsapp, $message, $sipRecord, 'verifikasi_approved');
    }

    /**
     * Template 6: Rejection Verifikasi Berkas SIP Baru
     */
    public function sendVerificationRejected(SipRecord $sipRecord, string $catatan): array
    {
        $dokter = $sipRecord->dokter;
        $renewalUrl = url('/dokter/renewal');

        $message = "⚠️ *PERBAIKAN BERKAS PENGAJUAN SIP DITOLAK*\n"
            . "🏥 *RSUD Dr. H. Moch. Ansari Saleh Banjarmasin*\n\n"
            . "Yth. *{$dokter->nama_lengkap}*,\n\n"
            . "Pengajuan perpanjangan SIP Anda *BELUM DAPAT DISETUJUI* oleh Admin SDM dengan rincian berkas:\n"
            . "• No. SIP Pengajuan: *{$sipRecord->no_sip}*\n"
            . "• Praktik Ke-: {$sipRecord->tempat_praktik_ke}\n\n"
            . "📝 *Catatan / Alasan Penolakan:*\n"
            . "_{$catatan}_\n\n"
            . "Harap perbaiki atau unggah ulang dokumen yang sesuai melalui tautan portal berikut:\n"
            . "👉 {$renewalUrl}\n\n"
            . "_Subbagian SDM Kepegawaian RSUD Ansari Saleh_";

        return $this->sendMessage($dokter->no_whatsapp, $message, $sipRecord, 'verifikasi_rejected');
    }
}
