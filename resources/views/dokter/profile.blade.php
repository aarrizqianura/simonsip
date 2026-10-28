@extends('layouts.dokter')

@section('title', 'Profil & Pengaturan Akun')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <a href="{{ route('dokter.dashboard') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 flex items-center gap-1.5 mb-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Dashboard Saya</span>
        </a>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Profil Saya & Ubah Kata Sandi</h2>
        <p class="text-xs text-slate-500">Kelola keamanan kredensial akun login Portal SIMONSIP Anda</p>
    </div>

    <!-- Doctor Info Card -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
        <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-xl shadow-md">
                dr
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">{{ $dokter->nama_lengkap }}</h3>
                <div class="text-xs text-slate-500 mt-0.5">
                    NIP / NIK: <strong class="font-mono text-slate-800">{{ $dokter->nip_nik }}</strong>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <div class="text-slate-400">KSM Penempatan</div>
                <div class="font-bold text-slate-800 mt-0.5">{{ $dokter->ksm?->nama_ksm ?? '-' }}</div>
            </div>
            <div>
                <div class="text-slate-400">Bidang Spesialisasi</div>
                <div class="font-bold text-slate-800 mt-0.5">{{ $dokter->spesialisasi?->nama_spesialisasi ?? '-' }}</div>
            </div>
            <div>
                <div class="text-slate-400">No. STR</div>
                <div class="font-bold text-slate-800 mt-0.5 font-mono">{{ $dokter->no_str }}</div>
                <div class="text-[10px] text-teal-700 font-semibold">{{ $dokter->str_status_text }}</div>
            </div>
            <div>
                <div class="text-slate-400">Nomor WhatsApp Aktif</div>
                <div class="font-bold text-slate-800 mt-0.5 font-mono">{{ $dokter->no_whatsapp }}</div>
            </div>
        </div>
    </div>

    <!-- Form Ganti Password -->
    <form action="{{ route('dokter.password.update') }}" method="POST" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-4">
        @csrf

        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <i class="fa-solid fa-key text-emerald-600"></i>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Perbarui Kata Sandi</h3>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
            </label>
            <input type="password" name="password_lama" required placeholder="Masukkan kata sandi lama Anda" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:border-emerald-500">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Kata Sandi Baru <span class="text-rose-500">*</span>
            </label>
            <input type="password" name="password_baru" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:border-emerald-500">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
            </label>
            <input type="password" name="password_baru_confirmation" required placeholder="Ulangi kata sandi baru" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:border-emerald-500">
        </div>

        <div class="pt-2 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                Simpan Kata Sandi Baru
            </button>
        </div>
    </form>

</div>
@endsection
