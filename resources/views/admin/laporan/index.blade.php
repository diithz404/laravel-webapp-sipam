@extends('layouts.admin')

@section('title', 'Rekapitulasi & Laporan')
@section('page_title', 'Laporan Rekapitulasi HIPPAM Tirto Makmur')

@section('content')
<div class="space-y-6">

    <!-- Header Controls & Export -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">Laporan Rekapitulasi & Audit Keuangan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Format laporan rekapitulasi operasional dan keuangan HIPPAM Tirto Makmur</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Period Selector Form -->
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex items-center gap-1.5">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="periode_id" onchange="this.form.submit()"
                        class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    @foreach($allPeriodes as $p)
                        <option value="{{ $p->id }}" {{ $selectedPeriode?->id == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Export Buttons -->
            <a href="{{ route('admin.laporan.export', ['periode_id' => $selectedPeriode?->id]) }}" 
               class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export Excel / CSV</span>
            </a>

            <button onclick="window.print()" 
                    class="px-3.5 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl border border-slate-300 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2 bg-white px-4 rounded-xl shadow-sm">
        <a href="{{ route('admin.laporan.index', ['tab' => 'rekap-rt', 'periode_id' => $selectedPeriode?->id]) }}" 
           class="py-3 px-4 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'rekap-rt' ? 'border-sky-600 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>📊 Rekap per RT (Hal 7)</span>
        </a>
        <a href="{{ route('admin.laporan.index', ['tab' => 'rekap-warga', 'periode_id' => $selectedPeriode?->id]) }}" 
           class="py-3 px-4 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'rekap-warga' ? 'border-sky-600 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>📋 Rekap Multi-Bulan Warga (Hal 8–11)</span>
        </a>
        <a href="{{ route('admin.laporan.index', ['tab' => 'keuangan', 'periode_id' => $selectedPeriode?->id]) }}" 
           class="py-3 px-4 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'keuangan' ? 'border-sky-600 text-sky-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>💰 Laporan Keuangan HIPPAM</span>
        </a>
    </div>

    <!-- TAB 1: Rekap per RT (Hal 7) -->
    @if($tab === 'rekap-rt')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4 p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-bold text-slate-900">Rekapitulasi Tagihan per Wilayah RT — Periode {{ $selectedPeriode?->nama_periode }}</h4>
                <span class="text-xs text-slate-400 font-mono">Format Sheet Hal7</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3">Wilayah RT</th>
                            <th class="p-3 text-center">Warga (KK)</th>
                            <th class="p-3 text-center">Tercatat</th>
                            <th class="p-3 text-center">Pemakaian (m³)</th>
                            <th class="p-3 text-right">Biaya Air (Rp)</th>
                            <th class="p-3 text-right">Admin (Rp)</th>
                            <th class="p-3 text-right">Tunggakan Lalu</th>
                            <th class="p-3 text-right">Total Tagihan</th>
                            <th class="p-3 text-right">Terbayar</th>
                            <th class="p-3 text-right">Sisa Tagihan</th>
                            <th class="p-3 text-center">% Lunas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $sumWarga = 0;
                            $sumTercatat = 0;
                            $sumM3 = 0;
                            $sumAir = 0;
                            $sumAdmin = 0;
                            $sumTunggakan = 0;
                            $sumTagihan = 0;
                            $sumDibayar = 0;
                            $sumSisa = 0;
                        @endphp
                        @foreach($rekapPerRt as $row)
                            @php
                                $sumWarga += $row->total_warga;
                                $sumTercatat += $row->tercatat;
                                $sumM3 += $row->total_m3;
                                $sumAir += $row->biaya_pemakaian;
                                $sumAdmin += $row->biaya_admin;
                                $sumTunggakan += $row->tunggakan_lalu;
                                $sumTagihan += $row->total_tagihan;
                                $sumDibayar += $row->total_dibayar;
                                $sumSisa += $row->sisa_tagihan;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition font-medium">
                                <td class="p-3 font-bold text-slate-900">{{ $row->rt->nama_rt }}</td>
                                <td class="p-3 text-center font-mono">{{ $row->total_warga }}</td>
                                <td class="p-3 text-center font-mono font-bold {{ $row->tercatat == $row->total_warga ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $row->tercatat }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold text-sky-700">{{ number_format($row->total_m3) }}</td>
                                <td class="p-3 text-right font-mono">Rp{{ number_format($row->biaya_pemakaian, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono">Rp{{ number_format($row->biaya_admin, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono text-amber-700">Rp{{ number_format($row->tunggakan_lalu, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono font-bold text-slate-900">Rp{{ number_format($row->total_tagihan, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-600">Rp{{ number_format($row->total_dibayar, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono font-bold {{ $row->sisa_tagihan > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    Rp{{ number_format($row->sisa_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $row->persen_lunas >= 80 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $row->persen_lunas }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 text-slate-900 font-bold border-t-2 border-slate-300">
                        <tr>
                            <td class="p-3 uppercase">TOTAL KESELURUHAN</td>
                            <td class="p-3 text-center font-mono">{{ $sumWarga }}</td>
                            <td class="p-3 text-center font-mono text-emerald-700">{{ $sumTercatat }}</td>
                            <td class="p-3 text-center font-mono text-sky-800">{{ number_format($sumM3) }}</td>
                            <td class="p-3 text-right font-mono">Rp{{ number_format($sumAir, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono">Rp{{ number_format($sumAdmin, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-amber-800">Rp{{ number_format($sumTunggakan, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-slate-900 text-sm">Rp{{ number_format($sumTagihan, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-emerald-700 text-sm">Rp{{ number_format($sumDibayar, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-rose-700 text-sm">Rp{{ number_format($sumSisa, 0, ',', '.') }}</td>
                            <td class="p-3 text-center font-mono">
                                {{ $sumTagihan > 0 ? round(($sumDibayar / $sumTagihan) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: Rekap Multi-Bulan Warga (Hal 8-11) -->
    @if($tab === 'rekap-warga')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">Rekapitulasi Angka Meter & Tagihan Multi-Bulan Warga</h4>
                    <p class="text-xs text-slate-400">Menampilkan matriks stand meter dan total rupiah per periode (setara sheet Hal8–Hal11)</p>
                </div>

                <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="rekap-warga">
                    <input type="hidden" name="periode_id" value="{{ $selectedPeriode?->id }}">
                    <select name="rt_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="">Semua RT</option>
                        @foreach($allRts as $rt)
                            <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[11px] text-left text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="p-2.5">No. Pelanggan</th>
                            <th class="p-2.5">Nama Warga</th>
                            <th class="p-2.5">RT</th>
                            @foreach($recentPeriodes as $p)
                                <th class="p-2.5 text-center bg-sky-50/50 border-l border-slate-200" colspan="2">
                                    {{ $p->nama_periode }}
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-slate-100 text-slate-500 text-[9px]">
                            <th class="p-1"></th>
                            <th class="p-1"></th>
                            <th class="p-1"></th>
                            @foreach($recentPeriodes as $p)
                                <th class="p-1 text-center border-l border-slate-200 font-mono">Stand (m³)</th>
                                <th class="p-1 text-right font-mono">Tagihan (Rp)</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($wargaMatrix as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-2.5 font-mono font-bold text-sky-700">{{ $item->warga->no_rekening }}</td>
                                <td class="p-2.5 font-bold text-slate-900">{{ $item->warga->nama }}</td>
                                <td class="p-2.5 font-medium text-slate-600">{{ $item->warga->rt->nama_rt }}</td>
                                @foreach($recentPeriodes as $p)
                                    @php $rec = $item->records[$p->id] ?? null; @endphp
                                    <td class="p-2.5 text-center font-mono border-l border-slate-200">
                                        {{ $rec ? ($rec->angka_ini ?? $rec->angka_lalu) : '-' }}
                                    </td>
                                    <td class="p-2.5 text-right font-mono font-semibold {{ $rec && $rec->status_bayar === 'lunas' ? 'text-emerald-600' : 'text-slate-800' }}">
                                        {{ $rec ? number_format($rec->total_tagihan, 0, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 3: Laporan Keuangan HIPPAM -->
    @if($tab === 'keuangan')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-2">
                <span class="text-xs font-semibold text-slate-400 uppercase">Potensi Penerimaan Bruto</span>
                <p class="text-2xl font-black text-slate-900">Rp{{ number_format($keuangan->total_potensi, 0, ',', '.') }}</p>
                <div class="text-xs text-slate-500 space-y-1 pt-3 border-t border-slate-100">
                    <div class="flex justify-between">
                        <span>Biaya Air (m³):</span>
                        <span class="font-bold">Rp{{ number_format($keuangan->total_air, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Biaya Administrasi:</span>
                        <span class="font-bold">Rp{{ number_format($keuangan->total_admin, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-2">
                <span class="text-xs font-semibold text-emerald-600 uppercase">Kas Realisasi Diterima</span>
                <p class="text-2xl font-black text-emerald-600">Rp{{ number_format($keuangan->total_realisasi, 0, ',', '.') }}</p>
                <div class="text-xs text-emerald-800 space-y-1 pt-3 border-t border-slate-100">
                    <div class="flex justify-between">
                        <span>Efisiensi Penagihan:</span>
                        <span class="font-bold">{{ $keuangan->efisiensi }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 mt-1">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $keuangan->efisiensi }}%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-2">
                <span class="text-xs font-semibold text-rose-600 uppercase">Total Piutang Berjalan</span>
                <p class="text-2xl font-black text-rose-600">Rp{{ number_format($keuangan->total_piutang, 0, ',', '.') }}</p>
                <div class="text-xs text-slate-500 space-y-1 pt-3 border-t border-slate-100">
                    <p>Sisa tagihan yang belum dilunasi warga pada periode {{ $selectedPeriode?->nama_periode }}.</p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
