@extends('layouts.admin')

@section('title', 'Kelola Pengguna')
@section('page_title', 'Manajemen Pengguna & Kader RT')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editUser: {} }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">Daftar Pengguna Sistem SIPAM</h3>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun Super Admin dan Petugas RT / Kader Pencatat</p>
        </div>

        <button @click="createModal = true" 
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Nama Pengguna</th>
                        <th class="p-3.5">Email</th>
                        <th class="p-3.5 text-center">Peran (Role)</th>
                        <th class="p-3.5">Wilayah RT yang Diampu</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-bold text-slate-900 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white {{ $user->role === 'admin' ? 'bg-indigo-600' : 'bg-teal-600' }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span>{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="text-[10px] text-sky-600 font-semibold block">(Akun Anda)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5">
                                <p class="font-medium text-slate-800">{{ $user->email }}</p>
                                <p class="text-[10px] text-slate-400">{{ $user->phone ?? 'Tidak ada no hp' }}</p>
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-teal-100 text-teal-700' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                @if($user->isPetugas())
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->rts as $rt)
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium text-[11px]">
                                                {{ $rt->nama_rt }}
                                            </span>
                                        @empty
                                            <span class="text-amber-600 text-xs italic">Belum ada RT</span>
                                        @endforelse
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Akses Penuh Semua RT</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="editUser = {{ $user->toJson() }}; editModal = true" class="p-1 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create User -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-sky-600 px-6 py-4 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold">Tambah Pengguna Baru</h3>
                    <button @click="createModal = false" class="text-white/80 hover:text-white">&times;</button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama user / kader RT..." class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                            <input type="email" name="email" required placeholder="user@hippam.id" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="phone" placeholder="08..." class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Peran (Role)</label>
                            <select name="role" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="petugas">Petugas RT</option>
                                <option value="admin">Super Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">RT yang Diampu (Jika Petugas RT)</label>
                        <select name="rt_ids[]" multiple class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none h-20">
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->nama_rt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pt-3 flex justify-end gap-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
