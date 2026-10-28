@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')
@section('page_title', 'Modul Pelaporan & Analytics Compliance SIP')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                <span>Laporan & Rekapitulasi SIMONSIP</span>
            </h2>
            <p class="text-xs text-slate-500">Rekapitulasi legalitas dokter untuk kebutuhan Akreditasi Rumah Sakit (STARKES/KARS), Komite Medik, dan Manajemen SDM</p>
        </div>

        <!-- Export Buttons -->
        <div class="flex items-center gap-2 flex-wrap no-print">
            <!-- Print Browser -->
            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print text-slate-600"></i>
                <span>Cetak Lembar</span>
            </button>

            <!-- Export PDF -->
            <a href="{{ route('laporan.pdf', request()->query()) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Unduh PDF Resmi (Kop RSUD)</span>
            </a>

            <!-- Export Excel -->
            <a href="{{ route('laporan.excel', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-excel"></i>
                <span>Ekspor Excel (CSV)</span>
            </a>
        </div>
    </div>

    <!-- 4 Report Tabs Selector -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 no-print">
        <a href="{{ route('laporan.index', ['jenis' => 'kepatuhan']) }}" 
           class="p-4 rounded-2xl border transition flex flex-col justify-between {{ $jenis === 'kepatuhan' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md border-emerald-600' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200' }}">
            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                <span>1. Kepatuhan STARKES</span>
                <i class="fa-solid fa-shield-halved {{ $jenis === 'kepatuhan' ? 'text-emerald-200' : 'text-emerald-600' }}"></i>
            </div>
            <div class="text-xs font-extrabold mt-3 leading-tight">
                Laporan Kepatuhan Legalitas SIP (Direksi)
            </div>
        </a>

        <a href="{{ route('laporan.index', ['jenis' => 'matriks_ksm']) }}" 
           class="p-4 rounded-2xl border transition flex flex-col justify-between {{ $jenis === 'matriks_ksm' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md border-emerald-600' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200' }}">
            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                <span>2. Matriks Per KSM</span>
                <i class="fa-solid fa-sitemap {{ $jenis === 'matriks_ksm' ? 'text-emerald-200' : 'text-indigo-600' }}"></i>
            </div>
            <div class="text-xs font-extrabold mt-3 leading-tight">
                Rekapitulasi SIP Berdasarkan KSM
            </div>
        </a>

        <a href="{{ route('laporan.index', ['jenis' => 'log_wa']) }}" 
           class="p-4 rounded-2xl border transition flex flex-col justify-between {{ $jenis === 'log_wa' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md border-emerald-600' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200' }}">
            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                <span>3. Audit Notifikasi</span>
                <i class="fa-brands fa-whatsapp {{ $jenis === 'log_wa' ? 'text-emerald-200' : 'text-green-600' }}"></i>
            </div>
            <div class="text-xs font-extrabold mt-3 leading-tight">
                Audit Trail Log WhatsApp Early Warning
            </div>
        </a>

        <a href="{{ route('laporan.index', ['jenis' => 'audit_verifikasi']) }}" 
           class="p-4 rounded-2xl border transition flex flex-col justify-between {{ $jenis === 'audit_verifikasi' ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md border-emerald-600' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200' }}">
            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                <span>4. Audit Verifikasi</span>
                <i class="fa-solid fa-file-signature {{ $jenis === 'audit_verifikasi' ? 'text-emerald-200' : 'text-sky-600' }}"></i>
            </div>
            <div class="text-xs font-extrabold mt-3 leading-tight">
                Riwayat Approval & Revisi SDM
            </div>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200 no-print">
        <form action="{{ route('laporan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <input type="hidden" name="jenis" value="{{ $jenis }}">

            @if($jenis === 'kepatuhan' || $jenis === 'matriks_ksm' || $jenis === 'audit_verifikasi')
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Filter KSM</label>
                <select name="ksm_id" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300">
                    <option value="">-- Semua KSM --</option>
                    @foreach($ksms as $k)
                        <option value="{{ $k->id }}" {{ $ksmId == $k->id ? 'selected' : '' }}>{{ $k->nama_ksm }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($jenis === 'kepatuhan' || $jenis === 'matriks_ksm' || $jenis === 'log_wa')
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Kepatuhan / Pesan</label>
                <select name="status" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300">
                    <option value="">-- Semua Status --</option>
                    @if($jenis === 'log_wa')
                        <option value="sent" {{ $status == 'sent' ? 'selected' : '' }}>Sent (Berhasil)</option>
                        <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                    @else
                        <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>🟢 Aman (> 90 Hari)</option>
                        <option value="warning" {{ $status == 'warning' ? 'selected' : '' }}>🟡 Peringatan (61–90 Hari)</option>
                        <option value="mendesak" {{ $status == 'mendesak' ? 'selected' : '' }}>🟠 Mendesak (31–60 Hari)</option>
                        <option value="expired" {{ $status == 'expired' ? 'selected' : '' }}>🔴 Kadaluarsa (≤ 30 Hari)</option>
                    @endif
                </select>
            </div>
            @endif

            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300">
            </div>

            <div class="sm:col-span-2 flex gap-1">
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                    Terapkan
                </button>
                @if($ksmId || $status || $startDate || $endDate)
                    <a href="{{ route('laporan.index', ['jenis' => $jenis]) }}" class="p-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Official Hospital Print Header (Visible on print) -->
    <div class="hidden print:block text-center border-b-2 border-slate-900 pb-3 mb-6">
        <div class="text-xs font-bold uppercase tracking-wider text-slate-700">Pemerintah Provinsi Kalimantan Selatan</div>
        <div class="text-lg font-black text-slate-900 uppercase">RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</div>
        <div class="text-[11px] text-slate-600">Jl. Brigjend H. Hasan Basry No. 1, Alalak Utara, Banjarmasin Utara, Kota Banjarmasin, Kalsel 70125 &bull; Telp. (0511) 3300759</div>
    </div>

    <!-- TAB 1: LAPORAN KEPATUHAN LEGALITAS SIP DOKTER -->
    @if($jenis === 'kepatuhan')
    <div class="space-y-6">
        <!-- Summary Analytics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase">Total Rekord SIP</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalSip }}</div>
                <div class="text-[11px] text-slate-500">Seluruh Tempat Praktik</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-200 bg-emerald-50/20">
                <div class="text-[11px] font-bold text-emerald-700 uppercase">🟢 Aman (> 90 Hari)</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ $countAman }}</div>
                <div class="text-[11px] text-emerald-600">{{ $complianceRate }}% Kepatuhan</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-amber-200 bg-amber-50/20">
                <div class="text-[11px] font-bold text-amber-700 uppercase">🟡 Peringatan (61-90)</div>
                <div class="text-2xl font-black text-amber-700 mt-1">{{ $countWarning }}</div>
                <div class="text-[11px] text-amber-600">Early Warning 1</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-orange-200 bg-orange-50/20">
                <div class="text-[11px] font-bold text-orange-700 uppercase">🟠 Mendesak (31-60)</div>
                <div class="text-2xl font-black text-orange-700 mt-1">{{ $countMendesak }}</div>
                <div class="text-[11px] text-orange-600">Early Warning 2</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-rose-200 bg-rose-50/20">
                <div class="text-[11px] font-bold text-rose-700 uppercase">🔴 Kadaluarsa (≤ 30)</div>
                <div class="text-2xl font-black text-rose-700 mt-1">{{ $countExpired }}</div>
                <div class="text-[11px] text-rose-600">Kritis / Expired</div>
            </div>
        </div>

        <!-- Table Kepatuhan -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Tabel Rincian Legalitas Seluruh Staf Medis Dokter</h3>
                <span class="text-xs text-slate-500">Standar Penilaian STARKES / KARS</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Nama Dokter & Gelar</th>
                            <th class="px-4 py-3.5">NIP / NIK</th>
                            <th class="px-4 py-3.5">KSM & Spesialisasi</th>
                            <th class="px-4 py-3.5">No. STR & Masa</th>
                            <th class="px-4 py-3.5">No. SIP</th>
                            <th class="px-4 py-3.5">Praktik</th>
                            <th class="px-4 py-3.5">Tgl Kadaluarsa</th>
                            <th class="px-4 py-3.5">Sisa Hari</th>
                            <th class="px-4 py-3.5 text-center">Status Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($records as $idx => $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 text-slate-400 font-semibold">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-900">{{ $r->dokter->nama_lengkap }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">{{ $r->dokter->nip_nik }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $r->dokter->ksm?->nama_ksm ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $r->dokter->spesialisasi?->nama_spesialisasi ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-mono text-slate-700">{{ $r->dokter->no_str }}</div>
                                <div class="text-[10px] text-teal-700 font-bold">{{ $r->dokter->str_status_text }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-semibold text-slate-800">{{ $r->no_sip }}</td>
                            <td class="px-4 py-3.5">Ke-{{ $r->tempat_praktik_ke }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $r->tgl_kadaluarsa->translatedFormat('d/m/Y') }}</td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold {{ $r->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $r->sisa_hari }} Hari
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $r->status_badge_class }}">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-slate-400">Tidak ada data sesuai kriteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 2: LAPORAN MATRIKS PER KSM -->
    @if($jenis === 'matriks_ksm')
    <div class="space-y-6">
        @foreach($recordsByKsm as $ksmName => $ksmRecords)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-sitemap text-emerald-400"></i>
                    <h3 class="text-sm font-bold">{{ $ksmName }}</h3>
                </div>
                <span class="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">
                    Total: {{ $ksmRecords->count() }} SIP
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Dokter & Gelar</th>
                            <th class="px-4 py-3">NIP / NIK</th>
                            <th class="px-4 py-3">Spesialisasi</th>
                            <th class="px-4 py-3">No. STR</th>
                            <th class="px-4 py-3">No. SIP</th>
                            <th class="px-4 py-3">Praktik</th>
                            <th class="px-4 py-3">Tgl Kadaluarsa</th>
                            <th class="px-4 py-3">Sisa Hari</th>
                            <th class="px-4 py-3 text-center">Status Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($ksmRecords as $i => $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $r->dokter->nama_lengkap }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $r->dokter->nip_nik }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->dokter->spesialisasi?->nama_spesialisasi ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $r->dokter->no_str }}</td>
                            <td class="px-4 py-3 font-mono font-semibold text-slate-800">{{ $r->no_sip }}</td>
                            <td class="px-4 py-3">Ke-{{ $r->tempat_praktik_ke }}</td>
                            <td class="px-4 py-3">{{ $r->tgl_kadaluarsa->translatedFormat('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-bold {{ $r->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-800' }}">{{ $r->sisa_hari }} Hari</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $r->status_badge_class }}">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- TAB 3: LAPORAN LOG NOTIFIKASI WA -->
    @if($jenis === 'log_wa')
    <div class="space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase">Total Log Pesan</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $statsLog['total'] }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-200 bg-emerald-50/20">
                <div class="text-[11px] font-bold text-emerald-700 uppercase">Terkirim (Sent)</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ $statsLog['sent'] }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-rose-200 bg-rose-50/20">
                <div class="text-[11px] font-bold text-rose-700 uppercase">Gagal (Failed)</div>
                <div class="text-2xl font-black text-rose-700 mt-1">{{ $statsLog['failed'] }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase">Gateway Channel</div>
                <div class="text-base font-extrabold text-emerald-700 mt-2">WhatsApp Fonnte API</div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Waktu Pengiriman</th>
                            <th class="px-4 py-3.5">Nama Dokter</th>
                            <th class="px-4 py-3.5">No. WhatsApp Tujuan</th>
                            <th class="px-4 py-3.5">Jenis Notifikasi</th>
                            <th class="px-4 py-3.5">Isi Ringkasan Pesan</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-700">
                                {{ $log->created_at->translatedFormat('d/m/Y H:i:s') }} WITA
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900">
                                {{ $log->sipRecord?->dokter?->nama_lengkap ?? 'SDM / Staf' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-emerald-700 font-semibold">{{ $log->recipient_phone }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $log->message_type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 max-w-md text-slate-600">
                                <div class="truncate">{{ $log->payload }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="font-bold {{ $log->status === 'sent' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Tidak ada log pesan WhatsApp yang tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 4: LAPORAN RIWAYAT VERIFIKASI & PERPANJANGAN SIP -->
    @if($jenis === 'audit_verifikasi')
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Jejak Audit Verifikasi & Perpanjangan SIP (Approval Audit Log)</h3>
            <p class="text-xs text-slate-500">Mencatat petugas verifikator SDM yang memproses berkas dan catatan revisi penolakan jika ada</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal Pengajuan</th>
                        <th class="px-4 py-3.5">Tanggal Diputuskan</th>
                        <th class="px-4 py-3.5">Nama Dokter & KSM</th>
                        <th class="px-4 py-3.5">No. SIP & Praktik</th>
                        <th class="px-4 py-3.5">Keputusan Status</th>
                        <th class="px-4 py-3.5">Admin SDM Verifikator</th>
                        <th class="px-4 py-3.5">Catatan Verifikasi / Alasan Penolakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($verifikasiRecords as $vr)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3.5 text-slate-600">{{ $vr->created_at->translatedFormat('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3.5 font-semibold text-slate-800">
                            {{ $vr->verified_at ? $vr->verified_at->translatedFormat('d/m/Y H:i') : 'Menunggu Approval' }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $vr->dokter->nama_lengkap }}</div>
                            <div class="text-[10px] text-slate-500">{{ $vr->dokter->ksm?->nama_ksm ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-mono font-semibold">{{ $vr->no_sip }}</div>
                            <div class="text-[10px] text-slate-500">Praktik Ke-{{ $vr->tempat_praktik_ke }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $vr->status_badge_class }}">
                                {{ $vr->is_verified ? 'DISETUJUI' : ($vr->status === 'menunggu_verifikasi' ? 'MENUNGGU VERIFIKASI' : 'DITOLAK / REVISI') }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 font-bold text-slate-800">
                            {{ $vr->verifiedBy?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-600 max-w-xs">
                            {{ $vr->catatan_verifikasi ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">Belum ada riwayat verifikasi berkas SIP.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
