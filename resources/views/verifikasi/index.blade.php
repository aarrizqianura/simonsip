@extends('layouts.app')

@section('title', 'Verifikasi & Approval SIP Baru')
@section('page_title', 'Antrean Verifikasi Legalitas SIP Dokter')

@section('content')
<div class="space-y-6" 
     x-data="{ 
        approveModal: false, 
        rejectModal: false, 
        pdfModal: false,
        selectedSip: null,
        selectedDoctorName: '',
        selectedDoctorKsm: '',
        selectedDoctorWa: '',
        selectedNoSip: '',
        selectedPraktikKe: '',
        selectedMasaBerlaku: '',
        selectedIsPending: false,
        pdfPreviewUrl: '',
        pdfDownloadUrl: '',
        pdfFileExt: 'pdf',
        zoomLevel: 100,
        rejectionReason: '',
        presetReasons: [
            'Masa berlaku tidak sesuai dengan dokumen fisik',
            'Hasil scan dokumen buram / tidak terbaca dengan jelas',
            'Nomor SIP yang diinput tidak sesuai dengan berkas PDF',
            'Dokumen tidak lengkap atau halaman terpotong',
            'Tanda tangan elektronik / stempel DPMPTSP belum tertera'
        ],
        openPdfPreview(data) {
            this.selectedSip = data.id;
            this.selectedDoctorName = data.doctorName;
            this.selectedDoctorKsm = data.ksm;
            this.selectedDoctorWa = data.wa;
            this.selectedNoSip = data.noSip;
            this.selectedPraktikKe = data.praktikKe;
            this.selectedMasaBerlaku = data.masaBerlaku;
            this.selectedIsPending = data.isPending;
            this.pdfPreviewUrl = data.previewUrl;
            this.pdfDownloadUrl = data.downloadUrl;
            this.pdfFileExt = data.fileExt || 'pdf';
            this.zoomLevel = 100;
            this.pdfModal = true;
        },
        zoomIn() {
            if (this.zoomLevel < 200) this.zoomLevel += 25;
        },
        zoomOut() {
            if (this.zoomLevel > 50) this.zoomLevel -= 25;
        },
        resetZoom() {
            this.zoomLevel = 100;
        },
        openApprove(id, name, sip, wa = '') {
            this.pdfModal = false;
            this.selectedSip = id;
            this.selectedDoctorName = name;
            this.selectedNoSip = sip;
            this.selectedDoctorWa = wa;
            this.approveModal = true;
        },
        openReject(id, name, sip, wa = '') {
            this.pdfModal = false;
            this.selectedSip = id;
            this.selectedDoctorName = name;
            this.selectedNoSip = sip;
            this.selectedDoctorWa = wa;
            this.rejectionReason = '';
            this.rejectModal = true;
        },
        selectPresetReason(reason) {
            if (!this.rejectionReason) {
                this.rejectionReason = reason;
            } else if (!this.rejectionReason.includes(reason)) {
                this.rejectionReason = this.rejectionReason.trim() + '. ' + reason;
            }
        }
     }"
     @keydown.escape.window="pdfModal = false; approveModal = false; rejectModal = false">

    <!-- Real-time WhatsApp Gateway Notification Banner (After Approve / Reject) -->
    @if(session('wa_notification'))
        @php $waNotif = session('wa_notification'); @endphp
        <div x-data="{ openNotif: true, showPayload: false }" 
             x-show="openNotif" 
             x-cloak
             class="rounded-3xl p-5 border shadow-sm transition-all {{ $waNotif['type'] === 'approved' ? 'bg-gradient-to-r from-emerald-50 via-teal-50/70 to-white border-emerald-200' : 'bg-gradient-to-r from-rose-50 via-amber-50/50 to-white border-rose-200' }}">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm {{ $waNotif['type'] === 'approved' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                        <i class="fa-brands fa-whatsapp text-2xl"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-extrabold text-sm text-slate-900">
                                Notifikasi Status WhatsApp Otomatis Terkirim
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $waNotif['status'] === 'sent' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                <i class="fa-solid {{ $waNotif['status'] === 'sent' ? 'fa-check-double' : 'fa-clock' }}"></i>
                                {{ $waNotif['status'] === 'sent' ? 'DELIVERED VIA WA GATEWAY' : 'QUEUED' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">
                            Pesan {{ $waNotif['type'] === 'approved' ? 'persetujuan aktivasi SIP' : 'penolakan & instruksi revisi berkas SIP' }} untuk 
                            <strong>{{ $waNotif['doctor_name'] }}</strong> (No. WA: <span class="font-mono font-bold text-emerald-700">+{{ $waNotif['phone'] }}</span>) telah dikirimkan secara real-time.
                        </p>
                        @if(!empty($waNotif['alasan']))
                            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/90 border border-rose-200 text-[11px] text-rose-800">
                                <i class="fa-solid fa-comment-dots text-rose-500"></i>
                                <span><strong>Alasan Penolakan:</strong> "{{ $waNotif['alasan'] }}"</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" 
                            @click="showPayload = !showPayload" 
                            class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-regular fa-eye"></i>
                        <span x-text="showPayload ? 'Sembunyikan Pesan' : 'Lihat Isi Pesan WA'"></span>
                    </button>
                    <a href="{{ $waNotif['wa_me_url'] }}" 
                       target="_blank" 
                       class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>Buka Chat WA Dokter</span>
                    </a>
                    <button type="button" @click="openNotif = false" class="p-2 text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Collapsible WA Message Preview -->
            <div x-show="showPayload" x-cloak class="mt-4 pt-3 border-t border-slate-200/70">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Salinan Pesan WhatsApp Gateway:</div>
                <pre class="p-3.5 rounded-2xl bg-slate-900 text-emerald-300 text-xs font-mono whitespace-pre-wrap leading-relaxed overflow-x-auto">{{ $waNotif['message'] }}</pre>
            </div>
        </div>
    @endif

    <!-- Page Header & Summary Cards -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pemeriksaan & Validasi Pengajuan SIP</h2>
            <p class="text-xs text-slate-500">Alur verifikasi berkas renewal SIP dokter oleh Bagian SDM sebelum diaktifkan dalam sistem legalitas RSUD Dr. H. Moch. Ansari Saleh</p>
        </div>

        <!-- Filter Tab -->
        <div class="flex items-center gap-1.5 p-1.5 bg-slate-200/80 rounded-2xl text-xs font-bold self-start lg:self-auto">
            <a href="{{ route('verifikasi.index', ['status' => 'menunggu_verifikasi']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ $status === 'menunggu_verifikasi' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-hourglass-half {{ $status === 'menunggu_verifikasi' ? 'text-amber-500' : 'text-slate-400' }}"></i>
                <span>Antrean Menunggu</span>
                @if($pendingCount > 0)
                    <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-900 text-[10px] font-black">
                        {{ $pendingCount }}
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">
                        0
                    </span>
                @endif
            </a>
            <a href="{{ route('verifikasi.index', ['status' => 'riwayat']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 {{ $status === 'riwayat' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-clock-rotate-left {{ $status === 'riwayat' ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                <span>Riwayat Audit Verifikasi</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold">
                    {{ ($approvedCount ?? 0) + ($rejectedCount ?? 0) }}
                </span>
            </a>
        </div>
    </div>

    <!-- Mini Info Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Antrean Menunggu</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $pendingCount }} <span class="text-xs font-semibold text-slate-500">Berkas</span></div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-file-shield"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Telah Disetujui</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ $approvedCount ?? 0 }} <span class="text-xs font-semibold text-slate-500">SIP Aktif</span></div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ditolak / Revisi</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $rejectedCount ?? 0 }} <span class="text-xs font-semibold text-slate-500">Pengajuan</span></div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-file-circle-xmark"></i>
            </div>
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
                        <th class="px-4 py-4">Status & Notifikasi WA</th>
                        <th class="px-5 py-4 text-right">Keputusan Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sipRecords as $sip)
                    @php
                        $fileExt = strtolower(pathinfo($sip->file_sip_path ?? 'pdf', PATHINFO_EXTENSION) ?: 'pdf');
                        $latestWaLog = $sip->notificationLogs->whereIn('message_type', ['verifikasi_approved', 'verifikasi_rejected'])->first();
                        $cleanWaPhone = preg_replace('/[^0-9]/', '', $sip->dokter->no_whatsapp ?? '');
                        if (str_starts_with($cleanWaPhone, '0')) {
                            $cleanWaPhone = '62' . substr($cleanWaPhone, 1);
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        
                        <!-- Dokter & KSM -->
                        <td class="px-5 py-4">
                            <a href="{{ route('dokters.show', $sip->dokter) }}" class="font-bold text-slate-900 hover:text-emerald-700 text-sm">
                                {{ $sip->dokter->nama_lengkap }}
                            </a>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                {{ $sip->dokter->ksm?->nama_ksm ?? '-' }} &bull; NIP: {{ $sip->dokter->nip_nik }}
                            </div>
                            <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/60">
                                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                    {{ $sip->dokter->no_whatsapp }}
                                </span>
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
                            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                                <i class="fa-regular fa-calendar-check mr-0.5"></i> Masa 5 Tahun
                            </div>
                        </td>

                        <!-- Berkas Dokumen (Trigger Modal Pratinjau PDF) -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                        @click="openPdfPreview({
                                            id: {{ $sip->id }},
                                            doctorName: @js($sip->dokter->nama_lengkap),
                                            ksm: @js($sip->dokter->ksm?->nama_ksm ?? '-'),
                                            wa: @js($sip->dokter->no_whatsapp),
                                            noSip: @js($sip->no_sip),
                                            praktikKe: 'Praktik Ke-{{ $sip->tempat_praktik_ke }}',
                                            masaBerlaku: '{{ $sip->tgl_terbit->translatedFormat('d M Y') }} s/d {{ $sip->tgl_kadaluarsa->translatedFormat('d M Y') }}',
                                            isPending: {{ $sip->status === 'menunggu_verifikasi' ? 'true' : 'false' }},
                                            previewUrl: '{{ route('verifikasi.preview', $sip) }}',
                                            downloadUrl: '{{ route('verifikasi.download', $sip) }}',
                                            fileExt: '{{ $fileExt }}'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 hover:border-rose-300 font-bold transition shadow-2xs cursor-pointer">
                                    <i class="fa-solid fa-file-pdf text-rose-600"></i>
                                    <span>Periksa PDF</span>
                                </button>

                                <a href="{{ route('verifikasi.download', $sip) }}" 
                                   title="Unduh Cepat Berkas PDF"
                                   class="p-1.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        </td>

                        <!-- Status & Log -->
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                {{ $sip->status_label }}
                            </span>

                            @if($sip->verifiedBy)
                                <div class="text-[10px] text-slate-500 mt-1">
                                    Verifikator: <strong>{{ $sip->verifiedBy->name }}</strong>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $sip->verified_at?->translatedFormat('d M Y, H:i') }} WITA
                                </div>
                            @else
                                <div class="text-[10px] text-amber-600 mt-1 font-semibold">
                                    <i class="fa-regular fa-clock mr-0.5"></i> Diajukan: {{ $sip->created_at->diffForHumans() }}
                                </div>
                            @endif

                            @if($sip->catatan_verifikasi)
                                <div class="text-[10px] {{ $sip->is_rejected ? 'text-rose-700 bg-rose-50/80 border-rose-200' : 'text-slate-600 bg-slate-50 border-slate-200' }} mt-1.5 p-1.5 rounded-lg border max-w-xs" title="{{ $sip->catatan_verifikasi }}">
                                    <span class="font-bold">{{ $sip->is_rejected ? 'Alasan Ditolak:' : 'Catatan:' }}</span>
                                    <span class="line-clamp-2">{{ $sip->catatan_verifikasi }}</span>
                                </div>
                            @endif

                            @if($latestWaLog)
                                <div class="mt-1.5 flex items-center gap-1 text-[10px] font-semibold {{ $latestWaLog->status === 'sent' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <span>Notifikasi WA {{ $latestWaLog->status === 'sent' ? 'Terkirim' : 'Pending' }} ({{ $latestWaLog->sent_at?->format('H:i') ?? '-' }})</span>
                                </div>
                            @endif
                        </td>

                        <!-- Aksi Keputusan -->
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($sip->status === 'menunggu_verifikasi')
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            @click="openApprove({{ $sip->id }}, @js($sip->dokter->nama_lengkap), @js($sip->no_sip), @js($sip->dokter->no_whatsapp))" 
                                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm flex items-center gap-1 transition cursor-pointer">
                                        <i class="fa-solid fa-check"></i>
                                        <span>Setujui</span>
                                    </button>

                                    <button type="button"
                                            @click="openReject({{ $sip->id }}, @js($sip->dokter->nama_lengkap), @js($sip->no_sip), @js($sip->dokter->no_whatsapp))" 
                                            class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs flex items-center gap-1 transition cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            @elseif($sip->is_rejected)
                                <div class="flex flex-col items-end gap-1.5">
                                    <span class="text-xs text-rose-700 font-bold inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-rose-50 border border-rose-200">
                                        <i class="fa-solid fa-circle-xmark"></i> Ditolak (Perlu Revisi)
                                    </span>
                                    @php
                                        $rejectWaText = "Yth. {$sip->dokter->nama_lengkap}, pengajuan SIP No. {$sip->no_sip} belum dapat disetujui dengan alasan: {$sip->catatan_verifikasi}. Mohon unggah ulang berkas perbaikan melalui SIMONSIP.";
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanWaPhone }}?text={{ rawurlencode($rejectWaText) }}"
                                       target="_blank"
                                       class="text-[10px] font-bold text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp"></i> Kirim Ulang Catatan via WA
                                    </a>
                                </div>
                            @else
                                <div class="flex flex-col items-end gap-1.5">
                                    <span class="text-xs text-emerald-700 font-bold inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check"></i> Sudah Disetujui
                                    </span>
                                    @php
                                        $approveWaText = "Yth. {$sip->dokter->nama_lengkap}, pengajuan SIP No. {$sip->no_sip} telah DISETUJUI dan aktif hingga " . $sip->tgl_kadaluarsa->translatedFormat('d F Y') . ". Terima kasih.";
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanWaPhone }}?text={{ rawurlencode($approveWaText) }}"
                                       target="_blank"
                                       class="text-[10px] font-bold text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp"></i> Hubungi Dokter via WA
                                    </a>
                                </div>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <!-- Optimalisasi Desain Empty State (Saat Antrean Kosong) -->
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="max-w-md mx-auto flex flex-col items-center">
                                <!-- Ilustrasi Ikon Centang Hijau Berlapis -->
                                <div class="relative mb-5">
                                    <div class="w-24 h-24 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center shadow-inner">
                                        <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 ring-4 ring-white">
                                            <i class="fa-solid fa-check text-3xl"></i>
                                        </div>
                                    </div>
                                    <span class="absolute -top-1 -right-1 w-8 h-8 rounded-full bg-amber-100 border-2 border-white text-amber-600 flex items-center justify-center text-xs shadow-sm">
                                        <i class="fa-solid fa-sparkles"></i>
                                    </span>
                                </div>

                                @if($status === 'menunggu_verifikasi')
                                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                                        Semua antrean pengajuan SIP telah selesai diverifikasi
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                        Tidak ada berkas pengajuan perpanjangan Surat Izin Praktik (SIP) dokter yang menunggu pemeriksaan saat ini. Sistem akan otomatis menampilkan berkas baru saat dokter mengunggah dokumen.
                                    </p>
                                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
                                        <a href="{{ route('verifikasi.index', ['status' => 'riwayat']) }}" 
                                           class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs transition inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                            <span>Lihat Riwayat Audit Verifikasi</span>
                                        </a>
                                        <a href="{{ route('dashboard') }}" 
                                           class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-chart-pie"></i>
                                            <span>Ke Dashboard Monitoring</span>
                                        </a>
                                    </div>
                                @else
                                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                                        Belum ada riwayat audit verifikasi berkas SIP
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                        Rekam jejak persetujuan maupun penolakan berkas SIP oleh Bagian SDM akan tercatat secara otomatis pada halaman ini.
                                    </p>
                                    <div class="mt-5">
                                        <a href="{{ route('verifikasi.index', ['status' => 'menunggu_verifikasi']) }}" 
                                           class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition inline-flex items-center gap-1.5 shadow-sm">
                                            <i class="fa-solid fa-list-check"></i>
                                            <span>Cek Antrean Menunggu</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
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

    <!-- ========================================================================= -->
    <!-- 1. MODAL PRATINJAU DOKUMEN (PDF PREVIEW MODAL DENGAN ZOOM & UNDUH CEPAT)  -->
    <!-- ========================================================================= -->
    <div x-show="pdfModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden bg-slate-900/75 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-3xl max-w-5xl w-full h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden" 
             @click.away="pdfModal = false">
            
            <!-- Modal Header & Toolbar (Zoom + Unduh Cepat) -->
            <div class="px-5 py-3.5 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 border border-rose-400/30 text-rose-400 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-white truncate" x-text="selectedDoctorName"></span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-emerald-300 border border-slate-700 text-[10px] font-mono" x-text="selectedPraktikKe"></span>
                        </div>
                        <div class="text-[11px] text-slate-300 truncate mt-0.5">
                            No. SIP: <strong class="font-mono text-white" x-text="selectedNoSip"></strong> &bull; Berlaku: <span x-text="selectedMasaBerlaku"></span>
                        </div>
                    </div>
                </div>

                <!-- PDF Interactive Controls: Zoom Out, Reset, Zoom In, Unduh Cepat, Close -->
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Zoom Control Group -->
                    <div class="inline-flex items-center bg-slate-800 rounded-xl p-1 border border-slate-700 text-xs">
                        <button type="button" 
                                @click="zoomOut()" 
                                :disabled="zoomLevel <= 50"
                                title="Perkecil Tampilan (Zoom Out)"
                                class="w-7 h-7 rounded-lg hover:bg-slate-700 disabled:opacity-40 flex items-center justify-center text-slate-200 transition cursor-pointer">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </button>
                        <button type="button" 
                                @click="resetZoom()" 
                                title="Reset Ukuran Zoom (100%)"
                                class="px-2.5 h-7 font-mono font-bold text-[11px] text-emerald-300 hover:text-white transition cursor-pointer"
                                x-text="zoomLevel + '%'">
                        </button>
                        <button type="button" 
                                @click="zoomIn()" 
                                :disabled="zoomLevel >= 200"
                                title="Perbesar Tampilan (Zoom In)"
                                class="w-7 h-7 rounded-lg hover:bg-slate-700 disabled:opacity-40 flex items-center justify-center text-slate-200 transition cursor-pointer">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                    </div>

                    <!-- Tombol Unduh Cepat -->
                    <a :href="pdfDownloadUrl" 
                       class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Cepat</span>
                    </a>

                    <!-- Buka di Tab Baru (Opsional) -->
                    <a :href="pdfPreviewUrl" 
                       target="_blank" 
                       title="Buka di Tab Baru"
                       class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs transition">
                        <i class="fa-solid fa-up-right-from-square"></i>
                    </a>

                    <!-- Tutup Modal -->
                    <button type="button" 
                            @click="pdfModal = false" 
                            title="Tutup Pratinjau"
                            class="p-2 rounded-xl bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white border border-slate-700 text-xs transition cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body: Document Canvas with Zoom Scaling -->
            <div class="flex-1 bg-slate-800/90 overflow-auto p-4 sm:p-6 flex justify-center items-start">
                <div class="transition-all duration-200 origin-top bg-white shadow-2xl rounded-xl overflow-hidden border border-slate-700"
                     :style="`width: ${zoomLevel}%; max-width: ${zoomLevel > 100 ? 'none' : '900px'}; min-height: 100%;`">
                    <template x-if="pdfModal && ['jpg', 'jpeg', 'png'].includes(pdfFileExt)">
                        <img :src="pdfPreviewUrl" alt="Pratinjau Berkas SIP" class="w-full h-auto block mx-auto">
                    </template>
                    <template x-if="pdfModal && !['jpg', 'jpeg', 'png'].includes(pdfFileExt)">
                        <iframe :src="pdfPreviewUrl + '#toolbar=1&navpanes=0&view=FitH'" 
                                class="w-full h-[72vh] border-0 block bg-white"
                                title="Pratinjau Dokumen PDF SIP">
                        </iframe>
                    </template>
                </div>
            </div>

            <!-- Modal Footer: Direct Verification Actions -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0 text-xs">
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>Pastikan Nomor SIP, Nama Dokter, dan Tanggal Masa Berlaku pada dokumen sesuai dengan data pengajuan.</span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" 
                            @click="pdfModal = false" 
                            class="px-4 py-2 rounded-xl font-bold bg-slate-200/80 text-slate-700 hover:bg-slate-300/80 transition cursor-pointer">
                        Tutup
                    </button>

                    <template x-if="selectedIsPending">
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="openReject(selectedSip, selectedDoctorName, selectedNoSip, selectedDoctorWa)" 
                                    class="px-4 py-2 rounded-xl font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Tolak Berkas</span>
                            </button>
                            <button type="button" 
                                    @click="openApprove(selectedSip, selectedDoctorName, selectedNoSip, selectedDoctorWa)" 
                                    class="px-5 py-2 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-check"></i>
                                <span>Setujui Berkas Ini</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. MODAL FORM PERSETUJUAN (APPROVAL MODAL + WA GATEWAY NOTIFICATION)      -->
    <!-- ========================================================================= -->
    <div x-show="approveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-100" @click.away="approveModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5 text-emerald-800 font-extrabold text-base">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <span>Konfirmasi Persetujuan SIP</span>
                </div>
                <button type="button" @click="approveModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="p-3.5 bg-emerald-50/80 rounded-2xl border border-emerald-200 text-xs text-emerald-900 space-y-1">
                <div>Dokter Pemohon: <strong x-text="selectedDoctorName"></strong></div>
                <div>Nomor SIP Baru: <strong class="font-mono" x-text="selectedNoSip"></strong></div>
                <div class="flex items-center gap-1.5 text-emerald-700 pt-0.5">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Notifikasi WhatsApp ke: <strong class="font-mono" x-text="selectedDoctorWa || '-'"></strong></span>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Menyetujui permohonan ini akan mengaktifkan status legalitas SIP dokter menjadi <strong>🟢 Aman</strong> dan secara otomatis mengirimkan pesan WhatsApp pemberitahuan bahwa SIP telah aktif.
            </p>

            <form :action="'{{ url('/verifikasi-sip') }}/' + selectedSip + '/approve'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Verifikator SDM (Dikirimkan ke WA Dokter)</label>
                    <textarea name="catatan" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">Berkas SIP telah diperiksa dan dinyatakan sah serta memenuhi standar regulasi rumah sakit.</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="approveModal = false" class="px-4 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 flex items-center gap-1.5 transition cursor-pointer">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>Setujui & Kirim WA Dokter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. MODAL ALASAN PENOLAKAN BERKAS (REJECTION REASON MODAL + WA GATEWAY)    -->
    <!-- ========================================================================= -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-100" @click.away="rejectModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5 text-rose-800 font-extrabold text-base">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <span>Alasan Penolakan Berkas SIP</span>
                </div>
                <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Info Dokter & Nomor WA Tujuan -->
            <div class="p-3.5 bg-rose-50/80 rounded-2xl border border-rose-200 text-xs text-rose-900 space-y-1">
                <div>Dokter Pemohon: <strong x-text="selectedDoctorName"></strong></div>
                <div>Nomor SIP Diajukan: <strong class="font-mono" x-text="selectedNoSip"></strong></div>
                <div class="flex items-center gap-1.5 text-rose-700 pt-0.5">
                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                    <span>Pesan revisi otomatis dikirim ke WA: <strong class="font-mono" x-text="selectedDoctorWa || '-'"></strong></span>
                </div>
            </div>

            <form :action="'{{ url('/verifikasi-sip') }}/' + selectedSip + '/reject'" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- Pilihan Cepat Alasan Penolakan -->
                <div>
                    <label class="block font-bold text-slate-600 mb-1.5 text-[11px]">
                        Pilih Cepat Alasan Penolakan Umum (Klik untuk mengisi):
                    </label>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="reason in presetReasons" :key="reason">
                            <button type="button"
                                    @click="selectPresetReason(reason)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-700 border border-slate-200 hover:border-rose-200 text-[11px] font-semibold transition cursor-pointer text-left">
                                + <span x-text="reason"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Input Wajib Alasan Penolakan -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">
                            Catatan / Alasan Penolakan Berkas <span class="text-rose-600">* (Wajib Diisi)</span>
                        </label>
                        <span class="text-[10px] font-mono" :class="rejectionReason.trim().length >= 5 ? 'text-emerald-600 font-bold' : 'text-slate-400'" x-text="rejectionReason.trim().length + ' karakter'"></span>
                    </div>
                    <textarea name="alasan_penolakan" 
                              x-model="rejectionReason"
                              rows="4" 
                              required 
                              minlength="5"
                              placeholder="Tuliskan alasan penolakan secara jelas (misalnya: Masa berlaku tidak sesuai atau Hasil scan dokumen buram)..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                    <div class="mt-1.5 p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200/80 flex items-start gap-2 text-[11px] text-emerald-900">
                        <i class="fa-brands fa-whatsapp text-emerald-600 text-sm mt-0.5"></i>
                        <span>Catatan penolakan ini akan <strong>langsung dikirimkan otomatis ke WhatsApp dokter</strong> yang bersangkutan beserta tautan untuk mengunggah ulang berkas perbaikan.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">Batal</button>
                    <button type="submit" 
                            :disabled="rejectionReason.trim().length < 5"
                            class="px-5 py-2.5 rounded-xl font-bold bg-rose-600 text-white hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-rose-600/20 flex items-center gap-1.5 transition cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Tolak & Kirim Alasan ke WA Dokter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
