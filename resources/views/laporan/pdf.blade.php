<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan SIMONSIP RSUD Dr. H. Moch. Ansari Saleh</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 2cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-kop {
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header-kop .instansi-1 {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #334155;
        }
        .header-kop .instansi-2 {
            font-size: 14pt;
            font-weight: 900;
            text-transform: uppercase;
            color: #047857;
            margin: 2px 0;
        }
        .header-kop .alamat {
            font-size: 8pt;
            color: #64748b;
        }
        .report-title {
            text-align: center;
            margin-bottom: 14px;
        }
        .report-title h2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            color: #0f172a;
        }
        .report-title .meta {
            font-size: 8pt;
            color: #64748b;
        }
        .summary-box {
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            background-color: #f8fafc;
            border-radius: 4px;
            font-size: 8pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8pt;
        }
        table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        table tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }
        .badge-aman { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-mendesak { background-color: #ffedd5; color: #9a3412; }
        .badge-expired { background-color: #ffe4e6; color: #9f1239; }
        .badge-menunggu { background-color: #e0f2fe; color: #075985; }

        .signature-section {
            margin-top: 30px;
            width: 100%;
        }
        .sig-box {
            width: 45%;
            float: right;
            text-align: center;
            font-size: 8.5pt;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI RSUD DR. H. MOCH. ANSARI SALEH -->
    <div class="header-kop">
        <div class="instansi-1">Pemerintah Provinsi Kalimantan Selatan</div>
        <div class="instansi-2">RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</div>
        <div class="alamat">
            Jl. Brigjend H. Hasan Basry No. 1, Alalak Utara, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70125<br>
            Telepon: (0511) 3300759 &bull; Posel: rsudansarisaleh@kalselprov.go.id &bull; Laman: ansarisaleh.kalselprov.go.id
        </div>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="report-title">
        @if($jenis === 'kepatuhan')
            <h2>Laporan Kepatuhan Legalitas SIP Dokter (STARKES/KARS)</h2>
        @elseif($jenis === 'matriks_ksm')
            <h2>Laporan Matriks Masa Berlaku SIP Per Kelompok Staf Medis (KSM)</h2>
        @elseif($jenis === 'log_wa')
            <h2>Laporan Audit Trail Pengiriman Notifikasi WhatsApp Gateway</h2>
        @elseif($jenis === 'audit_verifikasi')
            <h2>Laporan Audit Verifikasi & Perpanjangan SIP Tenaga Medis</h2>
        @endif
        <div class="meta">
            Tanggal Cetak: {{ $tanggalCetak }} ({{ $jamCetak }}) &bull; SIMONSIP Version 1.0
            @if(!empty($selectedKsm)) &bull; KSM: {{ $selectedKsm->nama_ksm }} @endif
        </div>
    </div>

    @if($jenis === 'kepatuhan' || $jenis === 'matriks_ksm')
    <div class="summary-box">
        <strong>Ringkasan Kepatuhan:</strong>
        Total Record SIP: <strong>{{ $totalSip }}</strong> |
        🟢 Aman (>90 Hari): <strong>{{ $countAman }}</strong> |
        🟡 Peringatan (61-90 Hari): <strong>{{ $countWarning }}</strong> |
        🟠 Mendesak (31-60 Hari): <strong>{{ $countMendesak }}</strong> |
        🔴 Kadaluarsa (≤30 Hari): <strong>{{ $countExpired }}</strong> |
        Tingkat Kepatuhan: <strong>{{ $complianceRate }}%</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th>Nama Dokter & Gelar</th>
                <th>NIP / NIK</th>
                <th>KSM / Spesialisasi</th>
                <th>No. STR</th>
                <th>No. SIP</th>
                <th>Prkt</th>
                <th>Tgl Kadaluarsa</th>
                <th>Sisa Hari</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $idx => $r)
            @php
                $badgeClass = match($r->status) {
                    'aktif' => 'badge-aman',
                    'warning' => 'badge-warning',
                    'mendesak' => 'badge-mendesak',
                    'expired' => 'badge-expired',
                    default => 'badge-menunggu',
                };
            @endphp
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><strong>{{ $r->dokter->nama_lengkap }}</strong></td>
                <td>{{ $r->dokter->nip_nik }}</td>
                <td>
                    {{ $r->dokter->ksm?->nama_ksm ?? '-' }}<br>
                    <small style="color: #64748b;">{{ $r->dokter->spesialisasi?->nama_spesialisasi ?? '-' }}</small>
                </td>
                <td>
                    {{ $r->dokter->no_str }}<br>
                    <small>{{ $r->dokter->is_str_seumur_hidup ? 'Seumur Hidup' : ($r->dokter->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-') }}</small>
                </td>
                <td>{{ $r->no_sip }}</td>
                <td style="text-align: center;">{{ $r->tempat_praktik_ke }}</td>
                <td>{{ $r->tgl_kadaluarsa->format('d/m/Y') }}</td>
                <td style="font-weight: bold; color: {{ $r->sisa_hari <= 30 ? '#be123c' : '#1e293b' }};">
                    {{ $r->sisa_hari }} Hari
                </td>
                <td>
                    <span class="badge {{ $badgeClass }}">
                        {{ $r->status_label }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($jenis === 'log_wa')
    <table>
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th>Waktu (WITA)</th>
                <th>Nama Dokter</th>
                <th>No. Tujuan</th>
                <th>Jenis Notifikasi</th>
                <th>Isi Pesan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $idx => $l)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $l->created_at->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $l->sipRecord?->dokter?->nama_lengkap ?? '-' }}</strong></td>
                <td>{{ $l->recipient_phone }}</td>
                <td>{{ $l->message_type_label }}</td>
                <td><small>{{ substr($l->payload, 0, 100) }}...</small></td>
                <td style="font-weight: bold; color: {{ $l->status === 'sent' ? '#047857' : '#be123c' }};">
                    {{ strtoupper($l->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($jenis === 'audit_verifikasi')
    <table>
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th>Nama Dokter</th>
                <th>KSM</th>
                <th>No. SIP Diajukan</th>
                <th>Tgl Pengajuan</th>
                <th>Tgl Disetujui/Ditolak</th>
                <th>Status</th>
                <th>Petugas Verifikator</th>
                <th>Catatan / Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($verifikasiRecords as $idx => $vr)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><strong>{{ $vr->dokter->nama_lengkap }}</strong></td>
                <td>{{ $vr->dokter->ksm?->nama_ksm ?? '-' }}</td>
                <td>{{ $vr->no_sip }} (Praktik {{ $vr->tempat_praktik_ke }})</td>
                <td>{{ $vr->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $vr->verified_at ? $vr->verified_at->format('d/m/Y H:i') : '-' }}</td>
                <td><strong>{{ $vr->is_verified ? 'DISETUJUI' : 'REVISI/TOLAK' }}</strong></td>
                <td>{{ $vr->verifiedBy?->name ?? '-' }}</td>
                <td><small>{{ $vr->catatan_verifikasi ?? '-' }}</small></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- TANDA TANGAN PENGESAHAN -->
    <div class="signature-section">
        <div class="sig-box">
            Banjarmasin, {{ $tanggalCetak }}<br>
            <strong>Subbagian SDM & Kepegawaian</strong><br>
            RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
            <div style="height: 50px;"></div>
            <strong><u>Hj. Noor Laila, S.Kep., Ns</u></strong><br>
            NIP. 197808122005012009
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
