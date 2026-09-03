@extends('layouts.admin')

@section('title', 'Data Warga / Pelanggan')
@section('page_title', 'Master Data RW & Warga Pelanggan')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ createModal: false, editModal: false, editPelanggan: {} }">

    <!-- Header Actions & Filters -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md p-4 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900">Daftar RW &amp; Warga Pelanggan Air</h3>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">Total <span class="font-bold text-sky-700">{{ $pelanggans->total() }}</span> warga terdaftar di seluruh wilayah RT binaan</p>
            </div>

            <button @click="createModal = true" 
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Daftarkan Warga Baru</span>
            </button>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.pelanggan.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 pt-3 border-t-2 border-slate-100">
            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama warga, no. pelanggan, alamat..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="rt_id" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">Semua Wilayah RT</option>
                    @foreach($rts as $rt)
                        <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <select name="status" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @if($search || $rtId || $status)
                    <a href="{{ route('admin.pelanggan.index') }}" class="p-2.5 text-slate-600 hover:text-slate-900 bg-slate-200 rounded-xl border border-slate-300 flex items-center justify-center shrink-0" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden">
        <div class="overflow-x-auto p-2 sm:p-3">
            <table class="w-full text-xs text-left text-slate-700">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="p-3.5">No. Pelanggan</th>
                        <th class="p-3.5">Nama Warga</th>
                        <th class="p-3.5">RT Pembina</th>
                        <th class="p-3.5">Alamat (Dusun &amp; RT/RW)</th>
                        <th class="p-3.5 text-center">Meter Awal</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($pelanggans as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono font-black text-sky-700 whitespace-nowrap">
                                <a href="{{ route('admin.pelanggan.show', $p->id) }}" class="hover:underline">
                                    {{ $p->no_rekening }}
                                </a>
                            </td>
                            <td class="p-3.5 font-black text-slate-900">
                                <a href="{{ route('admin.pelanggan.show', $p->id) }}" class="hover:text-sky-600 block">
                                    {{ $p->nama }}
                                </a>
                                @if($p->no_hp)
                                    <p class="text-[10px] text-slate-400 font-normal mt-0.5 font-mono">{{ $p->no_hp }}</p>
                                @endif
                            </td>
                            <td class="p-3.5 font-bold text-slate-800 whitespace-nowrap">{{ $p->rt?->nama_rt ?? '-' }}</td>
                            <td class="p-3.5 min-w-[170px]">
                                <div class="font-bold text-slate-800">{{ $p->dusun ? 'Dusun ' . preg_replace('/^dusun\s+/i', '', $p->dusun) : ($p->alamat ?? '-') }}</div>
                                @if($p->no_rt || $p->no_rw)
                                    <div class="text-[11px] font-mono font-bold text-sky-700 mt-0.5 flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.2 rounded bg-sky-50 border border-sky-200 text-[10px]">RT {{ $p->no_rt ?? '-' }}</span>
                                        <span class="px-1.5 py-0.2 rounded bg-sky-50 border border-sky-200 text-[10px]">RW {{ $p->no_rw ?? '-' }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3.5 text-center font-mono font-bold">{{ $p->angka_meter_awal }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $p->status === 'aktif' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-300' }}">
                                    {{ strtoupper($p->status) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.pelanggan.show', $p->id) }}" class="p-1.5 text-sky-700 hover:bg-sky-100 rounded-lg transition border border-sky-200" title="Detail Riwayat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <button @click="editPelanggan = {{ $p->toJson() }}; editModal = true" class="p-1.5 text-amber-700 hover:bg-amber-100 rounded-lg transition border border-amber-200" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <form action="{{ route('admin.pelanggan.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data warga {{ $p->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-700 hover:bg-rose-100 rounded-lg transition border border-rose-200" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 font-bold">
                                Tidak ada data warga yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pelanggans->hasPages())
            <div class="p-4 border-t-2 border-slate-200 bg-slate-50">
                {{ $pelanggans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create Pelanggan -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6">
                <div class="bg-gradient-to-r from-sky-700 to-teal-700 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <h3 class="text-base font-extrabold">Daftarkan Warga / Pelanggan Baru</h3>
                    <button @click="createModal = false" class="text-white/80 hover:text-white p-1">&times;</button>
                </div>
                <form action="{{ route('admin.pelanggan.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Pelanggan <span class="text-rose-500">*</span></label>
                        <input type="text" name="no_rekening" required placeholder="Contoh: BD-07-001" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Warga <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" required placeholder="Nama kepala keluarga..." class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah RT Pembina <span class="text-rose-500">*</span></label>
                        <select name="rt_id" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="">Pilih RT Pembina</option>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->nama_rt }} ({{ $rt->wilayah }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-slate-50 border-2 border-slate-200 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                                Alamat Domisili Warga <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-slate-500 font-semibold">Dusun, RT &amp; RW Terpisah</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Dusun <span class="text-rose-500">*</span></label>
                                <input type="text" name="dusun" required list="dusun-options-admin" placeholder="Misal: Bendrong"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">RT <span class="text-rose-500">*</span></label>
                                <input type="text" name="no_rt" required placeholder="Contoh: 01" maxlength="5"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">RW <span class="text-rose-500">*</span></label>
                                <input type="text" name="no_rw" required placeholder="Contoh: 01" maxlength="5"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                        </div>
                        <datalist id="dusun-options-admin">
                            @foreach($dusunList as $dusunName)
                                <option value="{{ $dusunName }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp/HP</label>
                            <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Angka Meter Awal</label>
                            <input type="number" name="angka_meter_awal" value="0" min="0" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Simpan Data Warga</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pelanggan -->
    <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6">
                <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <h3 class="text-base font-extrabold">Edit Data Warga</h3>
                    <button @click="editModal = false" class="text-white/80 hover:text-white p-1">&times;</button>
                </div>
                <form :action="'/admin/pelanggan/' + editPelanggan.id" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Pelanggan</label>
                        <input type="text" name="no_rekening" :value="editPelanggan.no_rekening" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Warga</label>
                        <input type="text" name="nama" :value="editPelanggan.nama" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah RT Pembina</label>
                        <select name="rt_id" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" :selected="editPelanggan.rt_id == {{ $rt->id }}">{{ $rt->nama_rt }} ({{ $rt->wilayah }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-slate-50 border-2 border-slate-200 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                                Alamat Domisili Warga <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-slate-500 font-semibold">Dusun, RT &amp; RW Terpisah</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Dusun <span class="text-rose-500">*</span></label>
                                <input type="text" name="dusun" :value="editPelanggan.dusun" required list="dusun-options-admin" placeholder="Misal: Bendrong"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">RT <span class="text-rose-500">*</span></label>
                                <input type="text" name="no_rt" :value="editPelanggan.no_rt" required placeholder="Contoh: 01" maxlength="5"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">RW <span class="text-rose-500">*</span></label>
                                <input type="text" name="no_rw" :value="editPelanggan.no_rw" required placeholder="Contoh: 01" maxlength="5"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp/HP</label>
                            <input type="text" name="no_hp" :value="editPelanggan.no_hp" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Keaktifan</label>
                            <select name="status" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="aktif" :selected="editPelanggan.status === 'aktif'">Aktif</option>
                                <option value="nonaktif" :selected="editPelanggan.status === 'nonaktif'">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Update Data Warga</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
