@extends('layouts.admin')

@section('title', 'Kelola Petugas & Pengguna')
@section('page_title', 'Manajemen Petugas RT & Administrator')

@section('content')
<div class="space-y-6" x-data="{ 
    createModal: false, 
    editModal: false, 
    resetModal: false,
    selectedUser: null,
    editUser: { id: '', name: '', email: '', phone: '', role: 'petugas', status: 'active', rts: [] },
    editRtIds: [],
    customPassword: 'hippam',
    openEdit(user) {
        this.editUser = { ...user };
        this.editRtIds = user.rts ? user.rts.map(r => r.id) : (user.rt_id ? [user.rt_id] : []);
        this.editModal = true;
    },
    openReset(user) {
        this.selectedUser = user;
        this.customPassword = 'hippam';
        this.resetModal = true;
    }
}">

    <!-- Top Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Petugas RT -->
        <div class="bg-white rounded-2xl p-5 border-2 border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Petugas RT Terdaftar</span>
                <span class="text-2xl font-black text-teal-700 font-mono mt-1 block">{{ $totalPetugas }} Akun</span>
                <span class="text-[11px] text-slate-400 font-medium">34 RT aktif di 3 Dusun (Pateguhan, Gentong, Bendrong)</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Card 2: Password Default Alert -->
        <div class="bg-white rounded-2xl p-5 border-2 {{ $petugasDefaultPassword > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200' }} shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold {{ $petugasDefaultPassword > 0 ? 'text-amber-800' : 'text-slate-500' }} uppercase tracking-wider block">Password Masih Default</span>
                <span class="text-2xl font-black {{ $petugasDefaultPassword > 0 ? 'text-amber-700' : 'text-slate-700' }} font-mono mt-1 block">{{ $petugasDefaultPassword }} Akun</span>
                <span class="text-[11px] {{ $petugasDefaultPassword > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">
                    {{ $petugasDefaultPassword > 0 ? 'Perlu diganti sebelum go-live!' : 'Semua password aman' }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl {{ $petugasDefaultPassword > 0 ? 'bg-amber-100 text-amber-700 border border-amber-300' : 'bg-slate-100 text-slate-500 border border-slate-200' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <!-- Card 3: Super Admin -->
        <div class="bg-white rounded-2xl p-5 border-2 border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Super Administrator</span>
                <span class="text-2xl font-black text-indigo-700 font-mono mt-1 block">{{ $totalAdmin }} Akun</span>
                <span class="text-[11px] text-slate-400 font-medium">Akses penuh lintas wilayah RT</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filters & Action Bar -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md p-4 sm:p-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900">Kelola Akun Petugas &amp; Admin</h3>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Lengkapi kontak asli petugas RT, atur wilayah tugas 3 dusun, atau reset kata sandi</p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <button @click="createModal = true" 
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 active:scale-95 rounded-xl shadow-md transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Tambah Pengguna Baru</span>
                </button>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 mt-4 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Cari Pengguna</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / Email / No HP..." 
                       class="w-full px-3.5 py-2 border-2 border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Peran (Role)</label>
                <select name="role" class="w-full px-3.5 py-2 border-2 border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none" onchange="this.form.submit()">
                    <option value="">Semua Peran</option>
                    <option value="petugas" {{ $roleFilter === 'petugas' ? 'selected' : '' }}>Petugas RT</option>
                    <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Dusun</label>
                <select name="dusun" class="w-full px-3.5 py-2 border-2 border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none" onchange="this.form.submit()">
                    <option value="">Semua Dusun</option>
                    @foreach($dusunList as $d)
                        <option value="{{ $d }}" {{ ($dusunFilter ?? '') === $d ? 'selected' : '' }}>Dusun {{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Wilayah RT</label>
                <select name="rt_id" class="w-full px-3.5 py-2 border-2 border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none" onchange="this.form.submit()">
                    <option value="">Semua RT (01 - 34)</option>
                    @foreach($rts as $rt)
                        <option value="{{ $rt->id }}" {{ $rtFilter == $rt->id ? 'selected' : '' }}>
                            {{ $rt->nama_rt }} ({{ $rt->dusun ?? $rt->wilayah }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                    Terapkan
                </button>
                @if($search || $roleFilter || $rtFilter || ($dusunFilter ?? ''))
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-700">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b-2 border-slate-200">
                    <tr>
                        <th class="p-3.5">Nama &amp; Kontak Petugas</th>
                        <th class="p-3.5 text-center">Peran</th>
                        <th class="p-3.5">Wilayah Tugas &amp; Beban Warga</th>
                        <th class="p-3.5 text-center">Keamanan Akun</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Nama & Kontak -->
                            <td class="p-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm text-white shadow-sm shrink-0 {{ $user->role === 'admin' ? 'bg-indigo-600' : 'bg-teal-600' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-slate-900 truncate">{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-black">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                            {{ $user->email }}
                                            @if($user->phone)
                                                &bull; <span class="font-semibold text-slate-700">{{ $user->phone }}</span>
                                            @else
                                                &bull; <span class="text-amber-600 italic">No HP belum diisi</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }}">
                                    {{ $user->role === 'admin' ? 'Super Admin' : 'Petugas RT' }}
                                </span>
                            </td>

                            <!-- Wilayah Tugas & Beban Warga -->
                            <td class="p-3.5">
                                @if($user->isPetugas())
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        @php
                                            $userRtList = $user->rts->isNotEmpty() ? $user->rts : ($user->rt ? collect([$user->rt]) : collect([]));
                                        @endphp
                                        @forelse($userRtList as $rt)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-bold text-[11px] border border-slate-300">
                                                <span>{{ $rt->nama_rt }}</span>
                                                <span class="px-1.5 py-0.2 rounded-md bg-teal-600 text-white text-[10px] font-black" title="Jumlah Pelanggan">
                                                    {{ $rt->pelanggans_count ?? $rt->pelanggans()->count() }} Warga
                                                </span>
                                            </span>
                                        @empty
                                            <span class="text-rose-600 text-xs font-bold italic">Belum di-assign RT</span>
                                        @endforelse
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs font-semibold italic">Akses Penuh Seluruh Wilayah</span>
                                @endif
                            </td>

                            <!-- Keamanan Akun / Status Password -->
                            <td class="p-3.5 text-center">
                                @if($user->isDefaultPassword())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Petugas belum pernah mengganti password bawaan ('hippam')">
                                        <span>⚠️ Sandi Default</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" title="Password telah diperbarui pada {{ $user->password_changed_at?->format('d/m/Y H:i') }}">
                                        <span>✓ Sandi Diganti</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-300' }}">
                                    {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button @click="openEdit({{ $user->toJson() }})" 
                                            class="p-2 text-sky-700 hover:bg-sky-100 rounded-xl transition border border-sky-200" title="Edit Kontak &amp; Wilayah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>

                                    <!-- Reset Password Button -->
                                    <button @click="openReset({{ $user->toJson() }})" 
                                            class="p-2 text-amber-700 hover:bg-amber-100 rounded-xl transition border border-amber-200" title="Reset Kata Sandi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </button>

                                    <!-- Delete Button -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-700 hover:bg-rose-100 rounded-xl transition border border-rose-200" title="Hapus Pengguna">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-bold">
                                Tidak ada data pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create User -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6">
                <div class="bg-gradient-to-r from-sky-700 to-teal-700 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <h3 class="text-base font-extrabold">Tambah Petugas / Admin Baru</h3>
                    <button @click="createModal = false" class="text-white/80 hover:text-white p-1 text-xl">&times;</button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1" x-data="{ newRole: 'petugas' }">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Petugas RT 20 / Bpk. Paidi" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" required placeholder="petugas20@hippam.local" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                            <select name="role" x-model="newRole" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="petugas">Petugas RT</option>
                                <option value="admin">Super Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi Awal <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" required value="password" placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- RT Selection checkboxes -->
                    <div x-show="newRole === 'petugas'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Wilayah RT yang Ditugaskan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border-2 border-slate-200 rounded-xl max-h-48 overflow-y-auto">
                            @foreach($rts as $rt)
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-white transition border border-transparent hover:border-slate-200">
                                    <input type="checkbox" name="rt_ids[]" value="{{ $rt->id }}" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500">
                                    <span>{{ $rt->nama_rt }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">Petugas hanya dapat mencatat meter &amp; pembayaran pada RT yang dipilih.</p>
                    </div>

                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6">
                <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-extrabold">Edit Data Petugas / User</h3>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="editUser.name"></p>
                    </div>
                    <button @click="editModal = false" class="text-white/80 hover:text-white p-1 text-xl">&times;</button>
                </div>
                <form :action="'/admin/users/' + editUser.id" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Petugas / Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editUser.name" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp Asli</label>
                            <input type="text" name="phone" x-model="editUser.phone" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                            <select name="role" x-model="editUser.role" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="petugas">Petugas RT</option>
                                <option value="admin">Super Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Akun <span class="text-rose-500">*</span></label>
                            <select name="status" x-model="editUser.status" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ubah Kata Sandi (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengganti" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 6 karakter jika ingin mengganti kata sandi.</p>
                    </div>

                    <!-- RT Selection checkboxes -->
                    <div x-show="editUser.role === 'petugas'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Wilayah RT yang Ditugaskan</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border-2 border-slate-200 rounded-xl max-h-48 overflow-y-auto">
                            @foreach($rts as $rt)
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-white transition border border-transparent hover:border-slate-200">
                                    <input type="checkbox" name="rt_ids[]" value="{{ $rt->id }}" 
                                           :checked="editRtIds.includes({{ $rt->id }})"
                                           @change="
                                               if ($event.target.checked) {
                                                   if (!editRtIds.includes({{ $rt->id }})) editRtIds.push({{ $rt->id }});
                                               } else {
                                                   editRtIds = editRtIds.filter(id => id !== {{ $rt->id }});
                                               }
                                           "
                                           class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500">
                                    <span>{{ $rt->nama_rt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div x-show="resetModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="resetModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md sm:w-full border-2 border-slate-300 my-6">
                <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold">Reset Kata Sandi Petugas</h3>
                        <p class="text-xs text-amber-100 mt-0.5" x-text="selectedUser?.name + ' (' + selectedUser?.email + ')'"></p>
                    </div>
                    <button @click="resetModal = false" class="text-white/80 hover:text-white p-1 text-xl">&times;</button>
                </div>
                <form :action="'/admin/users/' + selectedUser?.id + '/reset-password'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 space-y-1">
                        <p class="font-bold">⚠️ Perhatian Administrator:</p>
                        <p>Kata sandi akan direset. Harap segera berikan kata sandi baru kepada petugas terkait agar dapat segera login ke aplikasi.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi Baru</label>
                        <input type="text" name="custom_password" x-model="customPassword" required
                               class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-mono font-black focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Default: <span class="font-mono font-bold text-slate-700">hippam</span> (atau tentukan password baru yang mudah diingat).</p>
                    </div>

                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200">
                        <button type="button" @click="resetModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-amber-600 hover:bg-amber-500 rounded-xl shadow-md">
                            Reset Password Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
