<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $ksmFilter = $request->query('ksm_id');

        $dokterQuery = Dokter::with(['ksm', 'spesialisasi', 'sipRecords']);
        if ($ksmFilter) {
            $dokterQuery->where('ksm_id', $ksmFilter);
        }

        $totalDokter = $dokterQuery->count();

        // SIP counts based on primary SIP or all SIPs
        $sipQuery = SipRecord::query();
        if ($ksmFilter) {
            $sipQuery->whereHas('dokter', fn($q) => $q->where('ksm_id', $ksmFilter));
        }

        $countAman = (clone $sipQuery)->where('status', 'aktif')->count();
        $countWarning = (clone $sipQuery)->where('status', 'warning')->count();
        $countMendesak = (clone $sipQuery)->where('status', 'mendesak')->count();
        $countExpired = (clone $sipQuery)->where('status', 'expired')->count();
        $countMenunggu = (clone $sipQuery)->where('status', 'menunggu_verifikasi')->count();
        $totalSip = $countAman + $countWarning + $countMendesak + $countExpired + $countMenunggu;

        // Compliance rate: Aman / (Total - Menunggu Verifikasi) * 100
        $activeDenominator = max(1, $countAman + $countWarning + $countMendesak + $countExpired);
        $complianceRate = round(($countAman / $activeDenominator) * 100, 1);

        // KSM Breakdown
        $ksms = Ksm::withCount(['dokters'])->get()->map(function ($ksm) {
            $sips = SipRecord::whereHas('dokter', fn($q) => $q->where('ksm_id', $ksm->id))->get();
            $ksm->total_sip = $sips->count();
            $ksm->aman = $sips->where('status', 'aktif')->count();
            $ksm->warning = $sips->where('status', 'warning')->count();
            $ksm->mendesak = $sips->where('status', 'mendesak')->count();
            $ksm->expired = $sips->where('status', 'expired')->count();
            $ksm->menunggu = $sips->where('status', 'menunggu_verifikasi')->count();
            return $ksm;
        });

        // Urgent Action Needed (Kadaluarsa, Mendesak, Menunggu Verifikasi)
        $urgentSips = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi'])
            ->whereIn('status', ['expired', 'mendesak', 'menunggu_verifikasi', 'warning'])
            ->orderByRaw("FIELD(status, 'menunggu_verifikasi', 'expired', 'mendesak', 'warning')")
            ->orderBy('tgl_kadaluarsa')
            ->limit(10)
            ->get();

        // Recent Notifications
        $recentNotifications = NotificationLog::with('sipRecord.dokter')
            ->latest()
            ->limit(6)
            ->get();

        $allKsms = Ksm::orderBy('nama_ksm')->get();

        return view('dashboard.index', compact(
            'totalDokter',
            'totalSip',
            'countAman',
            'countWarning',
            'countMendesak',
            'countExpired',
            'countMenunggu',
            'complianceRate',
            'ksms',
            'urgentSips',
            'recentNotifications',
            'allKsms',
            'ksmFilter'
        ));
    }
}
