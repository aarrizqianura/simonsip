@extends('layouts.app')

@section('title', 'Verifikasi & Approval SIP Baru')
@section('page_title', 'Antrean Verifikasi Legalitas SIP Dokter')

@section('content')
<div class="space-y-6" x-data="{ 
    approveModal: false, 
    rejectModal: false, 
    selectedSip: null,
    selectedDoctorName: '',
    selectedNoSip: '',
    openApprove(id, name, sip) {
        this.selectedSip = id;
        this.selectedDoctorName = name;
        this.selectedNoSip = sip;
        this.approveModal = true;
    },
    openReject(id, name, sip) {
        this.selectedSip = id;
        this.selectedDoctorName = name;
        this.selectedNoSip = sip;
        this.rejectModal = true;
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pemeriksaan & Validasi Pengajuan SIP</h2>
            <p class="text-xs text-slate-500">Alur verifikasi berkas renewal SIP dokter sebelum diaktifkan dalam sistem legalitas RSUD Dr. H. Moch. Ansari Saleh</p>
        </div>

        <!-- Filter Tab -->
        <div class="flex items-center gap-1 p-1 bg-slate-200/80 rounded-2xl text-xs font-bold">
            <a href="{{ route('verifikasi.index', ['status' => 'menunggu_verifikasi']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'menunggu_verifikasi' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Antrean Menunggu</span>
                @if($pendingCount > 0)
                    <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-900 text-[10px] font-black">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('verifikasi.index', ['status' => 'riwayat']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'riwayat' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <span>Riwayat Audit Verifikasi</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Dokter & KSM</th>
                        <th class="px-4 py-4">Rincian Pengajuan SIP</th>
                        <th class="px-4 py-4">Masa Berlaku Baru</th>
                        <th class="px-4 py-4">Berkas PDF</th>
                        <th class="px-4 py-4">Status & Waktu</th>
                        <th class="px-5 py-4 text-right">Keputusan Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sipRecords as $sip)
                    <tr class="hover:bg-slate-50/80 transition">
                        
                        <!-- Dokter & KSM -->
                        <td class="px-5 py-4">
                            <a href="{{ route('dokters.show', $sip->dokter) }}" class="font-bold text-slate-900 hover:text-emerald-700 text-sm">
                                {{ $sip->dokter->nama_lengkap }}
                            </a>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                {{ $sip->dokter->ksm?->nama_ksm ?? '-' }} &bull; NIP: {{ $sip->dokter->nip_nik }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp text-emerald-600"></i> {{ $sip->dokter->no_whatsapp }}
                            </div>
                        </td>

                        <!-- Rincian SIP -->
                        <td class="px-4 py-4">
                            <div class="font-mono font-bold text-slate-900">{{ $sip->no_sip }}</div>
                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px] mt-1">
                                Praktik Ke-{{ $sip->tempat_praktik_ke }} {{ $sip->tempat_praktik_ke === 1 ? '(Ansari Saleh)' : '' }}
                            </span>
                        </td>

                        <!-- Masa Berlaku -->
                        <td class="px-4 py-4">
                            <div class="text-[11px] text-slate-500">Terbit: {{ $sip->tgl_terbit->translatedFormat('d M Y') }}</div>
                            <div class="font-bold text-slate-800 mt-0.5">Exp: {{ $sip->tgl_kadaluarsa->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Masa 5 Tahun</div>
                        </td>

                        <!-- Berkas Dokumen -->
                        <td class="px-4 py-4">
                            <a href="{{ asset('storage/' . $sip->file_sip_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-bold transition">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>Periksa PDF</span>
                            </a>
                        </td>

                        <!-- Status & Log -->
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                {{ $sip->status_label }}
                            </span>
                            @if($sip->verifiedBy)
                                <div class="text-[10px] text-slate-500 mt-1">
                                    Verifikator: <strong>{{ $sip->verifiedBy->name }}</strong>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $sip->verified_at?->translatedFormat('d M Y, H:i') }}
                                </div>
                            @else
                                <div class="text-[10px] text-amber-600 mt-1 font-semibold">
                                    Diajukan: {{ $sip->created_at->diffForHumans() }}
                                </div>
                            @endif

                            @if($sip->catatan_verifikasi)
                                <div class="text-[10px] text-slate-600 mt-1 p-1 bg-slate-50 rounded border border-slate-100 max-w-xs truncate" title="{{ $sip->catatan_verifikasi }}">
                                    Catatan: {{ $sip->catatan_verifikasi }}
                                </div>
                            @endif
                        </td>

                        <!-- Aksi Keputusan -->
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($sip->status === 'menunggu_verifikasi' || !$sip->is_verified)
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openApprove({{ $sip->id }}, '{{ addslashes($sip->dokter->nama_lengkap) }}', '{{ $sip->no_sip }}')" 
                                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm flex items-center gap-1 transition">
                                        <i class="fa-solid fa-check"></i>
                                        <span>Setujui</span>
                                    </button>

                                    <button @click="openReject({{ $sip->id }}, '{{ addslashes($sip->dokter->nama_lengkap) }}', '{{ $sip->no_sip }}')" 
                                            class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs flex items-center gap-1 transition">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            @else
                                <span class="text-xs text-emerald-700 font-bold flex items-center justify-end gap-1">
                                    <i class="fa-solid fa-circle-check"></i> Sudah Disetujui
                                </span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-clipboard-check text-4xl text-slate-300 mb-2"></i>
                            <div>Tidak ada pengajuan berkas SIP yang memerlukan tindakan verifikasi saat ini.</div>
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

    <!-- Modal Form Approval -->
    <div x-show="approveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="approveModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-emerald-800 font-bold text-base">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Konfirmasi Persetujuan SIP</span>
                </div>
                <button @click="approveModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-800">
                Dokter: <strong x-text="selectedDoctorName"></strong><br>
                Nomor SIP: <strong x-text="selectedNoSip"></strong>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Menyetujui permohonan ini akan mengubah status legalitas SIP dokter menjadi <strong>🟢 Aman</strong> dan secara otomatis mengirimkan notifikasi resmi ke nomor WhatsApp dokter.
            </p>

            <form :action="'/verifikasi-sip/' + selectedSip + '/approve'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Verifikator (Opsional)</label>
                    <textarea name="catatan" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500">Berkas SIP telah diperiksa dan dinyatakan sah serta memenuhi standar regulasi rumah sakit.</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="approveModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Konfirmasi Setujui SIP</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Penolakan -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="rejectModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-rose-800 font-bold text-base">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                    <span>Tolak Berkas Pengajuan SIP</span>
                </div>
                <button @click="rejectModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="p-3 bg-rose-50 rounded-2xl border border-rose-200 text-xs text-rose-800">
                Dokter: <strong x-text="selectedDoctorName"></strong><br>
                Nomor SIP: <strong x-text="selectedNoSip"></strong>
            </div>

            <form :action="'/verifikasi-sip/' + selectedSip + '/reject'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Alasan Penolakan / Catatan Revisi Berkas <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="alasan_penolakan" rows="4" required placeholder="Jelaskan alasan penolakan, contoh: Scan dokumen SIP buram/tidak terbaca, masa berlaku tidak sesuai, dll." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                    <span class="text-[10px] text-slate-400 mt-1 block">Catatan ini akan otomatis dikirimkan ke WhatsApp dokter agar segera diperbaiki</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-ban"></i>
                        <span>Kirim Penolakan & Revisi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
