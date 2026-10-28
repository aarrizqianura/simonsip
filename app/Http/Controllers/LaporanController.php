<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $jenis = $request->query('jenis', 'kepatuhan'); // kepatuhan, matriks_ksm, log_wa, audit_verifikasi
        $ksmId = $request->query('ksm_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $data = $this->getLaporanData($jenis, $ksmId, $status, $startDate, $endDate);
        $ksms = Ksm::orderBy('nama_ksm')->get();

        return view('laporan.index', array_merge($data, [
            'jenis' => $jenis,
            'ksmId' => $ksmId,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'ksms' => $ksms,
        ]));
    }

    public function exportPdf(Request $request): Response
    {
        $jenis = $request->query('jenis', 'kepatuhan');
        $ksmId = $request->query('ksm_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $data = $this->getLaporanData($jenis, $ksmId, $status, $startDate, $endDate);
        $data['jenis'] = $jenis;
        $data['selectedKsm'] = $ksmId ? Ksm::find($ksmId) : null;
        $data['tanggalCetak'] = Carbon::now()->translatedFormat('d F Y');
        $data['jamCetak'] = Carbon::now()->format('H:i') . ' WITA';

        $pdfTitle = match ($jenis) {
            'kepatuhan' => 'Laporan_Kepatuhan_SIP_Dokter_Ansari_Saleh',
            'matriks_ksm' => 'Laporan_Matriks_SIP_Per_KSM_Ansari_Saleh',
            'log_wa' => 'Laporan_Audit_Log_WhatsApp_SIMONSIP',
            'audit_verifikasi' => 'Laporan_Audit_Verifikasi_SIP_SDM',
            default => 'Laporan_SIMONSIP',
        };

        $pdf = Pdf::loadView('laporan.pdf', $data);
        $pdf->setPaper('a4', $jenis === 'kepatuhan' ? 'portrait' : 'landscape');

        return $pdf->download("{$pdfTitle}_" . date('Ymd_His') . ".pdf");
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $jenis = $request->query('jenis', 'kepatuhan');
        $ksmId = $request->query('ksm_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $data = $this->getLaporanData($jenis, $ksmId, $status, $startDate, $endDate);
        $fileName = "laporan_{$jenis}_ansari_saleh_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($jenis, $data) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            if ($jenis === 'matriks_ksm' || $jenis === 'kepatuhan') {
                fputcsv($handle, [
                    'No',
                    'Nama Dokter & Gelar',
                    'NIP / NIK',
                    'KSM',
                    'Spesialisasi',
                    'No. STR',
                    'Status STR',
                    'No. SIP',
                    'Praktik Ke-',
                    'Tgl. Terbit',
                    'Tgl. Kadaluarsa SIP',
                    'Sisa Hari',
                    'Status Kepatuhan',
                ], ';');

                $records = $data['records'] ?? [];
                $no = 1;
                foreach ($records as $r) {
                    $d = $r->dokter;
                    fputcsv($handle, [
                        $no++,
                        $d->nama_lengkap,
                        $d->nip_nik,
                        $d->ksm?->nama_ksm ?? '-',
                        $d->spesialisasi?->nama_spesialisasi ?? '-',
                        $d->no_str,
                        $d->is_str_seumur_hidup ? 'Seumur Hidup' : ($d->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-'),
                        $r->no_sip,
                        $r->tempat_praktik_ke,
                        $r->tgl_terbit?->format('d/m/Y') ?? '-',
                        $r->tgl_kadaluarsa?->format('d/m/Y') ?? '-',
                        $r->sisa_hari . ' Hari',
                        $r->status_label,
                    ], ';');
                }
            } elseif ($jenis === 'log_wa') {
                fputcsv($handle, [
                    'No',
                    'Waktu Pengiriman (WITA)',
                    'Nama Dokter',
                    'Nomor WhatsApp Tujuan',
                    'Jenis Pesan',
                    'Status Pengiriman',
                    'Isi Ringkasan Pesan',
                ], ';');

                $logs = $data['logs'] ?? [];
                $no = 1;
                foreach ($logs as $l) {
                    fputcsv($handle, [
                        $no++,
                        $l->created_at?->format('d/m/Y H:i:s') ?? '-',
                        $l->sipRecord?->dokter?->nama_lengkap ?? '-',
                        $l->recipient_phone,
                        $l->message_type_label,
                        strtoupper($l->status),
                        str_replace(["\r", "\n"], ' ', substr($l->payload, 0, 120)) . '...',
                    ], ';');
                }
            } elseif ($jenis === 'audit_verifikasi') {
                fputcsv($handle, [
                    'No',
                    'Nama Dokter',
                    'KSM',
                    'No. SIP Diajukan',
                    'Praktik Ke-',
                    'Tanggal Diajukan',
                    'Tanggal Diputuskan',
                    'Status Approval',
                    'Admin SDM Verifikator',
                    'Catatan Verifikasi / Alasan Penolakan',
                ], ';');

                $records = $data['verifikasiRecords'] ?? [];
                $no = 1;
                foreach ($records as $vr) {
                    fputcsv($handle, [
                        $no++,
                        $vr->dokter?->nama_lengkap ?? '-',
                        $vr->dokter?->ksm?->nama_ksm ?? '-',
                        $vr->no_sip,
                        $vr->tempat_praktik_ke,
                        $vr->created_at?->format('d/m/Y H:i') ?? '-',
                        $vr->verified_at?->format('d/m/Y H:i') ?? '-',
                        $vr->is_verified ? 'DISETUJUI' : 'DITOLAK / MENUNGGU',
                        $vr->verifiedBy?->name ?? '-',
                        $vr->catatan_verifikasi ?? '-',
                    ], ';');
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Helper to retrieve datasets based on type
     */
    protected function getLaporanData(string $jenis, ?string $ksmId, ?string $status, ?string $startDate, ?string $endDate): array
    {
        $today = Carbon::today();

        if ($jenis === 'kepatuhan' || $jenis === 'matriks_ksm') {
            $query = SipRecord::with(['dokter.ksm', 'dokter.spesialisasi'])
                ->orderBy('dokter_id')
                ->orderBy('tempat_praktik_ke');

            if ($ksmId) {
                $query->whereHas('dokter', fn($q) => $q->where('ksm_id', $ksmId));
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('tgl_kadaluarsa', [$startDate, $endDate]);
            }

            $records = $query->get();

            // Summary stats for compliance
            $totalSip = $records->count();
            $countAman = $records->where('status', 'aktif')->count();
            $countWarning = $records->where('status', 'warning')->count();
            $countMendesak = $records->where('status', 'mendesak')->count();
            $countExpired = $records->where('status', 'expired')->count();
            $countMenunggu = $records->where('status', 'menunggu_verifikasi')->count();

            $denominator = max(1, $countAman + $countWarning + $countMendesak + $countExpired);
            $complianceRate = round(($countAman / $denominator) * 100, 1);

            // Group by KSM for Matrix
            $recordsByKsm = $records->groupBy(fn($r) => $r->dokter->ksm?->nama_ksm ?? 'Tanpa KSM');

            return compact(
                'records',
                'recordsByKsm',
                'totalSip',
                'countAman',
                'countWarning',
                'countMendesak',
                'countExpired',
                'countMenunggu',
                'complianceRate'
            );
        }

        if ($jenis === 'log_wa') {
            $query = NotificationLog::with(['sipRecord.dokter.ksm'])->latest();

            if ($status) {
                $query->where('status', $status);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            $logs = $query->get();

            $statsLog = [
                'total' => $logs->count(),
                'sent' => $logs->where('status', 'sent')->count(),
                'failed' => $logs->where('status', 'failed')->count(),
                'pending' => $logs->where('status', 'pending')->count(),
            ];

            return compact('logs', 'statsLog');
        }

        if ($jenis === 'audit_verifikasi') {
            $query = SipRecord::with(['dokter.ksm', 'verifiedBy'])
                ->where(function ($q) {
                    $q->whereNotNull('verified_at')
                      ->orWhere('status', 'menunggu_verifikasi');
                })
                ->orderByDesc('updated_at');

            if ($ksmId) {
                $query->whereHas('dokter', fn($q) => $q->where('ksm_id', $ksmId));
            }

            if ($startDate && $endDate) {
                $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            $verifikasiRecords = $query->get();

            return compact('verifikasiRecords');
        }

        return [];
    }
}
