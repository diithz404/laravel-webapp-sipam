@extends('layouts.petugas')

@section('title', 'Rekap Data Warga RT')
@section('page_title', 'Rekap Data Warga & Laporan Tagihan RT')

@section('content')
<div class="space-y-4" x-data="{ createModal: false, editModal: false, editPelanggan: {} }">

    {{-- Header & Action Controls Card --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-md">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logohippam.png') }}" alt="Logo HIPPAM TIRTO MAKMUR" class="w-12 h-12 rounded-2xl object-cover shadow-sm bg-white p-0.5 border-2 border-slate-200 shrink-0">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 leading-tight">Rekap Data Warga &amp; Laporan Tagihan Air</h2>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        HIPPAM TIRTO MAKMUR &bull; <span class="font-bold text-slate-800">{{ $selectedRt?->nama_rt ?? 'Semua RT' }}</span> &bull; Periode: <span class="font-bold text-sky-700">{{ $selectedPeriode?->nama_periode ?? '-' }}</span>
                    </p>
                </div>
            </div>
            
            {{-- Quick Export & Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="createModal = true"
                   class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5"
                   title="Daftarkan warga baru di RT Anda">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Warga</span>
                </button>
                <a href="{{ route('petugas.warga.export', ['rt_id' => $rtId, 'periode_id' => $selectedPeriode?->id]) }}"
                   class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5"
                   title="Download data tabel dalam format Excel / CSV">
                    <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Ekspor CSV</span>
                </a>
                <a href="{{ route('petugas.input-meter.index', ['rt_id' => $rtId]) }}" 
                   class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Input Meter</span>
                </a>
                <a href="{{ route('petugas.pembayaran.index', ['rt_id' => $rtId]) }}" 
                   class="px-3 py-2 bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Kasir</span>
                </a>
            </div>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('petugas.warga.index') }}" class="flex flex-wrap items-center gap-2 pt-3 mt-3 border-t-2 border-slate-100">
            {{-- RT Filter --}}
            @if($userRts->count() > 1)
            <select name="rt_id" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                @if(isset($rtsByDusun) && $rtsByDusun->isNotEmpty())
                    @foreach($rtsByDusun as $dusunName => $rts)
                        <optgroup label="Dusun {{ $dusunName }} ({{ $rts->count() }} RT)">
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->nama_rt }} (Dusun {{ $dusunName }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @else
                    @foreach($userRts as $rt)
                        <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }} ({{ $rt->wilayah }})</option>
                    @endforeach
                @endif
            </select>
            @else
                <input type="hidden" name="rt_id" value="{{ $rtId }}">
            @endif

            {{-- Periode Filter --}}
            <select name="periode_id" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                @foreach($allPeriodes as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriode?->id == $p->id ? 'selected' : '' }}>
                        Periode: {{ $p->nama_periode }} {{ $p->status === 'aktif' ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>

            {{-- Status Meter Filter --}}
            <select name="status_meter" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua Status Meter</option>
                <option value="tercatat" {{ $statusMeter === 'tercatat' ? 'selected' : '' }}>✓ Sudah Tercatat</option>
                <option value="draft" {{ $statusMeter === 'draft' ? 'selected' : '' }}>⏳ Belum Dicatat</option>
            </select>

            {{-- Status Bayar Filter --}}
            <select name="status_bayar" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua Status Bayar</option>
                <option value="belum_bayar" {{ $statusBayar === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="sebagian" {{ $statusBayar === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                <option value="lunas" {{ $statusBayar === 'lunas' ? 'selected' : '' }}>Lunas</option>
            </select>

            {{-- Search Bar --}}
            <div class="flex-1 min-w-44 flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, no. pelanggan, alamat..."
                       class="flex-1 border-2 border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none">
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition shadow-sm">Cari</button>
                @if($search || $statusMeter || $statusBayar || $selectedPeriode?->id != $activePeriode?->id)
                <a href="{{ route('petugas.warga.index', ['rt_id' => $rtId]) }}"
                   class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-300" title="Reset Filter">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ======================================================== --}}
    {{-- RINGKASAN REKAP KEUANGAN & PEMAKAIAN AIR RT              --}}
    {{-- ======================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- 1. Total Pemakaian Air --}}
        <div class="bg-gradient-to-br from-sky-800 to-sky-950 text-white rounded-2xl p-4 sm:p-5 border-2 border-sky-900 shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-black tracking-wider text-sky-200">Total Pemakaian Air</span>
                <span class="p-1.5 rounded-lg bg-white/10 text-sky-300">💧</span>
            </div>
            <p class="text-xl sm:text-2xl font-black font-mono mt-1.5">{{ number_format($totalPemakaianM3) }} <span class="text-sm font-sans font-bold text-sky-200">m³</span></p>
            <p class="text-[11px] text-sky-200 mt-1 font-medium">
                Rata-rata: <span class="font-mono font-bold">{{ $totalWarga > 0 ? round($totalPemakaianM3 / $totalWarga, 1) : 0 }} m³</span> / rumah
            </p>
        </div>

        {{-- 2. Total Tagihan RT --}}
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 text-white rounded-2xl p-4 sm:p-5 border-2 border-slate-800 shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-black tracking-wider text-slate-300">Total Tagihan Periode</span>
                <span class="p-1.5 rounded-lg bg-white/10 text-slate-300">📑</span>
            </div>
            <p class="text-lg sm:text-2xl font-black font-mono mt-1.5 text-white">Rp{{ number_format($totalTagihanRp, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">
                <span class="font-bold text-sky-300">{{ $totalSudahDicatat }}/{{ $totalWarga }}</span> rumah tercatat ({{ $persenCatat }}%)
            </p>
        </div>

        {{-- 3. Total Kas Terbayar --}}
        <div class="bg-gradient-to-br from-emerald-800 to-teal-950 text-white rounded-2xl p-4 sm:p-5 border-2 border-emerald-900 shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-black tracking-wider text-emerald-200">Kas Terkumpul (Lunas)</span>
                <span class="p-1.5 rounded-lg bg-white/10 text-emerald-300">💰</span>
            </div>
            <p class="text-lg sm:text-2xl font-black font-mono mt-1.5 text-emerald-300">Rp{{ number_format($totalTerbayarRp, 0, ',', '.') }}</p>
            <p class="text-[11px] text-emerald-200 mt-1 font-medium">
                <span class="font-bold text-white">{{ $totalLunas }}</span> lunas • <span class="font-bold text-white">{{ $persenBayar }}%</span> tertagih
            </p>
        </div>

        {{-- 4. Sisa Tunggakan --}}
        <div class="bg-gradient-to-br from-rose-900 to-slate-950 text-white rounded-2xl p-4 sm:p-5 border-2 border-rose-900 shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-black tracking-wider text-rose-200">Sisa Tunggakan Belum Bayar</span>
                <span class="p-1.5 rounded-lg bg-white/10 text-rose-300">⏳</span>
            </div>
            <p class="text-lg sm:text-2xl font-black font-mono mt-1.5 text-rose-300">Rp{{ number_format($totalTunggakanRp, 0, ',', '.') }}</p>
            <p class="text-[11px] text-rose-200 mt-1 font-medium">
                <span class="font-bold text-white">{{ $totalBelumBayar + $totalSebagian }}</span> warga belum lunas
            </p>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TABEL REKAP DETAIL WARGA & CATATAN METER                 --}}
    {{-- ======================================================== --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">
                    Tabel Rekapitulasi Warga &amp; Tagihan Air ({{ $pelanggans->count() }} Data)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data rincian pemakaian stand meter dan status keuangan per warga di wilayah <span class="font-bold text-slate-800">{{ $selectedRt?->nama_rt }}</span>.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-sky-800 font-bold bg-sky-50 border border-sky-200 px-3 py-1 rounded-xl font-mono">
                    Periode: {{ $selectedPeriode?->nama_periode }}
                </span>
            </div>
        </div>

        @if($pelanggans->isEmpty())
            <div class="p-10 text-center">
                <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="text-sm font-bold text-slate-600 mt-3">Tidak ada data warga yang cocok dengan filter ini.</p>
                <p class="text-xs text-slate-400 mt-1">Silakan sesuaikan kata kunci pencarian atau reset filter di atas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-black text-[10px] tracking-wider border-b-2 border-slate-300">
                        <tr>
                            <th class="px-3 py-3 text-center w-10">No</th>
                            <th class="px-3 py-3 min-w-[130px]">No. Pelanggan</th>
                            <th class="px-3 py-3 min-w-[130px]">Nama Warga &amp; Kontak</th>
                            <th class="px-3 py-3 min-w-[190px]">Alamat (Dusun &amp; RT/RW)</th>
                            <th class="px-3 py-3 text-center min-w-[85px]">Stand Lalu</th>
                            <th class="px-3 py-3 text-center min-w-[115px]">Stand Kini</th>
                            <th class="px-3 py-3 text-center min-w-[80px]">Pemakaian</th>
                            <th class="px-3 py-3 text-right min-w-[110px]">Total Tagihan</th>
                            <th class="px-3 py-3 text-right min-w-[100px]">Terbayar</th>
                            <th class="px-3 py-3 text-right min-w-[100px]">Sisa Tagihan</th>
                            <th class="px-3 py-3 text-center min-w-[100px]">Status</th>
                            <th class="px-3 py-3 text-center min-w-[150px]">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($pelanggans as $index => $warga)
                        @php
                            $catatan = $warga->catatanMeters->first();
                            $sudahTercatat = $catatan && $catatan->angka_ini !== null;
                            $cleanPhone = preg_replace('/[^0-9]/', '', $warga->no_hp ?? '');
                            if (str_starts_with($cleanPhone, '0')) {
                                $waPhone = '62' . substr($cleanPhone, 1);
                            } else {
                                $waPhone = $cleanPhone;
                            }
                            
                            $waTextWarga = "*INFORMASI TAGIHAN / PEMBAYARAN AIR*\n"
                                         . "*HIPPAM TIRTO MAKMUR*\n"
                                         . "----------------------------------------\n"
                                         . "No. Pelanggan : {$warga->no_rekening}\n"
                                         . "Nama Warga    : {$warga->nama}\n"
                                         . "Periode       : " . ($selectedPeriode?->nama_periode ?? '-') . "\n\n"
                                         . "*Rincian Pemakaian:*\n"
                                         . "- Stand Lalu : " . ($catatan ? number_format($catatan->angka_lalu) : number_format($warga->angka_meter_awal)) . "\n"
                                         . "- Stand Kini : " . ($sudahTercatat ? number_format($catatan->angka_ini) : 'Belum Dicatat') . "\n"
                                         . "- Pemakaian  : " . ($sudahTercatat ? number_format($catatan->pemakaian) . ' m³' : '-') . "\n\n"
                                         . "*Rincian Biaya:*\n"
                                         . "- Total Tagihan: Rp" . number_format($catatan?->total_tagihan ?? 0, 0, ',', '.') . "\n"
                                         . "- Sudah Dibayar: Rp" . number_format($catatan?->total_dibayar ?? 0, 0, ',', '.') . "\n"
                                         . "- Sisa Tagihan : Rp" . number_format($catatan?->sisa_tagihan ?? 0, 0, ',', '.') . "\n"
                                         . "- Status Bayar : *" . strtoupper($catatan?->status_bayar ?? 'BELUM BAYAR') . "* " . ($catatan?->status_bayar === 'lunas' ? '✅' : '⏳') . "\n\n"
                                         . ($catatan ? "Lihat Kwitansi Digital:\n" . route('kwitansi.show', $catatan->id) . "\n\n" : "")
                                         . "_Pesan otomatis Petugas HIPPAM TIRTO MAKMUR._";
                            $waMessage = urlencode($waTextWarga);
                        @endphp
                        <tr class="hover:bg-sky-50/50 transition {{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/40' }}">
                            {{-- 1. No --}}
                            <td class="px-3 py-2.5 text-center font-bold text-slate-500 font-mono">{{ $index + 1 }}</td>

                            {{-- 2. No. Pelanggan --}}
                            <td class="px-3 py-2.5 font-mono">
                                <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 font-bold text-[11px] border border-sky-300">
                                    {{ $warga->no_rekening }}
                                </span>
                            </td>

                            {{-- 3. Nama Warga & Kontak --}}
                            <td class="px-3 py-2.5">
                                <div class="font-black text-slate-900 text-xs">{{ $warga->nama }}</div>
                                @if($warga->no_hp)
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $warga->no_hp }}</div>
                                @endif
                            </td>

                            {{-- 4. Alamat --}}
                            <td class="px-3 py-2.5 text-left text-xs">
                                <div class="font-bold text-slate-900">{{ $warga->dusun ? 'Dusun ' . preg_replace('/^dusun\s+/i', '', $warga->dusun) : ($warga->alamat ?? '-') }}</div>
                                @if($warga->no_rt || $warga->no_rw)
                                    <div class="text-[10px] font-mono font-bold text-sky-700 mt-0.5 flex items-center gap-1">
                                        <span class="px-1.5 py-0.2 rounded bg-sky-50 border border-sky-200">RT {{ $warga->no_rt ?? '-' }}</span>
                                        <span class="px-1.5 py-0.2 rounded bg-sky-50 border border-sky-200">RW {{ $warga->no_rw ?? '-' }}</span>
                                    </div>
                                @endif
                            </td>

                            {{-- 5. Stand Lalu --}}
                            <td class="px-3 py-2.5 text-center font-mono font-bold text-slate-700">
                                {{ $catatan ? number_format($catatan->angka_lalu) : number_format($warga->angka_meter_awal) }}
                            </td>

                            {{-- 6. Stand Kini --}}
                            <td class="px-3 py-2.5 text-center font-mono font-black">
                                @if($sudahTercatat)
                                    <span class="text-emerald-800 bg-emerald-100 border border-emerald-300 px-2 py-0.5 rounded-md">
                                        {{ number_format($catatan->angka_ini) }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">
                                        Belum Diisi
                                    </span>
                                @endif
                            </td>

                            {{-- 7. Pemakaian --}}
                            <td class="px-3 py-2.5 text-center font-mono font-black text-sky-900">
                                {{ $sudahTercatat ? number_format($catatan->pemakaian) . ' m³' : '—' }}
                            </td>

                            {{-- 8. Total Tagihan --}}
                            <td class="px-3 py-2.5 text-right font-mono font-black text-slate-900">
                                @if($catatan)
                                    Rp{{ number_format($catatan->total_tagihan, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- 9. Terbayar --}}
                            <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-700">
                                @if($catatan && $catatan->total_dibayar > 0)
                                    Rp{{ number_format($catatan->total_dibayar, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">Rp0</span>
                                @endif
                            </td>

                            {{-- 10. Sisa Tagihan --}}
                            <td class="px-3 py-2.5 text-right font-mono font-black {{ ($catatan?->sisa_tagihan ?? 0) > 0 ? 'text-rose-700' : 'text-slate-400' }}">
                                @if($catatan)
                                    Rp{{ number_format($catatan->sisa_tagihan, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- 11. Status Bayar --}}
                            <td class="px-3 py-2.5 text-center">
                                @if($catatan && $catatan->status_bayar === 'lunas')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ LUNAS
                                    </span>
                                @elseif($catatan && $catatan->status_bayar === 'sebagian')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                        SEBAGIAN
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        BELUM BAYAR
                                    </span>
                                @endif
                            </td>

                            {{-- 12. Aksi Cepat --}}
                            <td class="px-3 py-2.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="editPelanggan = {{ $warga->toJson() }}; editModal = true"
                                       class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-[10px] font-bold transition flex items-center gap-1" title="Edit Data Warga">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Edit</span>
                                    </button>
                                    @if($warga->no_hp)
                                        <a href="https://wa.me/{{ $waPhone }}?text={{ $waMessage }}" target="_blank"
                                           class="p-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg transition"
                                           title="Kirim Info Tagihan via WA">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($catatan)
                                        <a href="{{ route('kwitansi.show', $catatan->id) }}" target="_blank"
                                           class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-[10px] font-bold transition" title="Lihat Kwitansi">
                                            Struk
                                        </a>
                                    @endif
                                    <a href="{{ route('petugas.input-meter.index', ['rt_id' => $warga->rt_id]) }}" 
                                       class="px-2 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-300 rounded-lg text-[10px] font-bold transition" title="Input Meter">
                                        Catat
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    {{-- GRAND TOTAL FOOTER --}}
                    <tfoot class="bg-slate-900 text-white font-mono font-black text-xs border-t-2 border-slate-900">
                        <tr>
                            <td colspan="4" class="px-3 py-3 text-right font-sans font-black uppercase text-[11px] tracking-wider text-slate-300">
                                GRAND TOTAL REKAP RT ({{ $pelanggans->count() }} Warga):
                            </td>
                            <td colspan="2" class="px-3 py-3 text-center text-slate-400 font-normal text-[10px]">
                                {{ $totalSudahDicatat }}/{{ $totalWarga }} Tercatat
                            </td>
                            <td class="px-3 py-3 text-center text-sky-300 text-sm">
                                {{ number_format($totalPemakaianM3) }} m³
                            </td>
                            <td class="px-3 py-3 text-right text-white text-sm">
                                Rp{{ number_format($totalTagihanRp, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right text-emerald-400 text-sm">
                                Rp{{ number_format($totalTerbayarRp, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right text-rose-400 text-sm">
                                Rp{{ number_format($totalTunggakanRp, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-center text-[10px] font-bold text-slate-300">
                                {{ $persenBayar }}% Lunas
                            </td>
                            <td class="px-3 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- MODAL TAMBAH WARGA BARU (PETUGAS RT)                     -->
    <!-- ======================================================== -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6 relative z-10">
                <div class="bg-gradient-to-r from-sky-800 via-cyan-900 to-teal-900 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-extrabold">Daftarkan Warga Baru</h3>
                        <p class="text-xs text-sky-200 mt-0.5">Tambah pelanggan HIPPAM baru di wilayah binaan Anda</p>
                    </div>
                    <button @click="createModal = false" class="text-white/80 hover:text-white text-xl font-bold p-1">&times;</button>
                </div>
                <form action="{{ route('petugas.warga.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Pelanggan / Rekening <span class="text-rose-500">*</span></label>
                        <input type="text" name="no_rekening" required placeholder="Contoh: 003 atau BD-01-003" class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Warga <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" required placeholder="Nama kepala keluarga..." class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah RT Binaan <span class="text-rose-500">*</span></label>
                        <select name="rt_id" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach($userRts as $rt)
                                <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }} ({{ $rt->wilayah }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ALAMAT TERPISAH (Dusun, RT, RW) --}}
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
                                <input type="text" name="dusun" required list="dusun-options-petugas" placeholder="Misal: Bendrong"
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
                        <datalist id="dusun-options-petugas">
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

    <!-- ======================================================== -->
    <!-- MODAL EDIT WARGA (PETUGAS RT)                            -->
    <!-- ======================================================== -->
    <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border-2 border-slate-300 max-h-[90vh] flex flex-col my-6 relative z-10">
                <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-extrabold">Edit Data Warga</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Perbarui data identitas &amp; alamat domisili warga</p>
                    </div>
                    <button @click="editModal = false" class="text-white/80 hover:text-white text-xl font-bold p-1">&times;</button>
                </div>
                <form :action="'/petugas/warga/' + editPelanggan.id" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Pelanggan</label>
                        <input type="text" name="no_rekening" :value="editPelanggan.no_rekening" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-mono font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap Warga</label>
                        <input type="text" name="nama" :value="editPelanggan.nama" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah RT</label>
                        <select name="rt_id" required class="w-full px-4 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach($userRts as $rt)
                                <option value="{{ $rt->id }}" :selected="editPelanggan.rt_id == {{ $rt->id }}">{{ $rt->nama_rt }} ({{ $rt->wilayah }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ALAMAT TERPISAH (Dusun, RT, RW) --}}
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
                                <input type="text" name="dusun" :value="editPelanggan.dusun" required list="dusun-options-petugas" placeholder="Misal: Bendrong"
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
