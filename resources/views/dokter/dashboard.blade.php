@extends('layouts.dokter')

@section('title', 'Dashboard Saya')

@section('content')
<div class="space-y-6">

    <!-- Doctor Greeting Banner -->
    <div class="rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-2">
                    <i class="fa-solid fa-hospital-user"></i> Portal Resmi Staf Medis Dokter
                </span>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-snug">
                    Selamat Datang, {{ $dokter->nama_lengkap }}
                </h2>
                <div class="text-xs text-slate-300 mt-1 flex items-center gap-2 flex-wrap">
                    <span>NIP/NIK: <strong>{{ $dokter->nip_nik }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ $dokter->ksm?->nama_ksm }}</span>
                    <span>&bull;</span>
                    <span>{{ $dokter->spesialisasi?->nama_spesialisasi }}</span>
                </div>
            </div>

            <!-- Quick Renewal Shortcut -->
            <a href="{{ route('dokter.renewal') }}" 
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-slate-900 font-extrabold text-sm shadow-xl shadow-emerald-500/20 transition transform active:scale-95 shrink-0">
                <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                <span>Unggah Perpanjangan SIP</span>
            </a>
        </div>
    </div>

    <!-- If there is a pending verification submission -->
    @if($pendingSip)
    <div class="p-5 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 flex items-start gap-3 shadow-sm">
        <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0 font-bold">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <div class="font-bold text-sm">Pengajuan Perpanjangan SIP Sedang Diproses</div>
            <div class="text-xs text-sky-800 mt-0.5">
                SIP No. <strong>{{ $pendingSip->no_sip }}</strong> (Praktik Ke-{{ $pendingSip->tempat_praktik_ke }}) sedang dalam antrean verifikasi oleh Subbagian SDM Kepegawaian RSUD Dr. H. Moch. Ansari Saleh. Anda akan menerima pemberitahuan otomatis via WhatsApp setelah berkas disetujui.
            </div>
        </div>
    </div>
    @endif

    <!-- Countdown & Legal Status Hero Cards -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Big Countdown Badge for Primary SIP -->
        <div class="md:col-span-7 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 flex flex-col justify-between relative overflow-hidden">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">SIP Praktik Utama (RSUD Dr. H. Moch. Ansari Saleh)</div>
                        <h3 class="text-base font-bold text-slate-900 mt-0.5">Countdown Masa Berlaku SIP</h3>
                    </div>
                    @if($primarySip)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $primarySip->status_badge_class }}">
                            {{ $primarySip->status_label }}
                        </span>
                    @endif
                </div>

                @if($primarySip)
                <div class="py-6 flex flex-col sm:flex-row items-center gap-6">
                    <!-- Big Days Remaining Counter -->
                    <div class="w-36 h-36 rounded-3xl flex flex-col items-center justify-center shrink-0 text-center shadow-lg {{ $primarySip->sisa_hari <= 30 ? 'bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-rose-500/30' : ($primarySip->sisa_hari <= 90 ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 shadow-amber-500/30' : 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-emerald-500/30') }}">
                        <span class="text-4xl font-black tracking-tight">
                            {{ $primarySip->sisa_hari < 0 ? abs($primarySip->sisa_hari) : $primarySip->sisa_hari }}
                        </span>
                        <span class="text-[11px] font-bold uppercase tracking-wider mt-1 opacity-90">
                            {{ $primarySip->sisa_hari < 0 ? 'Hari Lewat' : 'Hari Lagi' }}
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div>
                            <div class="text-slate-400">Nomor SIP Resmi:</div>
                            <div class="text-sm font-bold text-slate-900 font-mono">{{ $primarySip->no_sip }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400">Tanggal Terbit & Expired:</div>
                            <div class="font-semibold text-slate-800">
                                {{ $primarySip->tgl_terbit->translatedFormat('d M Y') }} &mdash; 
                                <strong class="{{ $primarySip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-900' }}">{{ $primarySip->tgl_kadaluarsa->translatedFormat('d F Y') }}</strong>
                            </div>
                        </div>
                        <div>
                            <div class="text-slate-400">Status Validitas:</div>
                            <div class="font-semibold text-emerald-700">
                                <i class="fa-solid fa-check-circle mr-1"></i> Terdaftar Resmi di Subbag Kepegawaian
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-10 text-slate-400 text-xs">
                    SIP Praktik 1 belum didaftarkan oleh SDM.
                </div>
                @endif
            </div>

            <!-- Footer Card -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                @if($primarySip && $primarySip->file_sip_path)
                    <a href="{{ asset('storage/' . $primarySip->file_sip_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-700 hover:text-emerald-900 font-bold">
                        <i class="fa-solid fa-file-pdf text-rose-500"></i>
                        <span>Lihat Dokumen SIP Saya</span>
                    </a>
                @endif

                @if($primarySip && $primarySip->sisa_hari <= 90)
                    <a href="{{ route('dokter.renewal') }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                        Perbarui Sekarang &rarr;
                    </a>
                @endif
            </div>
        </div>

        <!-- STR Status & Early Warning Info -->
        <div class="md:col-span-5 space-y-6">
            
            <!-- STR Status Card -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Legalitas STR</span>
                    @if($dokter->is_str_seumur_hidup)
                        <span class="text-[10px] font-bold bg-teal-50 text-teal-700 px-2.5 py-0.5 rounded-full border border-teal-200">
                            <i class="fa-solid fa-infinity"></i> Seumur Hidup
                        </span>
                    @else
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-full">
                            Berjangka Waktu
                        </span>
                    @endif
                </div>

                <div class="text-xs space-y-2">
                    <div>
                        <div class="text-[11px] text-slate-400">Nomor Registrasi STR:</div>
                        <div class="font-mono font-bold text-slate-900 text-sm">{{ $dokter->no_str }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Masa Berlaku:</div>
                        <div class="font-bold text-slate-800">{{ $dokter->str_status_text }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600">
                        Sesuai UU Kesehatan No. 17/2023, STR berlaku seumur hidup. Sedangkan SIP wajib diperbarui setiap 5 tahun sekali.
                    </div>
                </div>
            </div>

            <!-- WhatsApp Early Warning Gateway Status -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Early Warning Gateway</span>
                    <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        <i class="fa-brands fa-whatsapp text-emerald-500"></i> Aktif
                    </span>
                </div>

                <div class="text-xs text-slate-600 space-y-1.5">
                    <div>Notifikasi otomatis dikirimkan ke WhatsApp Anda:</div>
                    <div class="font-mono font-bold text-emerald-800 bg-emerald-50/60 p-2 rounded-xl border border-emerald-200/50">
                        {{ $dokter->no_whatsapp }}
                    </div>
                    <div class="text-[11px] text-slate-400 pt-1">
                        Jadwal reminder: 90 hari (🟡 Peringatan), 60 hari (🟠 Mendesak), dan 30 hari (🔴 Kritis).
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Digital Preview Cards of All Active SIPs (Multi-SIP) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Digital Preview Kartu SIP Saya</h3>
                <p class="text-xs text-slate-500">Daftar legalitas praktik terdaftar (Praktik Ke-1, 2, dan 3)</p>
            </div>
            <a href="{{ route('dokter.renewal') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                + Tambah / Perpanjang SIP
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @for($p = 1; $p <= 3; $p++)
            @php
                $sip = $allSips->where('tempat_praktik_ke', $p)->first();
            @endphp
            <div class="bg-white rounded-3xl p-6 shadow-sm border {{ $sip ? 'border-slate-200' : 'border-dashed border-slate-300 bg-slate-50/50' }} flex flex-col justify-between">
                
                @if($sip)
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-lg {{ $p === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                Tempat Praktik Ke-{{ $p }} {{ $p === 1 ? '(Ansari Saleh)' : '' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                {{ $sip->status_label }}
                            </span>
                        </div>

                        <div class="mt-4 space-y-2.5 text-xs">
                            <div>
                                <div class="text-[11px] text-slate-400">Nomor SIP:</div>
                                <div class="font-mono font-bold text-slate-900 mt-0.5">{{ $sip->no_sip }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400">Masa Berlaku:</div>
                                <div class="font-semibold text-slate-800">
                                    {{ $sip->tgl_terbit->translatedFormat('d M Y') }} &mdash; {{ $sip->tgl_kadaluarsa->translatedFormat('d M Y') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400">Sisa Hari:</div>
                                <div class="font-bold {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $sip->sisa_hari }} Hari
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs mt-4">
                        <a href="{{ asset('storage/' . $sip->file_sip_path) }}" target="_blank" class="text-emerald-700 hover:text-emerald-900 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-file-pdf text-rose-500"></i>
                            <span>Dokumen PDF</span>
                        </a>
                        <span class="text-[10px] text-slate-400">
                            {{ $sip->is_verified ? 'Terverifikasi' : 'Pending' }}
                        </span>
                    </div>

                @else
                    <div class="text-center py-8 text-xs text-slate-400 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-base">
                            <i class="fa-solid fa-hospital"></i>
                        </div>
                        <div class="font-semibold text-slate-600">Praktik Ke-{{ $p }} Belum Ada</div>
                        <p class="text-[11px]">Anda belum mendaftarkan SIP tempat praktik ke-{{ $p }}</p>
                        <a href="{{ route('dokter.renewal') }}" class="inline-block mt-2 text-emerald-700 font-bold hover:underline">
                            + Daftarkan SIP Ke-{{ $p }}
                        </a>
                    </div>
                @endif

            </div>
            @endfor
        </div>
    </div>

</div>
@endsection
