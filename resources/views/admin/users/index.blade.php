@extends('layouts.admin')

@section('title', 'Kelola Pengguna')
@section('page_title', 'Manajemen Pengguna & Kader RT')

@section('content')
<div class="space-y-6" x-data="{ 
    createModal: false, 
    editModal: false, 
    editUser: { id: '', name: '', email: '', phone: '', role: 'petugas', status: 'active', rts: [] },
    editRtIds: [],
    openEdit(user) {
        this.editUser = { ...user };
        this.editRtIds = user.rts ? user.rts.map(r => r.id) : [];
        this.editModal = true;
    }
}">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-6 bg-white rounded-2xl border-2 border-slate-200 shadow-md">
        <div>
            <h3 class="text-base sm:text-lg font-black text-slate-900">Daftar Pengguna Sistem SIPAM</h3>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Kelola akun Super Admin dan Petugas RT / Kader Pencatat</p>
        </div>

        <button @click="createModal = true" 
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Pengguna Baru</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto p-3">
            <table class="w-full text-xs text-left text-slate-700">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b-2 border-slate-200">
                    <tr>
                        <th class="p-3.5">Nama Pengguna</th>
                        <th class="p-3.5">Email &amp; No. HP</th>
                        <th class="p-3.5 text-center">Peran (Role)</th>
                        <th class="p-3.5">Wilayah RT yang Diampu</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-bold text-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-sm shrink-0 {{ $user->role === 'admin' ? 'bg-indigo-600' : 'bg-teal-600' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-extrabold text-slate-900 block truncate">{{ $user->name }}</span>
                                        @if($user->id === auth()->id())
                                            <span class="text-[10px] text-sky-600 font-bold block">(Akun Anda)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5">
                                <p class="font-bold text-slate-800">{{ $user->email }}</p>
                                <p class="text-[10px] text-slate-500 font-mono">{{ $user->phone ?? 'Tidak ada no hp' }}</p>
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }}">
                                    {{ $user->role === 'admin' ? 'Super Admin' : 'Petugas RT' }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                @if($user->isPetugas())
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->rts as $rt)
                                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200">
                                                {{ $rt->nama_rt }}
                                            </span>
                                        @empty
                                            <span class="text-amber-700 text-xs font-bold italic">Belum ada RT</span>
                                        @endforelse
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs font-semibold italic">Akses Penuh Semua RT</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-300' }}">
                                    {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button @click="openEdit({{ $user->toJson() }})" 
                                            class="p-2 text-sky-700 hover:bg-sky-100 rounded-xl transition border border-sky-200" title="Edit Pengguna">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?')">
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
                                Belum ada data pengguna.
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
                    <h3 class="text-base font-extrabold">Tambah Pengguna Baru</h3>
                    <button @click="createModal = false" class="text-white/80 hover:text-white p-1">&times;</button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1" x-data="{ newRole: 'petugas' }">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Nama user / kader RT..." class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" required placeholder="user@hippam.id" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
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
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- RT Selection checkboxes -->
                    <div x-show="newRole === 'petugas'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Wilayah RT yang Diampu</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border-2 border-slate-200 rounded-xl max-h-40 overflow-y-auto">
                            @foreach($rts as $rt)
                                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-white transition border border-transparent hover:border-slate-200">
                                    <input type="checkbox" name="rt_ids[]" value="{{ $rt->id }}" class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500">
                                    <span>{{ $rt->nama_rt }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">Petugas dapat mengampu satu atau lebih RT binaan.</p>
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
                        <h3 class="text-base font-extrabold">Edit Data Pengguna</h3>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="editUser.name"></p>
                    </div>
                    <button @click="editModal = false" class="text-white/80 hover:text-white p-1">&times;</button>
                </div>
                <form :action="'/admin/users/' + editUser.id" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editUser.name" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
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
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah sandi" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 6 karakter jika ingin mengganti kata sandi.</p>
                    </div>

                    <!-- RT Selection checkboxes -->
                    <div x-show="editUser.role === 'petugas'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Wilayah RT yang Diampu</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border-2 border-slate-200 rounded-xl max-h-40 overflow-y-auto">
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
                        <p class="text-[11px] text-slate-500 font-medium">Pilih wilayah RT yang menjadi tanggung jawab petugas ini.</p>
                    </div>

                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Update Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
