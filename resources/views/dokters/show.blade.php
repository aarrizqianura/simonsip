@extends('layouts.app')

@section('title', $dokter->nama_lengkap)
@section('page_title', 'Profil Dokter & Legalitas Multi-SIP')

@section('content')
<div class="space-y-6" x-data="{ addSipModal: false, waModal: false }">
    
    <!-- Top Action Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('dokters.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 mb-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Master Dokter</span>
            </a>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $dokter->nama_lengkap }}</h2>
            <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                <span>NIP / NIK: <strong>{{ $dokter->nip_nik }}</strong></span>
                <span>&bull;</span>
                <span class="text-emerald-700 font-semibold">{{ $dokter->ksm?->nama_ksm }}</span>
                <span>&bull;</span>
                <span>{{ $dokter->spesialisasi?->nama_spesialisasi }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button @click="waModal = true" class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                <span>Kirim Pesan WA</span>
            </button>

            <button @click="addSipModal = true" class="px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-plus text-indigo-600"></i>
                <span>Tambah Multi-SIP (Praktik 2/3)</span>
            </button>

            <a href="{{ route('dokters.edit', $dokter) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 text-white hover:bg-slate-900 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Profil</span>
            </a>
        </div>
    </div>

    <!-- Doctor Info & STR Overview Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Profile & Account Credentials -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identitas & Akun Login</span>
                    <span class="text-[10px] font-bold bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-200">
                        {{ $dokter->user?->role_label ?? 'Dokter' }}
                    </span>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <div class="text-[11px] text-slate-400">Username Akses</div>
                        <div class="font-mono font-bold text-slate-900 text-sm">{{ $dokter->user?->username ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Nomor WhatsApp</div>
                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $dokter->no_whatsapp) }}" target="_blank" class="hover:underline text-emerald-700">
                                {{ $dokter->no_whatsapp }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Status Akun</div>
                        <div class="text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                            <i class="fa-solid fa-circle-check"></i> Aktif Terdaftar
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                Terdaftar: {{ $dokter->created_at?->translatedFormat('d F Y') }}
            </div>
        </div>

        <!-- STR Status Card -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Surat Tanda Registrasi (STR)</span>
                    @if($dokter->is_str_seumur_hidup)
                        <span class="text-[10px] font-bold bg-teal-50 text-teal-700 px-2.5 py-0.5 rounded-full border border-teal-200">
                            <i class="fa-solid fa-infinity"></i> Seumur Hidup
                        </span>
                    @else
                        <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2.5 py-0.5 rounded-full border border-amber-200">
                            Berlaku Hingga
                        </span>
                    @endif
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <div class="text-[11px] text-slate-400">Nomor Registrasi STR</div>
                        <div class="font-mono font-bold text-slate-900 text-sm">{{ $dokter->no_str }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Masa Berlaku STR</div>
                        <div class="font-bold text-slate-800">
                            {{ $dokter->str_status_text }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Regulasi Konsil Kedokteran</div>
                        <div class="text-slate-600 text-[11px]">
                            UU Kesehatan No. 17 Tahun 2023 (STR Seumur Hidup)
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                Data divalidasi kepegawaian
            </div>
        </div>

        <!-- Compliance & Overall SIP Status -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Legalitas Keseluruhan</span>
                    <span class="text-[10px] font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">
                        {{ $dokter->sipRecords->count() }} SIP Terdaftar
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @php
                        $ovStatus = $dokter->overall_status;
                        $primarySip = $dokter->primarySip;
                    @endphp
                    <div>
                        <div class="text-[11px] text-slate-400">Status Praktik Utama (Ansari Saleh)</div>
                        @if($primarySip)
                            <div class="mt-1">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $primarySip->status_badge_class }}">
                                    {{ $primarySip->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-600 mt-2">
                                Sisa Waktu: <strong class="{{ $primarySip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-800' }}">{{ $primarySip->sisa_hari }} Hari</strong> (s/d {{ $primarySip->tgl_kadaluarsa->translatedFormat('d F Y') }})
                            </div>
                        @else
                            <div class="text-xs text-rose-500 font-semibold mt-1">Belum Terdaftar SIP Praktik 1</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                Peringatan dini aktif via WhatsApp
            </div>
        </div>

    </div>

    <!-- Multi-SIP Records List Cards -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-hospital-user text-emerald-600"></i>
                <span>Daftar Record Multi-SIP Dokter</span>
            </h3>
            <span class="text-xs text-slate-500">Maksimal 3 Tempat Praktik sesuai Regulasi Kemenkes</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @for($p = 1; $p <= 3; $p++)
            @php
                $sip = $dokter->sipRecords->where('tempat_praktik_ke', $p)->first();
            @endphp
            <div class="bg-white rounded-3xl p-6 shadow-sm border {{ $sip ? 'border-slate-200' : 'border-dashed border-slate-300 bg-slate-50/50' }} flex flex-col justify-between relative overflow-hidden">
                
                @if($sip)
                    <!-- Header Record -->
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-lg {{ $p === 1 ? 'bg-emerald-100 text-emerald-800 font-black' : 'bg-slate-100 text-slate-700' }}">
                                Praktik Ke-{{ $p }} {{ $p === 1 ? '(RSUD Ansari Saleh)' : '' }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sip->status_badge_class }}">
                                {{ $sip->status_label }}
                            </span>
                        </div>

                        <div class="mt-4 space-y-3 text-xs">
                            <div>
                                <div class="text-[11px] text-slate-400">Nomor SIP</div>
                                <div class="font-mono font-bold text-slate-900 text-sm mt-0.5">{{ $sip->no_sip }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="text-[11px] text-slate-400">Tanggal Terbit</div>
                                    <div class="font-semibold text-slate-700">{{ $sip->tgl_terbit->translatedFormat('d M Y') }}</div>
                                </div>
                                <div>
                                    <div class="text-[11px] text-slate-400">Tanggal Kadaluarsa</div>
                                    <div class="font-bold {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-900' }}">
                                        {{ $sip->tgl_kadaluarsa->translatedFormat('d M Y') }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-400">Sisa Masa Berlaku</div>
                                <div class="text-sm font-extrabold {{ $sip->sisa_hari <= 30 ? 'text-rose-600' : ($sip->sisa_hari <= 90 ? 'text-amber-600' : 'text-emerald-700') }}">
                                    {{ $sip->sisa_hari < 0 ? 'Kadaluarsa ' . abs($sip->sisa_hari) . ' hari lalu' : $sip->sisa_hari . ' Hari Tersisa' }}
                                </div>
                            </div>

                            @if($sip->catatan_verifikasi)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600">
                                <strong>Catatan SDM:</strong> {{ $sip->catatan_verifikasi }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Action Card -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs mt-4">
                        <a href="{{ asset('storage/' . $sip->file_sip_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-600 hover:text-emerald-800 font-bold">
                            <i class="fa-solid fa-file-pdf text-rose-500 text-sm"></i>
                            <span>Buka Berkas PDF</span>
                        </a>

                        <span class="text-[10px] text-slate-400">
                            {{ $sip->is_verified ? 'Terverifikasi' : 'Pending Approval' }}
                        </span>
                    </div>

                @else
                    <!-- Empty Slot for Multi-SIP -->
                    <div class="text-center py-10 space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                            <i class="fa-solid fa-hospital"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-700 text-sm">Praktik Ke-{{ $p }} Kosong</div>
                            <div class="text-xs text-slate-400 mt-0.5">Dokter belum mendaftarkan SIP di tempat praktik ke-{{ $p }}</div>
                        </div>
                        <button @click="addSipModal = true" class="px-3.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs transition">
                            + Daftarkan SIP Ke-{{ $p }}
                        </button>
                    </div>
                @endif

            </div>
            @endfor
        </div>
    </div>

    <!-- WhatsApp Audit Log for this Doctor -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-emerald-500 text-lg"></i>
                    <span>Log Pengiriman Notifikasi WhatsApp ke Dokter</span>
                </h3>
                <p class="text-xs text-slate-500">Histori pesan reminder otomatis dan blast yang terkirim ke {{ $dokter->no_whatsapp }}</p>
            </div>
            <button @click="waModal = true" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition">
                Kirim WA Baru
            </button>
        </div>

        @php
            $doctorLogs = \App\Models\NotificationLog::whereHas('sipRecord', fn($q) => $q->where('dokter_id', $dokter->id))->latest()->get();
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Waktu Pengiriman</th>
                        <th class="px-4 py-3">Jenis Pesan</th>
                        <th class="px-4 py-3">Isi Pesan</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($doctorLogs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">
                            {{ $log->created_at->translatedFormat('d M Y, H:i') }} WITA
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $log->message_type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 max-w-md">
                            <div class="truncate">{{ $log->payload }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="font-bold {{ $log->status === 'sent' ? 'text-emerald-600' : 'text-rose-600' }}">
                                <i class="fa-solid fa-check mr-1"></i>{{ strtoupper($log->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-400">
                            Belum ada pesan WhatsApp yang dikirimkan ke dokter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah SIP Tambahan -->
    <div x-show="addSipModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="addSipModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i class="fa-solid fa-file-circle-plus text-emerald-600"></i>
                    <span>Tambah SIP Praktik Ke-2 / Ke-3</span>
                </div>
                <button @click="addSipModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('dokters.sip.store', $dokter) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor SIP Baru</label>
                    <input type="text" name="no_sip" required placeholder="503/xxxx/SIP-D/..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tempat Praktik Ke-</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 p-2 rounded-xl border border-slate-300 cursor-pointer text-slate-700 font-bold">
                            <input type="radio" name="tempat_praktik_ke" value="2" checked class="text-emerald-600">
                            <span>Praktik 2</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2 rounded-xl border border-slate-300 cursor-pointer text-slate-700 font-bold">
                            <input type="radio" name="tempat_praktik_ke" value="3" class="text-emerald-600">
                            <span>Praktik 3</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Terbit</label>
                        <input type="date" name="tgl_terbit" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kadaluarsa</label>
                        <input type="date" name="tgl_kadaluarsa" required value="{{ date('Y-m-d', strtotime('+5 years')) }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berkas Dokumen SIP (PDF/Gambar)</label>
                    <input type="file" name="file_sip" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="addSipModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md">Simpan SIP</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Instant WhatsApp Message -->
    <div x-show="waModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="waModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i>
                    <span>Kirim Pesan WhatsApp ke {{ $dokter->nama_lengkap }}</span>
                </div>
                <button @click="waModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('whatsapp.manual') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="phone" value="{{ $dokter->no_whatsapp }}">
                @if($primarySip)
                    <input type="hidden" name="sip_record_id" value="{{ $primarySip->id }}">
                @endif

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Tujuan WhatsApp</label>
                    <div class="p-2.5 bg-slate-50 rounded-xl font-mono text-slate-800 font-bold border border-slate-200">
                        {{ $dokter->no_whatsapp }}
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Teks Pesan WhatsApp</label>
                    <textarea name="message" rows="5" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500">Yth. {{ $dokter->nama_lengkap }}, ini adalah pesan resmi dari Bagian SDM RSUD Dr. H. Moch. Ansari Saleh Banjarmasin terkait masa berlaku legalitas SIP Anda. Mohon konfirmasi atau unggah pembaharuan berkas di portal SIMONSIP.</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="waModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Kirim Pesan Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
