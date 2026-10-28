@extends('layouts.app')

@section('title', 'Edit Data Dokter')
@section('page_title', 'Perbarui Data Profil Dokter')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" 
     x-data="{ 
        isStrSeumurHidup: {{ old('is_str_seumur_hidup', $dokter->is_str_seumur_hidup ? '1' : '0') == '1' ? 'true' : 'false' }} 
     }">

    <div>
        <a href="{{ route('dokters.show', $dokter) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 mb-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Detail Dokter</span>
        </a>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Edit Data {{ $dokter->nama_lengkap }}</h2>
        <p class="text-xs text-slate-500">Perbarui informasi profil dokter, KSM, spesialisasi, atau reset password login</p>
    </div>

    <form action="{{ route('dokters.update', $dokter) }}" method="POST" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- NIP / NIK (Readonly) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIP / NIK Dokter</label>
                <input type="text" value="{{ $dokter->nip_nik }}" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs font-bold">
                <span class="text-[10px] text-slate-400 mt-1 block">NIP tidak dapat diubah (kunci identitas)</span>
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $dokter->nama_lengkap) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-bold focus:border-emerald-500">
            </div>

            <!-- KSM -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelompok Staf Medis (KSM) <span class="text-rose-500">*</span></label>
                <select name="ksm_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500">
                    @foreach($ksms as $k)
                        <option value="{{ $k->id }}" {{ old('ksm_id', $dokter->ksm_id) == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_ksm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Spesialisasi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Spesialisasi <span class="text-rose-500">*</span></label>
                <select name="spesialisasi_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-medium focus:border-emerald-500">
                    @foreach($spesialisasis as $s)
                        <option value="{{ $s->id }}" {{ old('spesialisasi_id', $dokter->spesialisasi_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->nama_spesialisasi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- No WhatsApp -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp Aktif <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600 font-bold text-xs">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                    </div>
                    <input type="text" name="no_whatsapp" value="{{ old('no_whatsapp', $dokter->no_whatsapp) }}" required class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:border-emerald-500">
                </div>
            </div>

            <!-- Nomor STR -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor STR <span class="text-rose-500">*</span></label>
                <input type="text" name="no_str" value="{{ old('no_str', $dokter->no_str) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:border-emerald-500">
                
                <div class="mt-2 flex items-center gap-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-emerald-800">
                        <input type="checkbox" name="is_str_seumur_hidup" value="1" x-model="isStrSeumurHidup" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                        <span>STR Seumur Hidup</span>
                    </label>
                </div>

                <div x-show="!isStrSeumurHidup" x-cloak class="mt-2">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Kadaluarsa STR</label>
                    <input type="date" name="tgl_kadaluarsa_str" value="{{ old('tgl_kadaluarsa_str', $dokter->tgl_kadaluarsa_str?->format('Y-m-d')) }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
            </div>

            <!-- Reset Password Login (Opsional) -->
            <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Reset Kata Sandi Login (Opsional)
                </label>
                <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah kata sandi dokter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs focus:border-emerald-500">
                <span class="text-[10px] text-slate-400 mt-1 block">Minimal 6 karakter jika diisi</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('dokters.show', $dokter) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
