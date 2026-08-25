@extends('layouts.petugas')

@section('title', 'Data Warga RT')
@section('page_title', 'Daftar Warga & Pelanggan RT')

@section('content')
<div class="space-y-4">

    {{-- Header & Filters Card --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Data Warga &amp; Pelanggan Air</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Periode Aktif: <span class="font-bold text-sky-700">{{ $activePeriode?->nama_periode ?? '-' }}</span> &bull; 
                    Wilayah: <span class="font-bold text-slate-800">{{ $userRts->firstWhere('id', $rtId)?->nama_rt ?? 'Semua RT' }} ({{ $userRts->firstWhere('id', $rtId)?->wilayah ?? 'Desa Argosari' }})</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('petugas.input-meter.index', ['rt_id' => $rtId]) }}" 
                   class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Input Meter</span>
                </a>
                <a href="{{ route('petugas.pembayaran.index', ['rt_id' => $rtId]) }}" 
                   class="px-3.5 py-2 bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Kasir / Bayar</span>
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('petugas.warga.index') }}" class="flex flex-wrap gap-2">
            @if($userRts->count() > 1)
            <select name="rt_id" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua RT Binaan</option>
                @foreach($userRts as $rt)
                    <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }} ({{ $rt->wilayah }})</option>
                @endforeach
            </select>
            @else
                <input type="hidden" name="rt_id" value="{{ $rtId }}">
            @endif

            <select name="status_meter" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua Status Meter</option>
                <option value="tercatat" {{ $statusMeter === 'tercatat' ? 'selected' : '' }}>✓ Sudah Tercatat</option>
                <option value="draft" {{ $statusMeter === 'draft' ? 'selected' : '' }}>⏳ Belum Dicatat</option>
            </select>

            <select name="status_bayar" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua Status Bayar</option>
                <option value="belum_bayar" {{ $statusBayar === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="sebagian" {{ $statusBayar === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                <option value="lunas" {{ $statusBayar === 'lunas' ? 'selected' : '' }}>Lunas</option>
            </select>

            <div class="flex-1 min-w-44 flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, no. pelanggan, alamat, atau no HP..."
                       class="flex-1 border-2 border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none">
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition shadow-sm">Cari</button>
                @if($search || $statusMeter || $statusBayar)
                <a href="{{ route('petugas.warga.index', ['rt_id' => $rtId]) }}"
                   class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-300">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Summary Stats Badges --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div class="bg-white border-2 border-slate-200 rounded-2xl p-3.5 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-slate-500">Total Warga</span>
            <p class="text-lg font-black text-slate-900 mt-0.5 font-mono">{{ $totalWarga }}</p>
        </div>
        <div class="bg-sky-50 border-2 border-sky-300 rounded-2xl p-3.5 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-sky-700">Sudah Dicatat</span>
            <p class="text-lg font-black text-sky-800 mt-0.5 font-mono">{{ $totalSudahDicatat }}</p>
        </div>
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-3.5 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-amber-700">Belum Dicatat</span>
            <p class="text-lg font-black text-amber-800 mt-0.5 font-mono">{{ $totalBelumDicatat }}</p>
        </div>
        <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-3.5 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-emerald-700">Lunas Tagihan</span>
            <p class="text-lg font-black text-emerald-800 mt-0.5 font-mono">{{ $totalLunas }}</p>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Tabel Data Warga &amp; Tagihan Air ({{ $pelanggans->count() }} Data)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Menampilkan stand meter lalu, inputan meter kini, pemakaian, dan total rupiah yang harus dibayar.</p>
            </div>
            <span class="text-xs text-sky-700 font-bold bg-sky-50 border border-sky-200 px-3 py-1 rounded-xl">Desa Argosari</span>
        </div>

        @if($pelanggans->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="text-sm font-bold text-slate-600 mt-3">Tidak ada data warga yang cocok.</p>
                <p class="text-xs text-slate-400 mt-1">Silakan sesuaikan kata kunci pencarian atau filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100/80 text-slate-700 uppercase font-black text-[10px] tracking-wider border-b-2 border-slate-200">
                        <tr>
                            <th class="px-3.5 py-3.5 text-center w-10">No</th>
                            <th class="px-3.5 py-3.5">No. Pelanggan</th>
                            <th class="px-3.5 py-3.5">Nama Warga &amp; Kontak</th>
                            <th class="px-3.5 py-3.5 min-w-[200px]">Alamat</th>
                            <th class="px-3.5 py-3.5 text-center">Stand Lalu</th>
                            <th class="px-3.5 py-3.5 text-center">Stand Kini (Inputan)</th>
                            <th class="px-3.5 py-3.5 text-center">Pemakaian</th>
                            <th class="px-3.5 py-3.5 text-right">Jumlah Tagihan</th>
                            <th class="px-3.5 py-3.5 text-center">Status Bayar</th>
                            <th class="px-3.5 py-3.5 text-center">Aksi Cepat</th>
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
                            $waMessage = urlencode("Halo Bpk/Ibu {$warga->nama}, info dari Petugas HIPPAM Tirto Makmur mengenai layanan air bersih Desa Argosari.");
                        @endphp
                        <tr class="hover:bg-sky-50/40 transition">
                            {{-- 1. No --}}
                            <td class="px-3.5 py-3 text-center font-bold text-slate-500 font-mono">{{ $index + 1 }}</td>

                            {{-- 2. No. Pelanggan --}}
                            <td class="px-3.5 py-3">
                                <span class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-800 font-mono font-black text-xs border border-sky-300 inline-block shadow-2xs">
                                    {{ $warga->no_rekening }}
                                </span>
                            </td>

                            {{-- 3. Nama Warga & Kontak --}}
                            <td class="px-3.5 py-3">
                                <div class="font-black text-slate-900 text-xs">{{ $warga->nama }}</div>
                                @if($warga->no_hp)
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $warga->no_hp }}</div>
                                @else
                                    <div class="text-[10px] text-slate-400 italic mt-0.5">Tdk ada HP</div>
                                @endif
                            </td>

                            {{-- 4. Alamat (Dusun, RT/RW) --}}
                            <td class="px-3.5 py-3">
                                <div class="font-semibold text-slate-900 text-xs">{{ $warga->alamat }}</div>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5">
                                    {{ $warga->rt->nama_rt }} &bull; <span class="text-sky-800 font-bold">{{ $warga->rt->wilayah }}</span>
                                </div> 
                            </td>

                            {{-- 5. Stand Lalu --}}
                            <td class="px-3.5 py-3 text-center">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                    {{ $catatan ? number_format($catatan->angka_lalu) : number_format($warga->angka_meter_awal) }}
                                </span>
                            </td>

                            {{-- 6. Stand Kini (Inputan) --}}
                            <td class="px-3.5 py-3 text-center">
                                @if($sudahTercatat)
                                    <span class="font-mono font-black text-emerald-800 bg-emerald-100 border border-emerald-300 px-2.5 py-1 rounded-lg shadow-2xs">
                                        ✓ {{ number_format($catatan->angka_ini) }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                        ⏳ Belum Diinput
                                    </span>
                                @endif
                            </td>

                            {{-- 7. Pemakaian (m3) --}}
                            <td class="px-3.5 py-3 text-center">
                                @if($sudahTercatat)
                                    <span class="font-mono font-black text-sky-800 text-xs">
                                        {{ number_format($catatan->pemakaian) }} <span class="text-[10px] text-slate-500 font-normal">m³</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono">-</span>
                                @endif
                            </td>

                            {{-- 8. Jumlah Tagihan --}}
                            <td class="px-3.5 py-3 text-right">
                                @if($catatan)
                                    <div class="font-black text-slate-900 text-xs font-mono">
                                        Rp{{ number_format($catatan->total_tagihan, 0, ',', '.') }}
                                    </div>
                                    @if($catatan->tunggakan_lalu > 0)
                                        <div class="text-[9px] text-amber-600 font-bold mt-0.5">
                                            +Tunggakan: Rp{{ number_format($catatan->tunggakan_lalu, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    @if($catatan->total_dibayar > 0 && $catatan->status_bayar !== 'lunas')
                                        <div class="text-[9px] text-emerald-600 font-bold mt-0.5">
                                            Dibayar: Rp{{ number_format($catatan->total_dibayar, 0, ',', '.') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 font-mono">-</span>
                                @endif
                            </td>

                            {{-- 9. Status Bayar --}}
                            <td class="px-3.5 py-3 text-center">
                                @if($catatan && $catatan->status_bayar === 'lunas')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> LUNAS
                                    </span>
                                @elseif($catatan && $catatan->status_bayar === 'sebagian')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> SEBAGIAN
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> BELUM BAYAR
                                    </span>
                                @endif
                            </td>

                            {{-- 10. Aksi Cepat --}}
                            <td class="px-3.5 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($warga->no_hp)
                                        <a href="https://wa.me/{{ $waPhone }}?text={{ $waMessage }}" target="_blank"
                                           class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-lg transition shadow-2xs"
                                           title="Chat WhatsApp">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('petugas.input-meter.index', ['rt_id' => $warga->rt_id]) }}" 
                                       class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-300 rounded-lg text-[11px] font-bold transition shadow-2xs"
                                       title="Catat Meter">
                                        Catat
                                    </a>
                                    <a href="{{ route('petugas.pembayaran.index', ['rt_id' => $warga->rt_id, 'search' => $warga->no_rekening]) }}" 
                                       class="px-2.5 py-1 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-300 rounded-lg text-[11px] font-bold transition shadow-2xs"
                                       title="Bayar Kasir">
                                        Bayar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
