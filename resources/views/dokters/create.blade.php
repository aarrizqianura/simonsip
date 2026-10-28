@extends('layouts.app')

@section('title', 'Tambah Dokter Baru')
@section('page_title', 'Form Pendataan Awal Dokter Terpadu')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="{
        nipNik: '{{ old('nip_nik', '') }}',
        username: '{{ old('username', '') }}',
        password: '{{ old('password', $defaultPassword) }}',
        isStrSeumurHidup: {{ old('is_str_seumur_hidup', '1') == '1' ? 'true' : 'false' }},
        updateUsername() {
            this.username = this.nipNik;
        }
     }">

    <!-- Page Header & Guidance -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('dokters.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 mb-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Master Dokter</span>
            </a>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Form Pendataan Awal Dokter Terpadu</h2>
            <p class="text-xs text-slate-500">Pendaftaran biodata, legalitas SIP awal, dan pembuatan akun login otomatis dalam 1 langkah.</p>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <i class="fa-solid fa-shield-halved"></i> Terintegrasi Gateway WA
        </span>
    </div>

    <form action="{{ route('dokters.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- SECTION 1: INFORMASI AKUN LOGIN (USER CREDENTIAL) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-5">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-500/20">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">SECTION 1: INFORMASI AKUN LOGIN (USER CREDENTIAL)</h3>
                    <p class="text-xs text-slate-500">Kredensial akses dokter ke Portal SIMONSIP mandiri</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Username Login <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="username" 
                           x-model="username" 
                           required 
                           placeholder="Otomatis dari NIP/NIK" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-800 text-xs font-mono font-bold focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    <span class="text-[10px] text-slate-400 mt-1 block">Otomatis terisi dari input NIP / NIK</span>
                </div>

                <!-- Password Default -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Password Default <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="password" 
                           x-model="password" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-mono font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    <span class="text-[10px] text-slate-400 mt-1 block">Dapat diubah oleh dokter setelah login</span>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Peran Hak Akses
                    </label>
                    <div class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100/80 text-slate-700 text-xs font-bold flex items-center justify-between">
                        <span>Dokter (Staf Medis Mandiri)</span>
                        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Terkunci (Default role staf medis)</span>
                </div>
            </div>
        </div>

        <!-- SECTION 2: BIODATA & SPESIALISASI DOKTER -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-5">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-emerald-500/20">
                    2
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">SECTION 2: BIODATA & SPESIALISASI DOKTER</h3>
                    <p class="text-xs text-slate-500">Profil medis, nomor kontak WhatsApp, dan legalitas STR</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- NIP / NIK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        NIP / NIK Dokter <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nip_nik" 
                           x-model="nipNik" 
                           @input="updateUsername()" 
                           value="{{ old('nip_nik') }}"
                           required 
                           placeholder="Contoh: 198503152010011002" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                </div>

                <!-- Nama Lengkap beserta Gelar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap Beserta Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama_lengkap" 
                           value="{{ old('nama_lengkap') }}" 
                           required 
                           placeholder="Contoh: dr. H. Ahmad Fauzi, Sp.A" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-bold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                </div>

                <!-- Dropdown KSM -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kelompok Staf Medis (KSM) <span class="text-rose-500">*</span>
                    </label>
                    <select name="ksm_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                        <option value="">-- Pilih KSM --</option>
                        @foreach($ksms as $k)
                            <option value="{{ $k->id }}" {{ old('ksm_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_ksm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Spesialisasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Spesialisasi Dokter <span class="text-rose-500">*</span>
                    </label>
                    <select name="spesialisasi_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                        <option value="">-- Pilih Spesialisasi --</option>
                        @foreach($spesialisasis as $s)
                            <option value="{{ $s->id }}" {{ old('spesialisasi_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_spesialisasi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- No WhatsApp Aktif -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        No. WhatsApp Aktif <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600 font-bold text-xs">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </div>
                        <input type="text" 
                               name="no_whatsapp" 
                               value="{{ old('no_whatsapp') }}" 
                               required 
                               placeholder="08xxxxxxxxxx" 
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Tujuan pengiriman notifikasi & Early Warning Gateway</span>
                </div>

                <!-- Nomor STR & Checkbox Seumur Hidup -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Surat Tanda Registrasi (STR) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="no_str" 
                           value="{{ old('no_str') }}" 
                           required 
                           placeholder="Contoh: 3312100320145678" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    
                    <div class="mt-2.5 flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-emerald-800">
                            <input type="checkbox" 
                                   name="is_str_seumur_hidup" 
                                   value="1" 
                                   x-model="isStrSeumurHidup" 
                                   class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>STR Berlaku Seumur Hidup</span>
                        </label>
                    </div>

                    <!-- Input Tanggal Kadaluarsa STR jika bukan seumur hidup -->
                    <div x-show="!isStrSeumurHidup" x-cloak class="mt-2.5">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                            Tanggal Kadaluarsa STR
                        </label>
                        <input type="date" 
                               name="tgl_kadaluarsa_str" 
                               value="{{ old('tgl_kadaluarsa_str') }}" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: DATA LEGALITAS SIP AWAL (RSUD DR. H. MOCH. ANSARI SALEH) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-5">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-emerald-500/20">
                    3
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">SECTION 3: DATA LEGALITAS SIP AWAL (RSUD DR. H. MOCH. ANSARI SALEH)</h3>
                    <p class="text-xs text-slate-500">Nomor izin praktik, penentuan tempat praktik ke-, dan berkas pendukung</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nomor SIP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Surat Izin Praktik (SIP) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="no_sip" 
                           value="{{ old('no_sip') }}" 
                           required 
                           placeholder="Contoh: 503/0142/SIP-D/DPMPTSP/2024" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                </div>

                <!-- Praktik Ke- -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tempat Praktik Ke- <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl border border-emerald-300 bg-emerald-50/60 text-emerald-800 font-bold text-xs cursor-pointer hover:bg-emerald-100 transition">
                            <input type="radio" name="tempat_praktik_ke" value="1" {{ old('tempat_praktik_ke', '1') == '1' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                            <span>Praktik 1 (Ansari)</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs cursor-pointer hover:bg-slate-50 transition">
                            <input type="radio" name="tempat_praktik_ke" value="2" {{ old('tempat_praktik_ke') == '2' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                            <span>Praktik 2</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs cursor-pointer hover:bg-slate-50 transition">
                            <input type="radio" name="tempat_praktik_ke" value="3" {{ old('tempat_praktik_ke') == '3' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                            <span>Praktik 3</span>
                        </label>
                    </div>
                </div>

                <!-- Tanggal Terbit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Terbit SIP <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" 
                           name="tgl_terbit" 
                           value="{{ old('tgl_terbit', date('Y-m-d')) }}" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                </div>

                <!-- Tanggal Kadaluarsa (5 Tahun) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Kadaluarsa SIP (Masa 5 Tahun) <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" 
                           name="tgl_kadaluarsa" 
                           value="{{ old('tgl_kadaluarsa', date('Y-m-d', strtotime('+5 years'))) }}" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                </div>

                <!-- Berkas PDF SIP -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Unggah Berkas PDF SIP
                    </label>
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-5 text-center hover:border-emerald-500 transition bg-slate-50/50">
                        <i class="fa-solid fa-file-pdf text-3xl text-rose-500 mb-2"></i>
                        <input type="file" 
                               name="file_sip" 
                               accept=".pdf,.jpg,.jpeg,.png" 
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <span class="text-[10px] text-slate-400 mt-2 block">Format dokumen: PDF, JPG, PNG (Maksimal 10 MB)</span>
                    </div>
                </div>
            </div>

            <!-- Auto WhatsApp Notification Checkbox -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" 
                           name="send_wa" 
                           value="1" 
                           checked 
                           class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <div>
                        <div class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-emerald-600 text-sm"></i>
                            Kirim Kredensial Login & Link Akses via WhatsApp ke Dokter Otomatis
                        </div>
                        <div class="text-[11px] text-emerald-700">
                            Dokter akan langsung menerima pesan WhatsApp berisi Username, Password, dan Tautan Login SIMONSIP.
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Submit Button Action -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('dokters.index') }}" class="px-5 py-3 rounded-2xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition">
                Batalkan
            </a>
            <button type="submit" 
                    class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-xl shadow-emerald-600/30 transition transform active:scale-[0.99] flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>SIMPAN DATA & AKTIFKAN AKUN</span>
            </button>
        </div>

    </form>
</div>
@endsection
