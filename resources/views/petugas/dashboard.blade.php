@extends('layouts.petugas')

@section('title', 'Beranda Petugas RT')

@section('content')
<div class="space-y-4">

    <!-- Officer Profile & RT Selector -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 via-cyan-600 to-teal-500 text-white font-black text-xl flex items-center justify-center shadow-md">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Petugas Bertugas:</p>
                <h2 class="text-base font-black text-slate-900 leading-tight">{{ auth()->user()->name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black bg-sky-100 text-sky-800 border border-sky-300">
                        Periode: {{ $activePeriode?->nama_periode }}
                    </span>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                        {{ $selectedRt?->nama_rt }}
                    </span>
                </div>
            </div>
        </div>

        @if($userRts->count() > 1)
            <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
                @foreach($userRts as $rt)
                    <a href="{{ route('petugas.dashboard', ['rt_id' => $rt->id]) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $selectedRt?->id == $rt->id ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-700 hover:text-slate-900' }}">
                        {{ $rt->nama_rt }}
                    </a>
                @endforeach
            </div>
        @else
            <div class="px-4 py-2 bg-slate-50 rounded-xl text-xs font-black text-slate-800 border border-slate-200">
                Wilayah Binaan: <span class="text-sky-700">{{ $selectedRt?->nama_rt }}</span>
            </div>
        @endif
    </div>

    <!-- Progress Input Meter Card -->
    <div class="bg-gradient-to-br from-sky-700 via-sky-800 to-teal-800 rounded-2xl p-5 sm:p-6 text-white shadow-lg border-2 border-sky-900 relative overflow-hidden">
        <div class="flex items-center justify-between relative z-10">
            <div>
                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-white/20 text-sky-100">
                    PROGRES PENCATATAN BULAN INI
                </span>
                <h3 class="text-xl sm:text-2xl font-black mt-2 tracking-tight">{{ $selectedRt?->nama_rt }}</h3>
                <p class="text-xs text-sky-100 mt-0.5">{{ $totalTercatat }} dari {{ $totalPelanggan }} rumah warga telah dicatat</p>
            </div>
            <div class="text-right">
                <span class="text-3xl sm:text-4xl font-black text-white font-mono">{{ $progressPersen }}%</span>
                <p class="text-[11px] text-sky-200 font-bold">Selesai</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-black/30 rounded-full h-3.5 mt-4 overflow-hidden relative z-10 border border-white/20">
            <div class="bg-gradient-to-r from-emerald-400 to-teal-300 h-3.5 rounded-full transition-all duration-500 shadow" style="width: {{ $progressPersen }}%"></div>
        </div>

        <!-- Quick CTA inside card -->
        <div class="mt-4 pt-3.5 border-t border-white/20 flex items-center justify-between relative z-10">
            <span class="text-xs text-sky-100 font-semibold">
                {{ $totalPelanggan - $totalTercatat }} rumah warga tersisa
            </span>
            <a href="{{ route('petugas.input-meter.index', ['rt_id' => $selectedRt?->id]) }}" 
               class="px-4 py-2 bg-white text-sky-900 hover:bg-sky-50 font-black text-xs rounded-xl shadow-md transition transform active:scale-95 flex items-center gap-1.5">
                <span>Catat Meter Sekarang</span> &rarr;
            </a>
        </div>
    </div>

    <!-- 3 Mini Stats -->
    <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border-2 border-slate-200 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-slate-500">Total Tagihan</span>
            <p class="text-xs sm:text-base font-black text-slate-900 mt-0.5 font-mono">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-emerald-50 p-3.5 sm:p-4 rounded-2xl border-2 border-emerald-300 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-emerald-700">Terbayar</span>
            <p class="text-xs sm:text-base font-black text-emerald-700 mt-0.5 font-mono">Rp{{ number_format($totalTerbayar, 0, ',', '.') }}</p>
        </div>
        <div class="bg-rose-50 p-3.5 sm:p-4 rounded-2xl border-2 border-rose-300 shadow-sm text-center">
            <span class="text-[10px] uppercase font-bold text-rose-700">Sisa Tunggakan</span>
            <p class="text-xs sm:text-base font-black text-rose-700 mt-0.5 font-mono">Rp{{ number_format($totalTunggakan, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Quick Action Launchers (3 Columns) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="{{ route('petugas.input-meter.index', ['rt_id' => $selectedRt?->id]) }}" 
           class="p-4 rounded-2xl bg-white border-2 border-slate-200 shadow-sm hover:border-sky-500 hover:shadow-md transition flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center group-hover:scale-105 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-sky-600">Input Meter Air</h4>
                <p class="text-[10px] text-slate-500 font-medium">Pencatatan keliling RT</p>
            </div>
        </a>

        <a href="{{ route('petugas.pembayaran.index', ['rt_id' => $selectedRt?->id]) }}" 
           class="p-4 rounded-2xl bg-white border-2 border-slate-200 shadow-sm hover:border-teal-500 hover:shadow-md transition flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-200 flex items-center justify-center group-hover:scale-105 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-teal-600">Kasir / Bayar</h4>
                <p class="text-[10px] text-slate-500 font-medium">Pembayaran Tunai / Cash</p>
            </div>
        </a>

        <a href="{{ route('petugas.warga.index', ['rt_id' => $selectedRt?->id]) }}" 
           class="p-4 rounded-2xl bg-white border-2 border-slate-200 shadow-sm hover:border-indigo-500 hover:shadow-md transition flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center group-hover:scale-105 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-indigo-600">Data Warga</h4>
                <p class="text-[10px] text-slate-500 font-medium">Daftar pelanggan &amp; WA</p>
            </div>
        </a>
    </div>

    <!-- Unrecorded Houses Quick List -->
    @if($unrecordedWargas->isNotEmpty())
        <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-sm p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <h4 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Warga Belum Dicatat ({{ $unrecordedWargas->count() }} Rumah)</span>
                </h4>
                <a href="{{ route('petugas.input-meter.index', ['rt_id' => $selectedRt?->id]) }}" class="text-xs font-bold text-sky-600 hover:underline">
                    Buka Form &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($unrecordedWargas as $w)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-900">{{ $w->nama }}</p>
                            <p class="text-[10px] text-slate-500 font-mono">No. {{ $w->no_rekening }} &bull; {{ $w->alamat }}</p>
                        </div>
                        <a href="{{ route('petugas.input-meter.index', ['rt_id' => $selectedRt?->id]) }}" 
                           class="px-3 py-1 bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 rounded-lg text-xs font-bold transition">
                            Input &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Payments in RT -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-sm p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <h4 class="text-xs font-bold text-slate-800">Pembayaran Terakhir di {{ $selectedRt?->nama_rt }}</h4>
            <a href="{{ route('petugas.pembayaran.index', ['rt_id' => $selectedRt?->id]) }}" class="text-xs font-bold text-sky-600 hover:underline">
                Buka Kasir &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentPayments as $rp)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-900">{{ $rp->catatanMeter->pelanggan->nama }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $rp->tanggal_bayar->format('d M Y') }} &bull; {{ $rp->no_transaksi }} &bull; <span class="uppercase font-semibold text-teal-700">{{ $rp->metode }}</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-emerald-700 font-mono">+Rp{{ number_format($rp->jumlah_bayar, 0, ',', '.') }}</p>
                        <a href="{{ route('kwitansi.show', $rp->catatan_meter_id) }}" target="_blank" class="text-[10px] text-sky-600 font-bold hover:underline">Lihat Struk &rarr;</a>
                    </div>
                </div>
            @empty
                <p class="py-4 text-center text-xs text-slate-400 font-medium">Belum ada transaksi pembayaran di wilayah ini.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
