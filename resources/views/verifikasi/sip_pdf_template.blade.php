<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Praktik (SIP) - {{ $sipRecord->no_sip }}</title>
    <style>
        @page {
            margin: 35px 45px;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #1e293b;
            line-height: 1.45;
            font-size: 12pt;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            margin: 3px 0;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            color: #475569;
        }
        .doc-title {
            text-align: center;
            margin: 18px 0 22px 0;
        }
        .doc-title h1 {
            margin: 0;
            font-size: 15pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title .nomor {
            margin-top: 5px;
            font-size: 12pt;
            font-weight: bold;
        }
        .content-para {
            text-align: justify;
            margin-bottom: 14px;
            font-size: 11.5pt;
        }
        .data-table {
            width: 100%;
            margin: 12px 0 18px 0;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 6px 4px;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .data-table td.label {
            width: 34%;
            font-weight: bold;
            color: #334155;
        }
        .data-table td.sep {
            width: 3%;
        }
        .data-table td.val {
            width: 63%;
            font-weight: bold;
            color: #0f172a;
        }
        .validity-box {
            border: 1.5px solid #059669;
            background-color: #f0fdf4;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 18px 0;
            font-size: 11pt;
        }
        .footer-sig {
            width: 100%;
            margin-top: 30px;
        }
        .watermark {
            position: fixed;
            top: 38%;
            left: 12%;
            transform: rotate(-30deg);
            font-size: 38pt;
            font-weight: bold;
            color: rgba(16, 185, 129, 0.09);
            border: 4px solid rgba(16, 185, 129, 0.09);
            padding: 10px 25px;
            text-transform: uppercase;
            z-index: -1;
        }
        .qr-placeholder {
            border: 1px solid #cbd5e1;
            padding: 8px;
            display: inline-block;
            font-size: 8pt;
            text-align: center;
            color: #047857;
            background: #f8fafc;
            margin-top: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="watermark">DOKUMEN SIP RESMI</div>

    <table class="header-table">
        <tr>
            <td class="header-title">
                <h3>PEMERINTAH KOTA BANJARMASIN</h3>
                <h2>DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU</h2>
                <p>Jl. Sultan Adam No. 49, Surgi Mufti, Kec. Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70122</p>
            </td>
        </tr>
    </table>

    <div class="doc-title">
        <h1>SURAT IZIN PRAKTIK (SIP) DOKTER / DOKTER SPESIALIS</h1>
        <div class="nomor">Nomor: {{ $sipRecord->no_sip }}</div>
    </div>

    <p class="content-para">
        Berdasarkan Undang-Undang Republik Indonesia Nomor 17 Tahun 2023 tentang Kesehatan serta Peraturan Menteri Kesehatan Republik Indonesia tentang Izin dan Penyelenggaraan Praktik Kedokteran, yang bertanda tangan di bawah ini Kepala Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu memberikan <strong>Surat Izin Praktik (SIP)</strong> kepada:
    </p>

    <table class="data-table">
        <tr>
            <td class="label">Nama Lengkap Tenaga Medis</td>
            <td class="sep">:</td>
            <td class="val">{{ $sipRecord->dokter->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="label">NIP / NIK</td>
            <td class="sep">:</td>
            <td class="val">{{ $sipRecord->dokter->nip_nik }}</td>
        </tr>
        <tr>
            <td class="label">Kompetensi / Spesialisasi</td>
            <td class="sep">:</td>
            <td class="val">{{ $sipRecord->dokter->spesialisasi?->nama_spesialisasi ?? 'Dokter Spesialis' }}</td>
        </tr>
        <tr>
            <td class="label">Kelompok Staf Medis (KSM)</td>
            <td class="sep">:</td>
            <td class="val">{{ $sipRecord->dokter->ksm?->nama_ksm ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor STR</td>
            <td class="sep">:</td>
            <td class="val">
                {{ $sipRecord->dokter->no_str }}
                ({{ $sipRecord->dokter->is_str_seumur_hidup ? 'Berlaku Seumur Hidup' : 's/d ' . ($sipRecord->dokter->tgl_kadaluarsa_str?->translatedFormat('d F Y') ?? '-') }})
            </td>
        </tr>
        <tr>
            <td class="label">Tempat Praktik Ke-</td>
            <td class="sep">:</td>
            <td class="val">
                Praktik Ke-{{ $sipRecord->tempat_praktik_ke }} — RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
            </td>
        </tr>
        <tr>
            <td class="label">Alamat Tempat Praktik</td>
            <td class="sep">:</td>
            <td class="val">Jl. Brig Jend. Hasan Basri No.1, Banjarmasin Utara, Kota Banjarmasin</td>
        </tr>
    </table>

    <div class="validity-box">
        <strong>MASA BERLAKU SURAT IZIN PRAKTIK:</strong><br>
        Diterbitkan pada tanggal <strong>{{ $sipRecord->tgl_terbit->translatedFormat('d F Y') }}</strong> dan berlaku sampai dengan tanggal <strong>{{ $sipRecord->tgl_kadaluarsa->translatedFormat('d F Y') }}</strong>.
    </div>

    <p class="content-para">
        Dengan ketentuan bahwa yang bersangkutan wajib menaati seluruh peraturan perundang-undangan yang berlaku di bidang pelayanan kesehatan serta menjaga standar profesi kedokteran di lingkungan <strong>RSUD Dr. H. Moch. Ansari Saleh Provinsi Kalimantan Selatan</strong>.
    </p>

    <table class="footer-sig">
        <tr>
            <td style="width: 52%; vertical-align: bottom; font-size: 9.5pt; color: #475569;">
                <strong>Arsip Digital SIMONSIP RSUD Ansari Saleh:</strong><br>
                Status Berkas: {{ $sipRecord->status_label }}<br>
                @if($sipRecord->is_verified && $sipRecord->verifiedBy)
                    Diverifikasi oleh: {{ $sipRecord->verifiedBy->name }}<br>
                    Waktu Verifikasi: {{ $sipRecord->verified_at?->translatedFormat('d F Y H:i') }} WITA
                @else
                    Diajukan pada: {{ $sipRecord->created_at?->translatedFormat('d F Y H:i') }} WITA
                @endif
            </td>
            <td style="width: 48%; text-align: center;">
                <div>Ditetapkan di: Banjarmasin</div>
                <div>Pada tanggal: {{ $sipRecord->tgl_terbit->translatedFormat('d F Y') }}</div>
                <div style="margin-top: 6px; font-weight: bold;">KEPALA DPMPTSP KOTA BANJARMASIN</div>
                <div class="qr-placeholder">
                    [ TANDA TANGAN ELEKTRONIK BSrE - BSSN ]<br>
                    <strong>VALID &amp; TERDAFTAR</strong><br>
                    ID: {{ md5($sipRecord->no_sip) }}
                </div>
                <div style="font-weight: bold; text-decoration: underline;">Drs. H. M. YUSUF, M.AP</div>
                <div style="font-size: 10pt;">Pembina Utama Muda / NIP. 19700512 199603 1 004</div>
            </td>
        </tr>
    </table>
</body>
</html>

