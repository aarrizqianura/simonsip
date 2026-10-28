@extends('layouts.app')

@section('title', 'Dashboard Monitoring Legalitas SIP')
@section('page_title', 'Dashboard Monitoring Legalitas SIP Dokter')

@section('content')
<div class="space-y-8">
    
    <!-- Header Filter & Overview Banner -->
    <div class="glass-card rounded-2xl p-6 shadow-sm border border-slate-200/80 bg-gradient-to-r from-slate-900 via-slate-800 to-ansari-900 text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-400 uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Sistem Monitoring Real-Time &bull; STARKES Akreditasi RS
                </div>
                <h2 class="text-2xl font-black mt-1">Status Kepatuhan Legalitas SIP Dokter</h2>
                <p class="text-slate-300 text-sm mt-0.5">RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</p>
            </div>

            <!-- KSM Filter Dropdown -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                <div class="relative min-w-[220px]">
                    <select name="ksm_id" onchange="this.form.submit()" class="w-full bg-slate-800/90 text-white text-xs font-semibold rounded-xl border border-slate-700 py-2.5 pl-3 pr-8 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Semua KSM (Total Dokter: {{ $totalDokter }}) --</option>
                        @foreach($allKsms as $k)
                            <option value="{{ $k->id }}" {{ $ksmFilter == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_ksm }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($ksmFilter)
                    <a href="{{ route('dashboard') }}" class="p-2.5 rounded-xl bg-slate-700/80 hover:bg-slate-700 text-slate-300 text-xs">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Traffic Light & KPI Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <!-- Total Dokter -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs font-bold uppercase tracking-wider">Total Dokter</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-slate-900">{{ $totalDokter }}</div>
                <div class="text-[11px] text-slate-500 font-medium mt-1">Dokter Terdaftar</div>
            </div>
        </div>

        <!-- 🟢 Status Aman -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-200 ring-1 ring-emerald-500/20 flex flex-col justify-between hover:shadow-md transition bg-gradient-to-b from-emerald-50/40 to-white">
            <div class="flex items-center justify-between text-emerald-700">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500"></span>
                    Aman
                </span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-emerald-700">{{ $countAman }}</div>
                <div class="text-[11px] text-emerald-600 font-medium mt-1">> 90 Hari Masa Berlaku</div>
            </div>
        </div>

        <!-- 🟡 Status Peringatan -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-amber-200 ring-1 ring-amber-500/20 flex flex-col justify-between hover:shadow-md transition bg-gradient-to-b from-amber-50/40 to-white">
            <div class="flex items-center justify-between text-amber-700">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500"></span>
                    Peringatan
                </span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-amber-700">{{ $countWarning }}</div>
                <div class="text-[11px] text-amber-600 font-medium mt-1">61–90 Hari (WA Tahap 1)</div>
            </div>
        </div>

        <!-- 🟠 Status Mendesak -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-orange-200 ring-1 ring-orange-500/20 flex flex-col justify-between hover:shadow-md transition bg-gradient-to-b from-orange-50/40 to-white">
            <div class="flex items-center justify-between text-orange-700">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-sm shadow-orange-500"></span>
                    Mendesak
                </span>
                <div class="w-8 h-8 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-orange-700">{{ $countMendesak }}</div>
                <div class="text-[11px] text-orange-600 font-medium mt-1">31–60 Hari (WA Tahap 2)</div>
            </div>
        </div>

        <!-- 🔴 Status Kadaluarsa -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-rose-200 ring-1 ring-rose-500/20 flex flex-col justify-between hover:shadow-md transition bg-gradient-to-b from-rose-50/40 to-white">
            <div class="flex items-center justify-between text-rose-700">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500 animate-ping"></span>
                    Kadaluarsa
                </span>
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-rose-700">{{ $countExpired }}</div>
                <div class="text-[11px] text-rose-600 font-medium mt-1">≤ 30 Hari / Telah Lewat</div>
            </div>
        </div>

        <!-- 🔵 Menunggu Verifikasi -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-sky-200 ring-1 ring-sky-500/20 flex flex-col justify-between hover:shadow-md transition bg-gradient-to-b from-sky-50/40 to-white">
            <div class="flex items-center justify-between text-sky-700">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500 shadow-sm shadow-sky-500"></span>
                    Verifikasi
                </span>
                <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-black text-sky-700">{{ $countMenunggu }}</div>
                <div class="text-[11px] text-sky-600 font-medium mt-1">Pengajuan Renewal Baru</div>
            </div>
        </div>

    </div>

    <!-- Charts & Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Compliance Rate Doughnut -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Kepatuhan Legalitas SIP</h3>
                        <p class="text-xs text-slate-500">Indikator Mutu Pelayanan & Akreditasi STARKES</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $complianceRate >= 80 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $complianceRate }}% Tertib
                    </span>
                </div>

                <div class="relative w-48 h-48 mx-auto my-6">
                    <canvas id="complianceChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-black text-slate-800">{{ $complianceRate }}%</span>
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Tingkat Aman</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-xs">
                <div class="flex items-center gap-2 text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span>Aman: <strong>{{ $countAman }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                    <span>Peringatan: <strong>{{ $countWarning }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                    <span>Mendesak: <strong>{{ $countMendesak }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                    <span>Kadaluarsa: <strong>{{ $countExpired }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Matriks KSM Status Bar -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Distribusi Kepatuhan Per KSM</h3>
                    <p class="text-xs text-slate-500">Monitoring per Kelompok Staf Medis</p>
                </div>
                <a href="{{ route('laporan.index', ['jenis' => 'matriks_ksm']) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                    <span>Lihat Matriks Lengkap</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
                @foreach($ksms as $k)
                <div>
                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                        <span class="text-slate-800">{{ $k->nama_ksm }} ({{ $k->dokters_count }} Dokter)</span>
                        <div class="flex items-center gap-2 text-[11px]">
                            @if($k->expired > 0)
                                <span class="text-rose-600 font-bold"><i class="fa-solid fa-circle-xmark mr-0.5"></i>{{ $k->expired }} Exp</span>
                            @endif
                            @if($k->mendesak > 0)
                                <span class="text-orange-600 font-bold"><i class="fa-solid fa-bell mr-0.5"></i>{{ $k->mendesak }} Mds</span>
                            @endif
                            @if($k->warning > 0)
                                <span class="text-amber-600 font-bold"><i class="fa-solid fa-triangle-exclamation mr-0.5"></i>{{ $k->warning }} Prg</span>
                            @endif
                            <span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check mr-0.5"></i>{{ $k->aman }} Aman</span>
                        </div>
                    </div>
                    @php
                        $tot = max(1, $k->total_sip);
                        $pAman = ($k->aman / $tot) * 100;
                        $pWarn = ($k->warning / $tot) * 100;
                        $pMend = ($k->mendesak / $tot) * 100;
                        $pExp = ($k->expired / $tot) * 100;
                    @endphp
                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden flex">
                        <div style="width: {{ $pAman }}%" class="bg-emerald-500" title="Aman: {{ $k->aman }}"></div>
                        <div style="width: {{ $pWarn }}%" class="bg-amber-400" title="Peringatan: {{ $k->warning }}"></div>
                        <div style="width: {{ $pMend }}%" class="bg-orange-500" title="Mendesak: {{ $k->mendesak }}"></div>
                        <div style="width: {{ $pExp }}%" class="bg-rose-500" title="Kadaluarsa: {{ $k->expired }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Action Needed & Early Warning Feeds -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Table Tindak Lanjut Segera -->
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                        <span>Daftar Perhatian & Tindak Lanjut Segera</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Dokter dengan SIP mendekati kadaluarsa, expired, atau butuh persetujuan</p>
                </div>
                <a href="{{ route('dokters.index', ['status' => 'expired']) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Semua Dokter &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Dokter & KSM</th>
                            <th class="px-4 py-3.5">No. SIP & Praktik</th>
                            <th class="px-4 py-3.5">Batas Kadaluarsa</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($urgentSips as $sip)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('dokters.show', $sip->dokter) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                    {{ $sip->dokter->nama_lengkap }}
                                </a>
                                <div class="text-[11px] text-slate-500">
                                    {{ $sip->dokter->ksm?->nama_ksm ?? '-' }} &bull; NIP: {{ $sip->dokter->nip_nik }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-slate-800">{{ $sip->no_sip }}</span>
                                <div class="text-[11px] text-slate-500">Praktik Ke-{{ $sip->tempat_praktik_ke }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $sip->tgl_kadaluarsa->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-[11px] {{ $sip->sisa_hari <= 30 ? 'text-rose-500 font-bold' : 'text-slate-500' }}">
                                    {{ $sip->sisa_hari < 0 ? abs($sip->sisa_hari) . ' hari lalu' : $sip->sisa_hari . ' hari lagi' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                    {{ $sip->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1">
                                @if($sip->status === 'menunggu_verifikasi')
                                    <a href="{{ route('verifikasi.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                        <i class="fa-solid fa-file-check"></i> Verifikasi
                                    </a>
                                @else
                                    <a href="{{ route('dokters.show', $sip->dokter) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-check-double text-3xl text-emerald-400 mb-2"></i>
                                <div>Tidak ada SIP yang membutuhkan perhatian mendesak saat ini. Semua dalam status aman!</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Log WhatsApp Gateway Feed -->
        <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-emerald-500 text-lg"></i>
                            <span>Aktivitas WhatsApp</span>
                        </h3>
                        <p class="text-[11px] text-slate-500">Early Warning Gateway Logs</p>
                    </div>
                    <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-1 rounded-full border border-emerald-200">
                        Gateway Aktif
                    </span>
                </div>

                <div class="space-y-3.5 mt-4">
                    @forelse($recentNotifications as $notif)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                            <span class="font-semibold text-slate-800">{{ $notif->sipRecord?->dokter?->nama_lengkap ?? 'Dokter' }}</span>
                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-slate-600 text-[11px] truncate">
                            {{ $notif->message_type_label }} &bull; {{ $notif->recipient_phone }}
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[10px]">
                            <span class="font-bold {{ $notif->status === 'sent' ? 'text-emerald-600' : 'text-rose-600' }}">
                                <i class="fa-solid fa-check-double mr-1"></i>{{ strtoupper($notif->status) }}
                            </span>
                            <span class="text-slate-400">{{ $notif->created_at->format('H:i') }} WITA</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        Belum ada riwayat pengiriman notifikasi WhatsApp.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 mt-4">
                <a href="{{ route('whatsapp.index') }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition">
                    <span>Buka Matriks & Blast WA</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('complianceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Aman', 'Peringatan', 'Mendesak', 'Kadaluarsa'],
                    datasets: [{
                        data: [{{ $countAman }}, {{ $countWarning }}, {{ $countMendesak }}, {{ $countExpired }}],
                        backgroundColor: [
                            '#10b981', // Emerald
                            '#fbbf24', // Amber
                            '#f97316', // Orange
                            '#f43f5e'  // Rose
                        ],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.raw + ' SIP';
                                }
                            }
                        }
                    },
                    cutout: '72%'
                }
            });
        }
    });
</script>
@endpush
