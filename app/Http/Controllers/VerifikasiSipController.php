<?php

namespace App\Http\Controllers;

use App\Models\SipRecord;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiSipController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'menunggu_verifikasi');

        $query = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi', 'verifiedBy']);

        if ($status === 'menunggu_verifikasi') {
            $query->where('status', 'menunggu_verifikasi');
        } elseif ($status === 'riwayat') {
            $query->whereNotNull('verified_at');
        }

        $sipRecords = $query->orderByDesc('updated_at')->paginate(10)->withQueryString();
        $pendingCount = SipRecord::where('status', 'menunggu_verifikasi')->count();

        return view('verifikasi.index', compact('sipRecords', 'status', 'pendingCount'));
    }

    public function approve(Request $request, SipRecord $sipRecord, WhatsAppService $waService): RedirectResponse
    {
        $sipRecord->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => Carbon::now(),
            'catatan_verifikasi' => $request->input('catatan', 'Berkas SIP telah diperiksa dan dinyatakan sah serta sesuai standar regulasi.'),
        ]);

        // Hitung ulang status (biasanya 🟢 Aman jika baru diperpanjang 5 tahun)
        $sipRecord->status = $sipRecord->determineStatus();
        $sipRecord->save();

        // Kirim WhatsApp notifikasi bahwa verifikasi disetujui
        if ($sipRecord->dokter && $sipRecord->dokter->no_whatsapp) {
            $waService->sendVerificationApproved($sipRecord);
        }

        return redirect()->route('verifikasi.index')->with('success', "Pengajuan SIP No. {$sipRecord->no_sip} milik {$sipRecord->dokter->nama_lengkap} BERHASIL DISETUJUI.");
    }

    public function reject(Request $request, SipRecord $sipRecord, WhatsAppService $waService): RedirectResponse
    {
        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5'],
        ], [
            'alasan_penolakan.required' => 'Wajib memberikan alasan atau catatan perbaikan berkas untuk dokter.',
        ]);

        $catatan = $request->input('alasan_penolakan');

        // Status kembali dihitung berdasarkan sisa hari dari SIP yang ada
        $sipRecord->is_verified = false;
        $sipRecord->verified_by = auth()->id();
        $sipRecord->verified_at = Carbon::now();
        $sipRecord->catatan_verifikasi = $catatan;

        // Reset status to calculated days
        $days = $sipRecord->sisa_hari;
        if ($days > 90) {
            $sipRecord->status = 'aktif';
        } elseif ($days >= 61) {
            $sipRecord->status = 'warning';
        } elseif ($days >= 31) {
            $sipRecord->status = 'mendesak';
        } else {
            $sipRecord->status = 'expired';
        }

        $sipRecord->save();

        // Kirim notifikasi WA penolakan beserta alasan
        if ($sipRecord->dokter && $sipRecord->dokter->no_whatsapp) {
            $waService->sendVerificationRejected($sipRecord, $catatan);
        }

        return redirect()->route('verifikasi.index')->with('warning', "Pengajuan SIP No. {$sipRecord->no_sip} telah DITOLAK. Pesan revisi telah dikirimkan ke WhatsApp dokter.");
    }
}
