<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DokterPortalController extends Controller
{
    /**
     * Get currently logged-in doctor profile
     */
    protected function getDokter(): Dokter
    {
        $user = Auth::user();
        $dokter = $user->dokter;

        if (!$dokter) {
            abort(403, 'Profil Dokter belum terhubung dengan akun pengguna Anda.');
        }

        return $dokter;
    }

    public function dashboard(): View
    {
        $dokter = $this->getDokter();
        $dokter->load(['ksm', 'spesialisasi', 'sipRecords']);

        $primarySip = $dokter->sipRecords->where('tempat_praktik_ke', 1)->first() ?? $dokter->sipRecords->first();
        $allSips = $dokter->sipRecords->sortBy('tempat_praktik_ke');

        $pendingSip = $dokter->sipRecords->where('status', 'menunggu_verifikasi')->first();
        $rejectedSip = $dokter->sipRecords
            ->where('is_verified', false)
            ->whereNotNull('verified_at')
            ->where('status', '!=', 'menunggu_verifikasi')
            ->sortByDesc('verified_at')
            ->first();

        // Calculate days remaining
        $sisaHari = $primarySip ? $primarySip->sisa_hari : null;

        return view('dokter.dashboard', compact('dokter', 'primarySip', 'allSips', 'pendingSip', 'rejectedSip', 'sisaHari'));
    }

    public function renewal(): View
    {
        $dokter = $this->getDokter();
        $dokter->load(['ksm', 'spesialisasi', 'sipRecords']);

        $existingSips = $dokter->sipRecords->keyBy('tempat_praktik_ke');

        return view('dokter.renewal', compact('dokter', 'existingSips'));
    }

    public function submitRenewal(Request $request, WhatsAppService $waService): RedirectResponse
    {
        $dokter = $this->getDokter();

        $validated = $request->validate([
            'no_sip' => ['required', 'string', 'max:100'],
            'tempat_praktik_ke' => ['required', 'integer', 'between:1,3'],
            'tgl_terbit' => ['required', 'date'],
            'tgl_kadaluarsa' => ['required', 'date', 'after:tgl_terbit'],
            'file_sip' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'no_sip.required' => 'Nomor SIP Baru wajib diisi.',
            'tgl_kadaluarsa.after' => 'Tanggal kadaluarsa harus sesudah tanggal terbit.',
            'file_sip.required' => 'Dokumen berkas PDF SIP wajib diunggah.',
            'file_sip.max' => 'Ukuran file berkas maksimal 10 MB.',
        ]);

        $file = $request->file('file_sip');
        $filename = 'renewal_' . $dokter->nip_nik . '_p' . $validated['tempat_praktik_ke'] . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('sip_documents', $filename, 'public');

        // Simpan / buat record pengajuan SIP baru dengan status menunggu_verifikasi
        $sipRecord = SipRecord::create([
            'dokter_id' => $dokter->id,
            'no_sip' => $validated['no_sip'],
            'tempat_praktik_ke' => $validated['tempat_praktik_ke'],
            'tgl_terbit' => $validated['tgl_terbit'],
            'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
            'file_sip_path' => $filePath,
            'status' => 'menunggu_verifikasi',
            'is_verified' => false,
            'catatan_verifikasi' => 'Pengajuan perpanjangan SIP mandiri oleh dokter pada ' . Carbon::now()->translatedFormat('d F Y H:i'),
        ]);

        // Kirim alert ke Admin SDM via WhatsApp bahwa ada pengajuan perpanjangan baru
        $sdmPhone = env('WA_SDM_PHONE', '081234567890');
        if ($sdmPhone) {
            $msgSdm = "📥 *PENGAJUAN PERPANJANGAN SIP BARU*\n"
                . "🏥 RSUD Dr. H. Moch. Ansari Saleh\n\n"
                . "Dokter: *{$dokter->nama_lengkap}*\n"
                . "NIP/NIK: {$dokter->nip_nik}\n"
                . "KSM: {$dokter->ksm?->nama_ksm}\n"
                . "Praktik Ke-: {$sipRecord->tempat_praktik_ke}\n"
                . "No. SIP Diajukan: *{$sipRecord->no_sip}*\n"
                . "Kadaluarsa Baru: " . $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y') . "\n\n"
                . "Silakan verifikasi berkas pada menu Approval Verifikasi SIP SIMONSIP:\n"
                . url('/verifikasi-sip');
            $waService->sendMessage($sdmPhone, $msgSdm, $sipRecord, 'early_warning');
        }

        return redirect()->route('dokter.riwayat')
            ->with('success', "Pengajuan perpanjangan SIP No. {$sipRecord->no_sip} berhasil dikirim! Status berkas Anda kini Menunggu Verifikasi oleh Admin SDM.");
    }

    public function riwayat(): View
    {
        $dokter = $this->getDokter();
        $sipRecords = $dokter->sipRecords()->with(['verifiedBy', 'notificationLogs'])->latest()->paginate(10);

        return view('dokter.riwayat', compact('dokter', 'sipRecords'));
    }

    public function profile(): View
    {
        $dokter = $this->getDokter();
        $dokter->load(['ksm', 'spesialisasi', 'user']);

        return view('dokter.profile', compact('dokter'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password_baru.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}
