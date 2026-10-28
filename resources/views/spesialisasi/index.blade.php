@extends('layouts.app')

@section('title', 'Master Spesialisasi')
@section('page_title', 'Master Spesialisasi Dokter')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editId: null, editName: '' }">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Bidang Spesialisasi Medis</h2>
            <p class="text-xs text-slate-500">Master keahlian dan kompetensi dokter RSUD Dr. H. Moch. Ansari Saleh</p>
        </div>

        <button @click="createModal = true" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-700 transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Spesialisasi Baru</span>
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4 w-16">No</th>
                        <th class="px-5 py-4">Nama Spesialisasi</th>
                        <th class="px-5 py-4">Jumlah Dokter</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($spesialisasis as $idx => $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4 text-slate-400 font-semibold">{{ $spesialisasis->firstItem() + $idx }}</td>
                        <td class="px-5 py-4 font-bold text-slate-900 text-sm">
                            {{ $s->nama_spesialisasi }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-teal-50 text-teal-700 font-bold text-xs border border-teal-200">
                                <i class="fa-solid fa-stethoscope text-teal-600"></i>
                                {{ $s->dokters_count }} Dokter
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-1 whitespace-nowrap">
                            <button @click="editId = {{ $s->id }}; editName = '{{ addslashes($s->nama_spesialisasi) }}'; editModal = true;" class="p-2 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form action="{{ route('spesialisasi.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Hapus spesialisasi {{ $s->nama_spesialisasi }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada master data spesialisasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($spesialisasis->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $spesialisasis->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Create Spesialisasi -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="createModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="font-bold text-slate-900 text-base">Tambah Spesialisasi Baru</div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('spesialisasi.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Spesialisasi <span class="text-rose-600">*</span></label>
                    <input type="text" name="nama_spesialisasi" required placeholder="Contoh: Dokter Spesialis Bedah Umum (Sp.B)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white shadow-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Spesialisasi -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="editModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="font-bold text-slate-900 text-base">Perbarui Spesialisasi</div>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'/spesialisasi/' + editId" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Spesialisasi <span class="text-rose-600">*</span></label>
                    <input type="text" name="nama_spesialisasi" x-model="editName" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
