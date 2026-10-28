@extends('layouts.dokter')

@section('title', 'Unggah Perpanjangan SIP')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Breadcrumb -->
    <div>
        <a href="{{ route('dokter.dashboard') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 flex items-center gap-1.5 mb-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Dashboard Saya</span>
        </a>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">1-Step Renewal: Perpanjangan SIP Dokter</h2>
        <p class="text-xs text-slate-500">Unggah berkas Surat Izin Praktik (SIP) baru Anda beserta metadata masa berlaku untuk diverifikasi oleh Bagian SDM Kepegawaian</p>
    </div>

    <!-- Instructions Box -->
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
            <i class="fa-solid fa-circle-info text-emerald-600"></i>
            Petunjuk Pengajuan Perpanjangan SIP:
        </div>
        <p class="text-[11px] leading-relaxed text-emerald-800">
            Pastikan scan dokumen SIP yang diunggah berupa file <strong>PDF</strong> atau foto beresolusi jelas (maksimal 10 MB). Setelah formulir ini dikirimkan, status SIP akan berubah menjadi <strong>🔵 Menunggu Verifikasi SDM</strong>. Anda akan menerima notifikasi WhatsApp setelah berkas disetujui.
        </p>
    </div>

    <!-- Form 1-Step Renewal -->
    <form action="{{ route('dokter.renewal.post') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-5">
        @csrf

        <!-- Info Pemohon -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between text-xs">
            <div>
                <div class="text-[11px] text-slate-400">Pemohon Tenaga Medis:</div>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $dokter->nama_lengkap }}</div>
                <div class="text-[11px] text-slate-500">NIP: {{ $dokter->nip_nik }} &bull; {{ $dokter->ksm?->nama_ksm }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-teal-50 text-teal-800 border border-teal-200 font-bold text-[10px]">
                STR: {{ $dokter->no_str }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Nomor SIP Baru -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nomor SIP Baru <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       name="no_sip" 
                       value="{{ old('no_sip') }}" 
                       required 
                       placeholder="Contoh: 503/0142/SIP-D/DPMPTSP/2026" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-mono font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
            </div>

            <!-- Tempat Praktik Ke- -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tempat Praktik Ke- <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-2xl border border-emerald-300 bg-emerald-50/50 text-emerald-900 font-bold text-xs cursor-pointer hover:bg-emerald-100 transition">
                        <input type="radio" name="tempat_praktik_ke" value="1" checked class="text-emerald-600 focus:ring-emerald-500">
                        <span>Praktik 1 (Ansari)</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-2xl border border-slate-200 text-slate-700 font-bold text-xs cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="tempat_praktik_ke" value="2" class="text-emerald-600 focus:ring-emerald-500">
                        <span>Praktik 2</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-2xl border border-slate-200 text-slate-700 font-bold text-xs cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="tempat_praktik_ke" value="3" class="text-emerald-600 focus:ring-emerald-500">
                        <span>Praktik 3</span>
                    </label>
                </div>
            </div>

            <!-- Tanggal Terbit -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tanggal Terbit SIP Baru <span class="text-rose-500">*</span>
                </label>
                <input type="date" 
                       name="tgl_terbit" 
                       value="{{ old('tgl_terbit', date('Y-m-d')) }}" 
                       required 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500">
            </div>

            <!-- Tanggal Kadaluarsa -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tanggal Kadaluarsa Baru (5 Tahun) <span class="text-rose-500">*</span>
                </label>
                <input type="date" 
                       name="tgl_kadaluarsa" 
                       value="{{ old('tgl_kadaluarsa', date('Y-m-d', strtotime('+5 years'))) }}" 
                       required 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500">
            </div>

            <!-- Berkas PDF Dokumen SIP -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Unggah Dokumen Berkas SIP (PDF) <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-3xl p-6 text-center transition bg-slate-50/50">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-emerald-600 mb-2"></i>
                    <div class="text-xs font-bold text-slate-700">Pilih berkas dari perangkat Anda</div>
                    <p class="text-[11px] text-slate-400 mt-0.5 mb-3">Format didukung: PDF, JPG, PNG (Maksimal 10 MB)</p>
                    <input type="file" 
                           name="file_sip" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           required 
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                </div>
            </div>

        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('dokter.dashboard') }}" class="px-5 py-3 rounded-2xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" 
                    class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-xl shadow-emerald-600/30 transition transform active:scale-95 flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>KIRIM PENGAJUAN PERPANJANGAN SIP</span>
            </button>
        </div>

    </form>

</div>
@endsection
