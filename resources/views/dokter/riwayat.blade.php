@extends('layouts.dokter')

@section('title', 'Riwayat SIP Saya')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Histori & Jejak Pengajuan SIP</h2>
            <p class="text-xs text-slate-500">Daftar rekaman Surat Izin Praktik, status verifikasi kepegawaian, dan dokumen digital Anda</p>
        </div>

        <a href="{{ route('dokter.renewal') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Ajukan Perpanjangan Baru</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Nomor SIP & Praktik</th>
                        <th class="px-4 py-4">Masa Berlaku</th>
                        <th class="px-4 py-4">Status & Sisa Hari</th>
                        <th class="px-4 py-4">Status Verifikasi SDM</th>
                        <th class="px-4 py-4">Catatan Verifikator</th>
                        <th class="px-5 py-4 text-right">Berkas PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sipRecords as $sip)
                    <tr class="hover:bg-slate-50 transition">
                        
                        <td class="px-5 py-4">
                            <div class="font-mono font-bold text-slate-900 text-sm">{{ $sip->no_sip }}</div>
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px] mt-1">
                                Tempat Praktik Ke-{{ $sip->tempat_praktik_ke }} {{ $sip->tempat_praktik_ke === 1 ? '(RSUD Ansari Saleh)' : '' }}
                            </span>
                        </td>

                        <td class="px-4 py-4">
                            <div class="text-[11px] text-slate-500">Terbit: {{ $sip->tgl_terbit->translatedFormat('d M Y') }}</div>
                            <div class="font-bold text-slate-800 mt-0.5">Exp: {{ $sip->tgl_kadaluarsa->translatedFormat('d M Y') }}</div>
                        </td>

                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                {{ $sip->status_label }}
                            </span>
                            <div class="text-[11px] font-bold mt-1 {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-700' }}">
                                {{ $sip->sisa_hari < 0 ? abs($sip->sisa_hari) . ' hari lalu' : $sip->sisa_hari . ' hari tersisa' }}
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            @if($sip->is_verified)
                                <div class="text-emerald-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i> Disetujui
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Oleh: {{ $sip->verifiedBy?->name ?? 'Admin SDM' }}
                                </div>
                            @elseif($sip->status === 'menunggu_verifikasi')
                                <div class="text-sky-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Menunggu Verifikasi
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Sedang diperiksa tim SDM
                                </div>
                            @else
                                <div class="text-rose-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-xmark"></i> Ditolak / Revisi
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-4 max-w-xs">
                            @if($sip->catatan_verifikasi)
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-[11px]">
                                    {{ $sip->catatan_verifikasi }}
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-right">
                            <a href="{{ asset('storage/' . $sip->file_sip_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold transition">
                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                <span>Lihat PDF</span>
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                            Belum ada riwayat pengajuan SIP yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sipRecords->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $sipRecords->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
