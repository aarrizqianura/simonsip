<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class WhatsAppLogController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $type = $request->query('message_type');
        $search = $request->query('search');

        $query = NotificationLog::with(['sipRecord.dokter.ksm']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('message_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%")
                    ->orWhereHas('sipRecord.dokter', fn($dq) => $dq->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => NotificationLog::count(),
            'sent' => NotificationLog::where('status', 'sent')->count(),
            'failed' => NotificationLog::where('status', 'failed')->count(),
            'pending' => NotificationLog::where('status', 'pending')->count(),
            'today' => NotificationLog::whereDate('created_at', Carbon::today())->count(),
        ];

        $allDokters = Dokter::with('primarySip')->orderBy('nama_lengkap')->get();

        return view('whatsapp.index', compact('logs', 'stats', 'status', 'type', 'search', 'allDokters'));
    }

    /**
     * Manual Instant Blast / Test WhatsApp Message
     */
    public function sendManual(Request $request, WhatsAppService $waService): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string', 'min:5'],
            'sip_record_id' => ['nullable', 'exists:sip_records,id'],
        ]);

        $sipRecord = !empty($validated['sip_record_id']) ? SipRecord::find($validated['sip_record_id']) : null;
        $result = $waService->sendMessage($validated['phone'], $validated['message'], $sipRecord, 'early_warning');

        if ($result['status'] === 'sent') {
            return back()->with('success', "Pesan WhatsApp berhasil dikirimkan ke {$result['phone']}.");
        }

        return back()->with('error', "Gagal mengirimkan pesan WhatsApp ke {$result['phone']}. Periksa log atau konfigurasi gateway.");
    }

    /**
     * Trigger Early Warning System Cron Job Manually
     */
    public function triggerCron(): RedirectResponse
    {
        Artisan::call('sip:check-expiry', ['--send-wa' => true]);
        $output = Artisan::output();

        return back()->with('success', "Early Warning Engine berhasil dijalankan. Output: " . trim($output));
    }

    /**
     * Resend failed or existing log message
     */
    public function resend(NotificationLog $log, WhatsAppService $waService): RedirectResponse
    {
        $result = $waService->sendMessage($log->recipient_phone, $log->payload, $log->sipRecord, $log->message_type);

        if ($result['status'] === 'sent') {
            $log->update(['status' => 'sent', 'sent_at' => Carbon::now()]);
            return back()->with('success', "Pesan berhasil dikirim ulang ke {$log->recipient_phone}.");
        }

        return back()->with('error', "Gagal mengirim ulang pesan ke {$log->recipient_phone}.");
    }
}
