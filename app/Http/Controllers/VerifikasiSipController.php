<?php

namespace App\Http\Controllers;

use App\Models\SipRecord;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class VerifikasiSipController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'menunggu_verifikasi');

        $query = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'verifiedBy', 'notificationLogs']);

        if ($status === 'menunggu_verifikasi') {
            $query->where('status', 'menunggu_verifikasi');
        } elseif ($status === 'riwayat') {
            $query->whereNotNull('verified_at');
        }

        $sipRecords = $query->orderByDesc('updated_at')->paginate(10)->withQueryString();
        $pendingCount = SipRecord::where('status', 'menunggu_verifikasi')->count();
        $approvedCount = SipRecord::whereNotNull('verified_at')->where('is_verified', true)->count();
        $rejectedCount = SipRecord::whereNotNull('verified_at')->where('is_verified', false)->where('status', '!=', 'menunggu_verifikasi')->count();

        return view('verifikasi.index', compact(
            'sipRecords',
            'status',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Stream PDF or Image inline for the PDF Preview Modal
     */
    public function previewPdf(SipRecord $sipRecord): Response
    {
        $sipRecord->load(['dokter.ksm', 'dokter.spesialisasi', 'verifiedBy']);

        $filePath = $sipRecord->file_sip_path;
        if ($filePath && Storage::disk('public')->exists($filePath) && Storage::disk('public')->size($filePath) > 500) {
            return Storage::disk('public')->response($filePath);
        }

        $pdf = Pdf::loadView('verifikasi.sip_pdf_template', compact('sipRecord'))
            ->setPaper('a4', 'portrait');

        $filename = 'SIP_' . $sipRecord->dokter->nip_nik . '_P' . $sipRecord->tempat_praktik_ke . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Force download of the SIP PDF document
     */
    public function downloadPdf(SipRecord $sipRecord): Response
    {
        $sipRecord->load(['dokter.ksm', 'dokter.spesialisasi', 'verifiedBy']);

        $safeDoctorName = preg_replace('/[^A-Za-z0-9_-]/', '_', $sipRecord->dokter->nama_lengkap);
        $filePath = $sipRecord->file_sip_path;

        if ($filePath && Storage::disk('public')->exists($filePath) && Storage::disk('public')->size($filePath) > 500) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'pdf';
            $downloadName = "SIP_{$safeDoctorName}_Praktik{$sipRecord->tempat_praktik_ke}.{$ext}";
            return Storage::disk('public')->download($filePath, $downloadName);
        }

        $pdf = Pdf::loadView('verifikasi.sip_pdf_template', compact('sipRecord'))
            ->setPaper('a4', 'portrait');

        $downloadName = "SIP_{$safeDoctorName}_Praktik{$sipRecord->tempat_praktik_ke}.pdf";

        return $pdf->download($downloadName);
    }

    public function approve(Request $request, SipRecord $sipRecord, WhatsAppService $waService): RedirectResponse
    {
        $catatan = trim((string) $request->input('catatan', ''));
        if ($catatan === '') {
            $catatan = 'Berkas SIP telah diperiksa dan dinyatakan sah serta sesuai standar regulasi.';
        }

        $sipRecord->is_verified = true;
        $sipRecord->verified_by = auth()->id();
        $sipRecord->verified_at = Carbon::now();
        $sipRecord->catatan_verifikasi = $catatan;
        $sipRecord->status = 'aktif';
        $sipRecord->save();

        // Hitung ulang status sesuai masa berlaku (biasanya 🟢 Aman jika baru diperpanjang 5 tahun)
        $sipRecord->status = $sipRecord->determineStatus();
        $sipRecord->save();

        // Kirim WhatsApp notifikasi bahwa verifikasi disetujui
        $waResult = null;
        if ($sipRecord->dokter && $sipRecord->dokter->no_whatsapp) {
            $waResult = $waService->sendVerificationApproved($sipRecord);
        }

        return redirect()->route('verifikasi.index')
            ->with('success', "Pengajuan SIP No. {$sipRecord->no_sip} milik {$sipRecord->dokter->nama_lengkap} BERHASIL DISETUJUI dan telah diaktifkan.")
            ->with('wa_notification', $waResult ? [
                'type' => 'approved',
                'doctor_name' => $sipRecord->dokter->nama_lengkap,
                'no_sip' => $sipRecord->no_sip,
                'phone' => $waResult['phone'],
                'status' => $waResult['status'],
                'message' => $waResult['message'],
                'wa_me_url' => $waResult['wa_me_url'],
            ] : null);
    }

    public function reject(Request $request, SipRecord $sipRecord, WhatsAppService $waService): RedirectResponse
    {
        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5'],
        ], [
            'alasan_penolakan.required' => 'Wajib menuliskan alasan penolakan atau catatan perbaikan berkas untuk dokter.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal terdiri dari 5 karakter agar jelas bagi dokter.',
        ]);

        $catatan = trim((string) $request->input('alasan_penolakan'));

        // Tandai berkas ditolak oleh verifikator SDM
        $sipRecord->is_verified = false;
        $sipRecord->verified_by = auth()->id();
        $sipRecord->verified_at = Carbon::now();
        $sipRecord->catatan_verifikasi = $catatan;
        $sipRecord->status = 'expired';
        $sipRecord->save();

        // Kirim notifikasi WA penolakan beserta alasan secara otomatis melalui WA Gateway
        $waResult = null;
        if ($sipRecord->dokter && $sipRecord->dokter->no_whatsapp) {
            $waResult = $waService->sendVerificationRejected($sipRecord, $catatan);
        }

        return redirect()->route('verifikasi.index')
            ->with('warning', "Pengajuan SIP No. {$sipRecord->no_sip} milik {$sipRecord->dokter->nama_lengkap} telah DITOLAK. Catatan perbaikan berkas telah dikirimkan ke WhatsApp dokter.")
            ->with('wa_notification', $waResult ? [
                'type' => 'rejected',
                'doctor_name' => $sipRecord->dokter->nama_lengkap,
                'no_sip' => $sipRecord->no_sip,
                'alasan' => $catatan,
                'phone' => $waResult['phone'],
                'status' => $waResult['status'],
                'message' => $waResult['message'],
                'wa_me_url' => $waResult['wa_me_url'],
            ] : null);
    }
}
