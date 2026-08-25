@extends('layouts.admin')

@section('title', 'Detail Warga — ' . $pelanggan->nama)
@section('page_title', 'Profil & Riwayat Warga Pelanggan')

@section('content')
<div class="space-y-6">

    <!-- Top Card / Profile Header -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-600 via-cyan-500 to-teal-400 text-white font-black text-2xl flex items-center justify-center shadow-lg">
                {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl font-black text-slate-900">{{ $pelanggan->nama }}</h3>
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-black bg-sky-100 text-sky-800 border border-sky-300">
                        {{ $pelanggan->no_rekening }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $pelanggan->status === 'aktif' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-300' }}">
                        {{ strtoupper($pelanggan->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    {{ $pelanggan->alamat }} &bull; RT Pembina: <strong>{{ $pelanggan->rt->nama_rt }}</strong> &bull; HP: {{ $pelanggan->no_hp ?? '-' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pelanggan.index') }}" class="px-4 py-2.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition border border-slate-300">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Pemakaian</span>
            <p class="text-xl sm:text-2xl font-black text-sky-700 mt-1 font-mono">{{ number_format($totalPemakaian) }} <span class="text-xs font-bold text-slate-500">m³</span></p>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Tagihan Akumulasi</span>
            <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1 font-mono">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Total Terbayar</span>
            <p class="text-xl sm:text-2xl font-black text-emerald-700 mt-1 font-mono">Rp{{ number_format($totalDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md">
            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Sisa Tunggakan</span>
            <p class="text-xl sm:text-2xl font-black {{ $totalSisa > 0 ? 'text-rose-700' : 'text-slate-400' }} mt-1 font-mono">
                Rp{{ number_format($totalSisa, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- History Timeline Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h4 class="text-sm font-bold text-slate-800">Histori Catatan Meter & Pembayaran</h4>
            <p class="text-xs text-slate-400">Rincian angka meter dari bulan ke bulan dan status pembayaran</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Periode</th>
                        <th class="p-3.5 text-center">Stand Lalu</th>
                        <th class="p-3.5 text-center">Stand Ini</th>
                        <th class="p-3.5 text-center">Pemakaian (m³)</th>
                        <th class="p-3.5 text-right">Biaya Air</th>
                        <th class="p-3.5 text-right">Admin</th>
                        <th class="p-3.5 text-right">Tunggakan</th>
                        <th class="p-3.5 text-right">Total Tagihan</th>
                        <th class="p-3.5 text-right">Dibayar</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayatMeter as $row)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-bold text-slate-900">{{ $row->periode->nama_periode }}</td>
                            <td class="p-3.5 text-center font-mono">{{ $row->angka_lalu }}</td>
                            <td class="p-3.5 text-center font-mono font-semibold">{{ $row->angka_ini ?? '-' }}</td>
                            <td class="p-3.5 text-center font-mono font-bold text-sky-700">{{ $row->pemakaian }} m³</td>
                            <td class="p-3.5 text-right font-mono">Rp{{ number_format($row->biaya_pemakaian, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono">Rp{{ number_format($row->biaya_admin, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono {{ $row->tunggakan_lalu > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">
                                Rp{{ number_format($row->tunggakan_lalu, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-mono font-black text-slate-900">Rp{{ number_format($row->total_tagihan, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-bold text-emerald-600">Rp{{ number_format($row->total_dibayar, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $row->status_bayar === 'lunas' ? 'bg-emerald-100 text-emerald-700' : ($row->status_bayar === 'sebagian' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                    {{ ucfirst($row->status_bayar) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('kwitansi.show', $row->id) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-6 text-center text-slate-400">Belum ada riwayat catatan meter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
