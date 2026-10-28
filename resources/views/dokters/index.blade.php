@extends('layouts.app')

@section('title', 'Master Data Dokter')
@section('page_title', 'Master Data Profil Dokter & Multi-SIP')

@section('content')
<div class="space-y-6" x-data="{ importModal: false }">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Dokter RSUD Dr. H. Moch. Ansari Saleh</h2>
            <p class="text-xs text-slate-500">Kelola master data staf medis, status STR, dan legalitas Multi-SIP</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Export CSV -->
            <a href="{{ route('dokters.export') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Ekspor CSV</span>
            </a>

            <!-- Import Excel Modal Trigger -->
            <button @click="importModal = true" class="px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-file-import text-teal-600"></i>
                <span>Impor Massal</span>
            </button>

            <!-- Tambah Dokter Baru -->
            <a href="{{ route('dokters.create') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-700 transition flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Dokter & Akun Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200">
        <form action="{{ route('dokters.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            
            <!-- Search Text -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari Nama Dokter, NIP/NIK, No. STR, atau No. SIP..." 
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 text-slate-800">
            </div>

            <!-- Filter KSM -->
            <div class="sm:col-span-3">
                <select name="ksm_id" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 text-slate-800">
                    <option value="">-- Semua KSM --</option>
                    @foreach($ksms as $k)
                        <option value="{{ $k->id }}" {{ $ksmId == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_ksm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status SIP -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 text-slate-800">
                    <option value="">-- Semua Status SIP --</option>
                    <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>🟢 Aman (> 90 Hari)</option>
                    <option value="warning" {{ $status == 'warning' ? 'selected' : '' }}>🟡 Peringatan (61–90 Hari)</option>
                    <option value="mendesak" {{ $status == 'mendesak' ? 'selected' : '' }}>🟠 Mendesak (31–60 Hari)</option>
                    <option value="expired" {{ $status == 'expired' ? 'selected' : '' }}>🔴 Kadaluarsa (≤ 30 Hari)</option>
                    <option value="menunggu_verifikasi" {{ $status == 'menunggu_verifikasi' ? 'selected' : '' }}>🔵 Menunggu Verifikasi</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex items-center gap-1">
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                @if($search || $ksmId || $status)
                    <a href="{{ route('dokters.index') }}" class="p-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Doctors Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Dokter & Kredensial</th>
                        <th class="px-4 py-4">KSM & Spesialisasi</th>
                        <th class="px-4 py-4">Status STR</th>
                        <th class="px-4 py-4">Status Legalitas SIP (Ansari Saleh)</th>
                        <th class="px-4 py-4">Multi-SIP</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($dokters as $d)
                    @php
                        $primarySip = $d->sipRecords->where('tempat_praktik_ke', 1)->first() ?? $d->sipRecords->first();
                        $sipCount = $d->sipRecords->count();
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        
                        <!-- Nama & NIP -->
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($d->nama_lengkap, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('dokters.show', $d) }}" class="font-bold text-slate-900 hover:text-emerald-700 text-sm">
                                        {{ $d->nama_lengkap }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>NIP: <strong>{{ $d->nip_nik }}</strong></span>
                                        <span>&bull;</span>
                                        <span>WA: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $d->no_whatsapp) }}" target="_blank" class="text-emerald-600 hover:underline"><i class="fa-brands fa-whatsapp"></i> {{ $d->no_whatsapp }}</a></span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- KSM & Spesialisasi -->
                        <td class="px-4 py-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200/60 font-semibold text-[11px]">
                                {{ $d->ksm?->nama_ksm ?? '-' }}
                            </span>
                            <div class="text-[11px] text-slate-500 mt-1">
                                {{ $d->spesialisasi?->nama_spesialisasi ?? '-' }}
                            </div>
                        </td>

                        <!-- Status STR -->
                        <td class="px-4 py-4">
                            @if($d->is_str_seumur_hidup)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                    <i class="fa-solid fa-infinity text-[11px]"></i> Seumur Hidup
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                    s/d {{ $d->tgl_kadaluarsa_str?->format('d/m/Y') }}
                                </span>
                            @endif
                            <div class="text-[10px] text-slate-400 mt-1">
                                No. {{ $d->no_str }}
                            </div>
                        </td>

                        <!-- Status SIP Utama -->
                        <td class="px-4 py-4">
                            @if($primarySip)
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $primarySip->status_badge_class }}">
                                        {{ $primarySip->status_label }}
                                    </span>
                                    <div class="text-[11px] font-semibold text-slate-700 mt-1">
                                        No: {{ $primarySip->no_sip }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        Exp: {{ $primarySip->tgl_kadaluarsa?->translatedFormat('d M Y') }}
                                        <strong class="{{ $primarySip->sisa_hari <= 30 ? 'text-rose-600' : 'text-slate-700' }}">({{ $primarySip->sisa_hari }} hari)</strong>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-rose-500 font-semibold italic">Belum Ada SIP</span>
                            @endif
                        </td>

                        <!-- Multi-SIP Badge -->
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold {{ $sipCount > 1 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600' }}">
                                <i class="fa-solid fa-hospital text-[10px]"></i> {{ $sipCount }} Praktik
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="px-5 py-4 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('dokters.show', $d) }}" title="Lihat Profil & SIP Lengkap" class="p-2 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 transition">
                                <i class="fa-solid fa-folder-open"></i>
                            </a>
                            <a href="{{ route('dokters.edit', $d) }}" title="Edit Biodata Dokter" class="p-2 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('dokters.destroy', $d) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dokter {{ $d->nama_lengkap }} beserta akun login dan berkas SIP miliknya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus Dokter" class="p-2 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-user-doctor text-4xl text-slate-300 mb-2"></i>
                            <div>Tidak ada data dokter yang ditemukan sesuai kriteria pencarian.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dokters->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $dokters->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Impor Massal CSV -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="importModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i class="fa-solid fa-file-import text-emerald-600"></i>
                    <span>Impor Massal Data Dokter</span>
                </div>
                <button @click="importModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Unggah file berformat <strong>CSV (Pemisah titik koma / semicolon ';')</strong> dengan urutan kolom:<br>
                <code class="block bg-slate-100 p-2 rounded-lg text-[11px] text-slate-800 mt-2 font-mono">
                    NIP;Nama Dokter;KSM;Spesialisasi;No WA;No STR;No SIP;Tgl Exp SIP (YYYY-MM-DD)
                </code>
            </p>

            <form action="{{ route('dokters.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-emerald-500 transition">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-emerald-600 mb-2"></i>
                    <input type="file" name="file_import" accept=".csv,.txt" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="importModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md">
                        Proses Impor
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
