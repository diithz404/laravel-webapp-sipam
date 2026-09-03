@extends('layouts.admin')

@section('title', 'Data Warga / Pelanggan')
@section('page_title', 'Master Data Pelanggan Desa (3 Dusun)')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ createModal: false, editModal: false, editPelanggan: {} }">

    <!-- Header Actions & Quick Nav -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md p-4 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base sm:text-lg font-black text-slate-900">Master Data Pelanggan Air HIPPAM</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-sky-100 text-sky-800 border border-sky-300">
                        3 Dusun / 34 RT
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    Total <span class="font-bold text-sky-700">{{ $pelanggans->total() }}</span> warga sesuai filter (dari total database master desa)
                </p>
            </div>

            <button @click="createModal = true" 
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-md transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Daftarkan Warga Baru</span>
            </button>
        </div>

        <!-- Quick Filters Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a href="{{ route('admin.pelanggan.index') }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ !$statusSetup && !$jenisPelanggan && !$namaGanda && !$dusun ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Data
            </a>
            <a href="{{ route('admin.pelanggan.index', ['status_setup' => 'belum_lengkap']) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $statusSetup === 'belum_lengkap' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                <span>⚠️ Belum Setup Awal</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusSetup === 'belum_lengkap' ? 'bg-white/30 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $totalBelumSetup }}</span>
            </a>
            <a href="{{ route('admin.pelanggan.index', ['jenis_pelanggan' => 'non_rumah_tangga']) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $jenisPelanggan === 'non_rumah_tangga' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-indigo-50 text-indigo-800 border border-indigo-200 hover:bg-indigo-100' }}">
                <span>🏢 Non-Rumah Tangga</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $jenisPelanggan === 'non_rumah_tangga' ? 'bg-white/30 text-white' : 'bg-indigo-200 text-indigo-900' }}">{{ $totalNonRt }}</span>
            </a>
            <a href="{{ route('admin.pelanggan.index', ['nama_ganda' => '1']) }}" 
               class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $namaGanda ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100' }}">
                <span>🔍 Perlu Verifikasi (Nama Ganda)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $namaGanda ? 'bg-white/30 text-white' : 'bg-rose-200 text-rose-900' }}">{{ $totalNamaGanda }}</span>
            </a>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.pelanggan.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-2.5 pt-3 border-t-2 border-slate-100">
            @if($statusSetup) <input type="hidden" name="status_setup" value="{{ $statusSetup }}"> @endif
            @if($jenisPelanggan) <input type="hidden" name="jenis_pelanggan" value="{{ $jenisPelanggan }}"> @endif
            @if($namaGanda) <input type="hidden" name="nama_ganda" value="{{ $namaGanda }}"> @endif

            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama warga, no. rekening (HPM-..), alamat..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="dusun" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">Semua Dusun</option>
                    @foreach($dusunList as $d)
                        <option value="{{ $d }}" {{ $dusun == $d ? 'selected' : '' }}>Dusun {{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="rt_id" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">Semua RT (01 - 34)</option>
                    @foreach($rts as $rt)
                        <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>
                            {{ $rt->nama_rt }} ({{ $rt->dusun ?? $rt->wilayah }})
                        </option>
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
                @if($search || $rtId || $dusun || $status || $statusSetup || $jenisPelanggan || $namaGanda)
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
                        <th class="p-3.5">Kategori &amp; Dusun</th>
                        <th class="p-3.5">RT Pembina</th>
                        <th class="p-3.5 text-center">Setup Awal</th>
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
                                @if($p->no_urut_lokal)
                                    <span class="block text-[10px] font-normal text-slate-400 font-sans">No Urut: #{{ $p->no_urut_lokal }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-black text-slate-900">
                                <a href="{{ route('admin.pelanggan.show', $p->id) }}" class="hover:text-sky-600 block">
                                    {{ $p->nama }}
                                </a>
                                @if($p->catatan_nama)
                                    <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>{{ $p->catatan_nama }}</span>
                                    </div>
                                @endif
                                @if($p->no_hp)
                                    <p class="text-[10px] text-slate-400 font-normal mt-0.5 font-mono">{{ $p->no_hp }}</p>
                                @endif
                            </td>
                            <td class="p-3.5 min-w-[150px]">
                                <div class="font-bold text-slate-800">
                                    Dusun {{ $p->dusun ? preg_replace('/^dusun\s+/i', '', $p->dusun) : ($p->rt?->dusun ?? '-') }}
                                </div>
                                @if($p->jenis_pelanggan === 'non_rumah_tangga')
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-black bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        🏢 {{ $p->sub_kategori ?? 'Non-RT' }}
                                    </span>
                                @else
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        🏠 Rumah Tangga
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 font-bold text-slate-800 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-lg bg-sky-50 border border-sky-200 text-sky-800 font-black">
                                    {{ $p->rt?->nama_rt ?? ('RT ' . $p->no_rt) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($p->status_setup === 'belum_lengkap')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        Belum Lengkap
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Siap ({{ $p->angka_meter_awal ?? 0 }} m³)
                                    </span>
                                @endif
                            </td>
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
                                Tidak ada data warga yang ditemukan sesuai kriteria filter.
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
                        <input type="text" name="no_rekening" required placeholder="Contoh: HPM-01-001" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Warga <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" required placeholder="Nama kepala keluarga..." class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah RT Pembina <span class="text-rose-500">*</span></label>
                        <select name="rt_id" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="">Pilih RT Pembina (01 - 34)</option>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->nama_rt }} ({{ $rt->dusun ?? $rt->wilayah }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Pelanggan</label>
                            <select name="jenis_pelanggan" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="rumah_tangga">Rumah Tangga</option>
                                <option value="non_rumah_tangga">Non Rumah Tangga</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub Kategori</label>
                            <select name="sub_kategori" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="">- Standar -</option>
                                <option value="Fasilitas Ibadah">Fasilitas Ibadah</option>
                                <option value="Peternakan/Penampungan">Peternakan/Penampungan</option>
                                <option value="Fasilitas Pendidikan">Fasilitas Pendidikan</option>
                                <option value="Koperasi/Usaha">Koperasi/Usaha</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-slate-50 border-2 border-slate-200 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                                Alamat Domisili Warga <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-slate-500 font-semibold">Dusun &amp; RT Terpisah</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Dusun <span class="text-rose-500">*</span></label>
                                <select name="dusun" required class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="Pateguhan">Pateguhan (RT 01-12)</option>
                                    <option value="Gentong">Gentong (RT 13-19)</option>
                                    <option value="Bendrong">Bendrong (RT 20-34)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">RT <span class="text-rose-500">*</span></label>
                                <input type="text" name="no_rt" required placeholder="Contoh: 01" maxlength="5"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                        </div>
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
                        <input type="text" name="no_rekening" :value="editPelanggan.no_rekening" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Warga</label>
                        <input type="text" name="nama" :value="editPelanggan.nama" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Nama (Nama Ganda / Verifikasi)</label>
                        <input type="text" name="catatan_nama" :value="editPelanggan.catatan_nama" placeholder="Keterangan verifikasi nama..." class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah RT Pembina</label>
                        <select name="rt_id" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" :selected="editPelanggan.rt_id == {{ $rt->id }}">{{ $rt->nama_rt }} ({{ $rt->dusun ?? $rt->wilayah }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Pelanggan</label>
                            <select name="jenis_pelanggan" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="rumah_tangga" :selected="editPelanggan.jenis_pelanggan === 'rumah_tangga'">Rumah Tangga</option>
                                <option value="non_rumah_tangga" :selected="editPelanggan.jenis_pelanggan === 'non_rumah_tangga'">Non Rumah Tangga</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub Kategori</label>
                            <select name="sub_kategori" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="" :selected="!editPelanggan.sub_kategori">- Standar -</option>
                                <option value="Fasilitas Ibadah" :selected="editPelanggan.sub_kategori === 'Fasilitas Ibadah'">Fasilitas Ibadah</option>
                                <option value="Peternakan/Penampungan" :selected="editPelanggan.sub_kategori === 'Peternakan/Penampungan'">Peternakan/Penampungan</option>
                                <option value="Fasilitas Pendidikan" :selected="editPelanggan.sub_kategori === 'Fasilitas Pendidikan'">Fasilitas Pendidikan</option>
                                <option value="Koperasi/Usaha" :selected="editPelanggan.sub_kategori === 'Koperasi/Usaha'">Koperasi/Usaha</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-slate-50 border-2 border-slate-200 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                                Alamat Domisili Warga <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-slate-500 font-semibold">Dusun &amp; RT</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Dusun <span class="text-rose-500">*</span></label>
                                <select name="dusun" required class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="Pateguhan" :selected="editPelanggan.dusun === 'Pateguhan'">Pateguhan</option>
                                    <option value="Gentong" :selected="editPelanggan.dusun === 'Gentong'">Gentong</option>
                                    <option value="Bendrong" :selected="editPelanggan.dusun === 'Bendrong'">Bendrong</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">RT <span class="text-rose-500">*</span></label>
                                <input type="text" name="no_rt" :value="editPelanggan.no_rt" required placeholder="Contoh: 01" maxlength="5"
                                       class="w-full px-3 py-2 border-2 border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp</label>
                            <input type="text" name="no_hp" :value="editPelanggan.no_hp" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Setup</label>
                            <select name="status_setup" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <option value="belum_lengkap" :selected="editPelanggan.status_setup === 'belum_lengkap'">Belum Lengkap</option>
                                <option value="lengkap" :selected="editPelanggan.status_setup === 'lengkap'">Lengkap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Keaktifan</label>
                            <select name="status" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
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
