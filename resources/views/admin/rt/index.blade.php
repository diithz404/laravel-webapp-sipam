@extends('layouts.admin')

@section('title', 'Kelola RT & Petugas')
@section('page_title', 'Master Data RT Desa (34 RT / 3 Dusun)')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editRt: {} }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-6 bg-white rounded-2xl border-2 border-slate-200 shadow-md">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-base sm:text-lg font-black text-slate-900">Daftar Wilayah RT Desa Argosari</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-teal-100 text-teal-800 border border-teal-300">
                    34 RT (31 Terisi, 3 Menunggu Data)
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">Penomoran RT desa-wide: Pateguhan (RT 01-12), Gentong (RT 13-19), Bendrong (RT 20-34).</p>
        </div>

        <button @click="createModal = true" 
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Wilayah RT Baru</span>
        </button>
    </div>

    <!-- Grouped RT Sections by Dusun -->
    @foreach($rtsByDusun as $dusunName => $dusunRts)
        <div class="space-y-3">
            <div class="flex items-center justify-between bg-slate-100 px-4 py-2.5 rounded-2xl border border-slate-300">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $dusunName === 'Pateguhan' ? 'bg-sky-500' : ($dusunName === 'Gentong' ? 'bg-emerald-500' : 'bg-indigo-500') }}"></span>
                    <h4 class="text-sm font-black text-slate-900">Dusun {{ $dusunName }}</h4>
                    <span class="text-xs font-bold text-slate-500">({{ $dusunRts->count() }} RT)</span>
                </div>
                <div class="text-xs font-bold text-slate-600">
                    Total: <span class="text-sky-700 font-black">{{ $dusunRts->sum('pelanggans_count') }}</span> Warga
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($dusunRts as $rt)
                    <div class="bg-white rounded-2xl border-2 {{ $rt->pelanggans_count == 0 ? 'border-amber-200 bg-amber-50/10' : 'border-slate-200' }} shadow-md hover:shadow-xl hover:border-sky-300 transition p-4 sm:p-5 flex flex-col justify-between group">
                        <div>
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-black {{ $rt->pelanggans_count > 0 ? 'bg-sky-100 text-sky-800 border border-sky-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                        {{ $rt->kode_rt }}
                                    </span>
                                    <h4 class="text-base font-black text-slate-900 mt-1.5">{{ $rt->nama_rt }}</h4>
                                    <p class="text-xs text-slate-500 font-medium">Dusun {{ $rt->dusun ?? $rt->wilayah }}</p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1">
                                    <button @click="editRt = {{ $rt->toJson() }}; editModal = true"
                                            class="p-2 text-slate-500 hover:text-sky-600 hover:bg-sky-50 rounded-xl transition border border-slate-200 hover:border-sky-200" title="Edit RT">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <form action="{{ route('admin.rt.destroy', $rt->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus wilayah {{ $rt->nama_rt }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition border border-slate-200 hover:border-rose-200" title="Hapus RT">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($rt->keterangan)
                                <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl mt-3 border border-slate-200 italic font-medium">
                                    "{{ $rt->keterangan }}"
                                </p>
                            @endif

                            <!-- Officers in charge -->
                            <div class="mt-4 pt-3 border-t-2 border-slate-100">
                                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500">Petugas RT Pengampu:</span>
                                <div class="mt-2 space-y-1.5">
                                    @forelse($rt->petugas as $p)
                                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 bg-slate-50 p-2 rounded-xl border border-slate-200">
                                            <div class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-[10px] font-black shadow-xs shrink-0">
                                                {{ strtoupper(substr($p->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate">{{ $p->name }}</p>
                                                <p class="text-[10px] text-slate-400 font-normal truncate font-mono">{{ $p->phone ?? $p->email }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-amber-700 bg-amber-50 p-2 rounded-xl border border-amber-200 font-bold">
                                            {{ $rt->pelanggans_count == 0 ? 'Belum ada data & petugas' : 'Belum ditugaskan petugas' }}
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Footer Stats -->
                        <div class="mt-5 pt-3.5 border-t-2 border-slate-200 flex items-center justify-between text-xs">
                            @if($rt->pelanggans_count > 0)
                                <span class="text-slate-600 font-bold bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $rt->pelanggans_count }} Warga Binaan</span>
                                <a href="{{ route('admin.pelanggan.index', ['rt_id' => $rt->id]) }}" class="font-extrabold text-sky-700 hover:text-sky-800 flex items-center gap-1">
                                    <span>Lihat Warga</span> &rarr;
                                </a>
                            @else
                                <span class="text-amber-800 font-bold bg-amber-100 px-2.5 py-1 rounded-lg border border-amber-300">⚠️ Belum Ada Data</span>
                                <span class="text-slate-400 font-medium">RT Siap Digunakan</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Modal Create RT -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6">
                <div class="bg-gradient-to-r from-sky-700 to-teal-700 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <h3 class="text-base font-extrabold">Tambah Wilayah RT Baru</h3>
                    <button @click="createModal = false" class="text-white/80 hover:text-white p-1">&times;</button>
                </div>
                <form action="{{ route('admin.rt.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode RT <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_rt" required placeholder="Contoh: RT 35" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama RT <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_rt" required placeholder="Contoh: RT 35" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Dusun <span class="text-rose-500">*</span></label>
                        <select name="wilayah" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="Dusun Pateguhan">Dusun Pateguhan</option>
                            <option value="Dusun Gentong">Dusun Gentong</option>
                            <option value="Dusun Bendrong">Dusun Bendrong</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Petugas RT Pengampu</label>
                        <select name="petugas_ids[]" multiple class="w-full px-3.5 py-2 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none h-24">
                            @foreach($allPetugas as $petugas)
                                <option value="{{ $petugas->id }}">{{ $petugas->name }} ({{ $petugas->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1 font-medium">Tahan tombol Ctrl (Windows) untuk memilih lebih dari satu petugas.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan Wilayah</label>
                        <textarea name="keterangan" rows="2" placeholder="Catatan wilayah Dusun dan RT..." class="w-full px-4 py-2 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Simpan Wilayah RT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit RT -->
    <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6">
                <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <h3 class="text-base font-extrabold">Edit Wilayah RT</h3>
                    <button @click="editModal = false" class="text-white/80 hover:text-white p-1">&times;</button>
                </div>
                <form :action="'/admin/rt/' + editRt.id" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode RT</label>
                        <input type="text" name="kode_rt" :value="editRt.kode_rt" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama RT</label>
                        <input type="text" name="nama_rt" :value="editRt.nama_rt" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Dusun</label>
                        <input type="text" name="wilayah" :value="editRt.wilayah" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Petugas RT Pengampu</label>
                        <select name="petugas_ids[]" multiple class="w-full px-3.5 py-2 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none h-24">
                            @foreach($allPetugas as $petugas)
                                <option value="{{ $petugas->id }}">{{ $petugas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan Wilayah</label>
                        <textarea name="keterangan" :value="editRt.keterangan" rows="2" class="w-full px-4 py-2 border-2 border-slate-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-200 shrink-0">
                        <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md">Update RT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
