@extends('layouts.app')

@section('title', 'Matriks & Blast WhatsApp Gateway')
@section('page_title', 'WhatsApp Gateway & Early Warning Monitoring')

@section('content')
<div class="space-y-6" x-data="{ blastModal: false }">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-brands fa-whatsapp text-emerald-500 text-2xl"></i>
                <span>Matriks & Blast WhatsApp Gateway</span>
            </h2>
            <p class="text-xs text-slate-500">Pemantauan otomatisasi cron job harian (07.00 WITA) dan histori pengiriman pesan notifikasi legalitas SIP</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Trigger Cron Job Now -->
            <form action="{{ route('whatsapp.trigger') }}" method="POST" onsubmit="return confirm('Jalankan pengecekan status kadaluarsa SIP dan kirim pengingat WhatsApp sekarang?');">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white font-bold text-xs hover:bg-slate-900 transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-bolt text-amber-400"></i>
                    <span>Jalankan Cron Job Sekarang</span>
                </button>
            </form>

            <!-- Instant Blast Modal Trigger -->
            <button @click="blastModal = true" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-700 transition flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim Pesan Manual / Blast</span>
            </button>
        </div>
    </div>

    <!-- Gateway Status & Stats Banner -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Notifikasi</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Semua pesan terkirim</div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-200 bg-emerald-50/30">
            <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i> Berhasil (Sent)
            </div>
            <div class="text-2xl font-black text-emerald-700 mt-2">{{ $stats['sent'] }}</div>
            <div class="text-[11px] text-emerald-600 mt-0.5">Delivered ke WhatsApp</div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-rose-200 bg-rose-50/30">
            <div class="text-xs font-bold text-rose-700 uppercase tracking-wider flex items-center gap-1">
                <i class="fa-solid fa-circle-xmark"></i> Gagal (Failed)
            </div>
            <div class="text-2xl font-black text-rose-700 mt-2">{{ $stats['failed'] }}</div>
            <div class="text-[11px] text-rose-600 mt-0.5">Kendala nomor / API</div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-sky-200 bg-sky-50/30">
            <div class="text-xs font-bold text-sky-700 uppercase tracking-wider flex items-center gap-1">
                <i class="fa-solid fa-calendar-day"></i> Dikirim Hari Ini
            </div>
            <div class="text-2xl font-black text-sky-700 mt-2">{{ $stats['today'] }}</div>
            <div class="text-[11px] text-sky-600 mt-0.5">Periode hari ini</div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 col-span-2 md:col-span-1">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gateway Engine</div>
            <div class="text-base font-extrabold text-slate-800 mt-2 flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Fonnte / Multi-WA</span>
            </div>
            <div class="text-[10px] text-emerald-700 font-semibold mt-0.5">Mode Simulasi & Live API</div>
        </div>

    </div>

    <!-- Filter Toolbar -->
    <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-200">
        <form action="{{ route('whatsapp.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            
            <div class="sm:col-span-6 relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama dokter, no. HP tujuan, atau teks pesan..." class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-emerald-500">
            </div>

            <div class="sm:col-span-3">
                <select name="message_type" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300">
                    <option value="">-- Semua Jenis Pesan --</option>
                    <option value="account_created" {{ $type == 'account_created' ? 'selected' : '' }}>Notifikasi Akun Baru</option>
                    <option value="early_warning" {{ $type == 'early_warning' ? 'selected' : '' }}>Early Warning (61-90 Hari)</option>
                    <option value="urgent" {{ $type == 'urgent' ? 'selected' : '' }}>Peringatan Mendesak (≤60 Hari)</option>
                    <option value="verifikasi_approved" {{ $type == 'verifikasi_approved' ? 'selected' : '' }}>Verifikasi Disetujui</option>
                    <option value="verifikasi_rejected" {{ $type == 'verifikasi_rejected' ? 'selected' : '' }}>Verifikasi Ditolak</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300">
                    <option value="">-- Semua Status --</option>
                    <option value="sent" {{ $status == 'sent' ? 'selected' : '' }}>Sent (Berhasil)</option>
                    <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Failed (Gagal)</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                    Filter
                </button>
            </div>

        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Waktu & Kanal</th>
                        <th class="px-4 py-4">Dokter & No. Tujuan</th>
                        <th class="px-4 py-4">Jenis Notifikasi</th>
                        <th class="px-4 py-4">Rincian Payload Pesan</th>
                        <th class="px-4 py-4">Status Pengiriman</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition">
                        
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                            <div class="text-[11px] text-slate-500">{{ $log->created_at->format('H:i:s') }} WITA</div>
                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-semibold mt-1">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                            </span>
                        </td>

                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-800">
                                {{ $log->sipRecord?->dokter?->nama_lengkap ?? 'SDM / Dokter' }}
                            </div>
                            <div class="text-[11px] font-mono text-emerald-700 font-semibold mt-0.5">
                                {{ $log->recipient_phone }}
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $log->message_type_label }}
                            </span>
                        </td>

                        <td class="px-4 py-4 max-w-md">
                            <div class="text-slate-700 text-xs line-clamp-2 bg-slate-50 p-2 rounded-xl border border-slate-100 font-sans" title="{{ $log->payload }}">
                                {{ $log->payload }}
                            </div>
                        </td>

                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $log->status === 'sent' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                <i class="fa-solid {{ $log->status === 'sent' ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                {{ strtoupper($log->status) }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <form action="{{ route('whatsapp.resend', $log) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Kirim Ulang Pesan" class="p-2 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 transition">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-brands fa-whatsapp text-4xl text-slate-300 mb-2"></i>
                            <div>Tidak ada log pesan WhatsApp yang sesuai kriteria filter.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form Instant Blast WhatsApp -->
    <div x-show="blastModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="blastModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i>
                    <span>Kirim Pesan WhatsApp Manual / Blast</span>
                </div>
                <button @click="blastModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('whatsapp.manual') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Dokter Tujuan (Opsional)</label>
                    <select onchange="if(this.value){ const opt=this.options[this.selectedIndex]; document.getElementById('blastPhone').value = opt.getAttribute('data-phone'); document.getElementById('blastSipId').value = opt.getAttribute('data-sip'); }" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        <option value="">-- Masukkan Manual atau Pilih dari Dokter Terdaftar --</option>
                        @foreach($allDokters as $d)
                            <option value="{{ $d->id }}" data-phone="{{ $d->no_whatsapp }}" data-sip="{{ $d->primarySip?->id ?? '' }}">
                                {{ $d->nama_lengkap }} ({{ $d->no_whatsapp }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="sip_record_id" id="blastSipId" value="">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp Tujuan <span class="text-rose-600">*</span></label>
                    <input type="text" name="phone" id="blastPhone" required placeholder="08xxxxxxxxxx atau 628xxxx" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Isi Pesan WhatsApp <span class="text-rose-600">*</span></label>
                    <textarea name="message" rows="5" required placeholder="Tuliskan pesan resmi dari SIMONSIP RSUD Dr. H. Moch. Ansari Saleh..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="blastModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim WhatsApp Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
