@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Akun Pengguna & Hak Akses')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editUser: {} }">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pengguna Sistem SIMONSIP</h2>
            <p class="text-xs text-slate-500">Kelola kredensial akun administrator IT, staf SDM, dan akun staf medis dokter</p>
        </div>

        <button @click="createModal = true" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-700 transition flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah Administrator Baru</span>
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Nama Lengkap</th>
                        <th class="px-4 py-4">Username Login</th>
                        <th class="px-4 py-4">Peran (Role)</th>
                        <th class="px-4 py-4">Email Terdaftar</th>
                        <th class="px-4 py-4">Dibuat Pada</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $u->name }}</div>
                            @if($u->dokter)
                                <div class="text-[11px] text-emerald-700 font-semibold">Terkait Profil Dokter (NIP: {{ $u->dokter->nip_nik }})</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-mono font-bold text-slate-800">
                            {{ $u->username }}
                        </td>
                        <td class="px-4 py-4">
                            @if($u->role === 'super_admin')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Super Admin (IT/SIRS)
                                </span>
                            @elseif($u->role === 'admin_sdm')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Admin SDM Kepegawaian
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Dokter
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-600">
                            {{ $u->email ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-slate-500">
                            {{ $u->created_at?->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4 text-right space-x-1 whitespace-nowrap">
                            <button @click="editUser = { id: {{ $u->id }}, name: '{{ addslashes($u->name) }}', username: '{{ $u->username }}', email: '{{ $u->email }}', role: '{{ $u->role }}' }; editModal = true;" class="p-2 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun pengguna {{ $u->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">Tidak ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Create User -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="createModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="font-bold text-slate-900 text-base">Tambah Akun Pengguna Baru</div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="name" required placeholder="Nama Petugas" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username Login</label>
                    <input type="text" name="username" required placeholder="username_login" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alamat Email (Opsional)</label>
                    <input type="email" name="email" placeholder="email@ansarisaleh.kalselprov.go.id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Peran (Role)</label>
                    <select name="role" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                        <option value="admin_sdm">Admin SDM (Kepegawaian)</option>
                        <option value="super_admin">Super Admin (IT/SIRS)</option>
                        <option value="dokter">Dokter (Staf Medis)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kata Sandi</label>
                    <input type="password" name="password" required value="Ansari@2026" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl font-bold bg-slate-100 text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-emerald-600 text-white shadow-md">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="editModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="font-bold text-slate-900 text-base">Perbarui Akun Pengguna</div>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'/users/' + editUser.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="editUser.name" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username Login</label>
                    <input type="text" name="username" x-model="editUser.username" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" x-model="editUser.email" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Peran (Role)</label>
                    <select name="role" x-model="editUser.role" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                        <option value="admin_sdm">Admin SDM (Kepegawaian)</option>
                        <option value="super_admin">Super Admin (IT/SIRS)</option>
                        <option value="dokter">Dokter (Staf Medis)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono">
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
