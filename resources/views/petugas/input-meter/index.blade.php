@extends('layouts.petugas')

@section('title', 'Input Meter Air')
@section('page_title', 'Pencatatan Meter Air RT')

@section('content')
<div class="space-y-4" x-data="{ batchMode: false, allSelected: false }">

    {{-- Header & RT Selector --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Pencatatan Meter Air Warga</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Periode Aktif: <span class="font-bold text-sky-700">{{ $activePeriode?->nama_periode ?? 'Belum ada periode aktif' }}</span>
            </p>
        </div>

        {{-- RT Filter --}}
        @if($userRts->count() > 1)
        <div class="flex flex-wrap gap-1.5">
            @foreach($userRts as $rt)
                <a href="{{ route('petugas.input-meter.index', ['rt_id' => $rt->id]) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition border-2
                          {{ $selectedRt?->id == $rt->id
                              ? 'bg-sky-600 text-white border-sky-700 shadow-md'
                              : 'bg-slate-50 text-slate-700 border-slate-300 hover:border-sky-400 hover:text-sky-700' }}">
                    {{ $rt->nama_rt }}
                </a>
            @endforeach
        </div>
        @else
            <span class="px-3.5 py-2 bg-sky-50 text-sky-800 border-2 border-sky-200 rounded-xl text-xs font-black">
                Wilayah Binaan: {{ $selectedRt?->nama_rt ?? 'Semua RT' }}
            </span>
        @endif
    </div>

    {{-- Progress Summary Card --}}
    @if($pelanggans->isNotEmpty())
    <div class="bg-gradient-to-br from-sky-700 via-sky-800 to-teal-800 rounded-2xl p-5 text-white shadow-lg border-2 border-sky-900">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider bg-white/20 px-2.5 py-0.5 rounded-full text-sky-100">
                    Progres Pencatatan RT
                </span>
                <p class="text-xl font-black mt-1.5">{{ $totalTercatat }} / {{ $pelanggans->count() }} Rumah Warga</p>
                <p class="text-xs text-sky-200 mt-0.5">Tersisa {{ $pelanggans->count() - $totalTercatat }} warga yang belum dicatat</p>
            </div>
            <div class="text-right">
                @php $pct = $pelanggans->count() > 0 ? round(($totalTercatat / $pelanggans->count()) * 100) : 0; @endphp
                <span class="text-4xl font-black font-mono tracking-tight">{{ $pct }}%</span>
                <p class="text-[11px] text-sky-200 font-bold">Selesai</p>
            </div>
        </div>
        <div class="w-full bg-black/30 rounded-full h-3 mt-3.5 overflow-hidden border border-white/20">
            <div class="bg-gradient-to-r from-emerald-400 to-teal-300 h-3 rounded-full transition-all duration-500 shadow" style="width: {{ $pct }}%"></div>
        </div>
        @if($allFilled)
            <div class="mt-3 pt-2.5 border-t border-white/20 flex items-center justify-between">
                <span class="text-xs text-emerald-200 font-bold">✓ Semua meter warga telah tercatat lengkap!</span>
            </div>
        @endif
    </div>
    @endif

    {{-- No periode / no pelanggan warning --}}
    @if(!$activePeriode)
        <div class="p-6 bg-amber-50 border-2 border-amber-300 rounded-2xl text-center shadow-sm">
            <p class="text-sm font-bold text-amber-900">⚠ Belum ada periode tagihan aktif.</p>
            <p class="text-xs text-amber-700 mt-1">Silakan hubungi administrator untuk membuka periode tagihan bulan ini.</p>
        </div>
    @elseif($pelanggans->isEmpty())
        <div class="p-6 bg-white border-2 border-slate-200 rounded-2xl text-center shadow-sm">
            <p class="text-sm font-bold text-slate-700">Tidak ada daftar warga aktif di wilayah {{ $selectedRt?->nama_rt }}.</p>
        </div>
    @else

    {{-- Mode Toggle --}}
    <div class="flex items-center justify-between pt-1">
        <p class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Daftar Warga Binaan</p>
        <div class="flex gap-2">
            <button @click="batchMode = false" :class="!batchMode ? 'bg-sky-600 text-white shadow-md' : 'bg-white text-slate-700 border-2 border-slate-300'"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition">
                Satu per Satu
            </button>
            <button @click="batchMode = true" :class="batchMode ? 'bg-sky-600 text-white shadow-md' : 'bg-white text-slate-700 border-2 border-slate-300'"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-xl transition">
                Input Serentak
            </button>
        </div>
    </div>

    {{-- === SINGLE INPUT MODE === --}}
    <div x-show="!batchMode" class="space-y-3">
        @foreach($pelanggans as $pelanggan)
            @php 
                $catatan = $catatanRecords[$pelanggan->id] ?? null; 
                $isRecorded = $catatan?->angka_ini !== null;
                $cardBorder = $isRecorded ? 'border-emerald-300' : 'border-slate-300';
            @endphp
            <div class="bg-white rounded-2xl border-2 {{ $cardBorder }} shadow-md hover:shadow-lg transition overflow-hidden">
                {{-- Warga Info Header --}}
                <div class="flex items-center justify-between px-4 py-3 {{ $isRecorded ? 'bg-emerald-50/70 border-b-2 border-emerald-200' : 'bg-slate-50 border-b-2 border-slate-200' }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-sky-100 text-sky-800 border border-sky-300 font-mono">
                                {{ $pelanggan->no_rekening }}
                            </span>
                            <p class="text-sm font-black text-slate-900">{{ $pelanggan->nama }}</p>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $pelanggan->alamat }}</p>
                    </div>
                    @if($isRecorded)
                        <span class="px-3 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                            <span>✓</span> Tercatat
                        </span>
                    @elseif($catatan?->status_meter === 'terkunci')
                        <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-slate-200 text-slate-700 border border-slate-300">Terkunci</span>
                    @else
                        <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-300">Belum Dicatat</span>
                    @endif
                </div>

                {{-- Meter Data Display --}}
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-2.5 mb-3">
                        <div class="text-center p-2.5 rounded-xl bg-slate-100 border border-slate-200">
                            <p class="text-[10px] text-slate-500 font-bold uppercase">Meter Lalu</p>
                            <p class="text-base font-black text-slate-800 font-mono">{{ number_format($catatan?->angka_lalu ?? 0) }}</p>
                        </div>
                        <div class="text-center p-2.5 rounded-xl {{ $isRecorded ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }}">
                            <p class="text-[10px] text-slate-500 font-bold uppercase">Meter Ini</p>
                            <p class="text-base font-black {{ $isRecorded ? 'text-emerald-700' : 'text-amber-700' }} font-mono">
                                {{ $isRecorded ? number_format($catatan->angka_ini) : '—' }}
                            </p>
                        </div>
                        <div class="text-center p-2.5 rounded-xl bg-sky-50 border border-sky-200">
                            <p class="text-[10px] text-sky-700 font-bold uppercase">Pemakaian</p>
                            <p class="text-base font-black text-sky-800 font-mono">
                                {{ $isRecorded ? number_format($catatan->pemakaian) . ' m³' : '—' }}
                            </p>
                        </div>
                    </div>

                    @if($isRecorded)
                        {{-- Summary tagihan --}}
                        <div class="flex items-center justify-between bg-slate-50 border-2 border-slate-200 rounded-xl px-3.5 py-2.5">
                            <div class="text-xs text-slate-700">
                                Total Tagihan:
                                <span class="font-black text-slate-900 font-mono text-sm ml-1">Rp{{ number_format($catatan->total_tagihan, 0, ',', '.') }}</span>
                                @if($catatan->tunggakan_lalu > 0)
                                    <span class="text-rose-600 font-bold ml-1">(+Rp{{ number_format($catatan->tunggakan_lalu, 0, ',', '.') }} tunggakan)</span>
                                @endif
                            </div>
                            @if($catatan->status_meter !== 'terkunci')
                            <button type="button" 
                                    onclick="this.closest('.p-4').querySelector('[data-edit]').classList.toggle('hidden')" 
                                    class="px-3 py-1 bg-sky-100 hover:bg-sky-200 text-sky-800 border border-sky-300 rounded-lg text-xs font-bold transition">
                                ✎ Edit
                            </button>
                            @endif
                        </div>
                        
                        {{-- Edit form (hidden by default) --}}
                        @if($catatan->status_meter !== 'terkunci')
                        <form action="{{ route('petugas.input-meter.single') }}" method="POST" class="hidden mt-3" data-edit>
                            @csrf
                            <input type="hidden" name="catatan_id" value="{{ $catatan->id }}">
                            <div class="flex gap-2">
                                <input type="number" name="angka_ini" value="{{ $catatan->angka_ini }}"
                                       min="{{ $catatan->angka_lalu }}"
                                       class="flex-1 border-2 border-sky-400 rounded-xl px-3.5 py-2 text-base font-mono font-black focus:ring-2 focus:ring-sky-400 outline-none"
                                       placeholder="Angka meter baru">
                                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-extrabold rounded-xl transition shadow">
                                    Simpan
                                </button>
                            </div>
                        </form>
                        @endif
                    @elseif($catatan && $catatan->status_meter === 'terkunci')
                        <p class="text-xs text-slate-500 text-center py-2 font-medium">Periode telah dikunci oleh administrator.</p>
                    @else
                        {{-- Input form --}}
                        <form action="{{ route('petugas.input-meter.single') }}" method="POST">
                            @csrf
                            <input type="hidden" name="catatan_id" value="{{ $catatan?->id }}">
                            <div class="flex gap-2">
                                <input type="number" name="angka_ini"
                                       min="{{ $catatan?->angka_lalu ?? 0 }}"
                                       class="flex-1 border-2 border-slate-300 rounded-xl px-4 py-2.5 text-base font-mono font-black focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none placeholder-slate-400"
                                       placeholder="Masukkan angka meter saat ini...">
                                <button type="submit"
                                        class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 active:scale-[0.98] text-white text-xs font-black rounded-xl transition shadow-md">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- === BATCH INPUT MODE === --}}
    <div x-show="batchMode" x-cloak>
        <form action="{{ route('petugas.input-meter.batch') }}" method="POST" class="space-y-3">
            @csrf
            @foreach($pelanggans as $index => $pelanggan)
                @php $catatan = $catatanRecords[$pelanggan->id] ?? null; @endphp
                @if($catatan)
                <input type="hidden" name="meters[{{ $index }}][id]" value="{{ $catatan->id }}">
                <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-sky-100 text-sky-800 font-mono">
                                {{ $pelanggan->no_rekening }}
                            </span>
                            <p class="text-sm font-black text-slate-900 truncate">{{ $pelanggan->nama }}</p>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Meter Lalu: <span class="font-mono font-bold text-slate-800">{{ number_format($catatan->angka_lalu) }}</span></p>
                    </div>
                    <input type="number"
                           name="meters[{{ $index }}][angka_ini]"
                           value="{{ $catatan->angka_ini }}"
                           min="{{ $catatan->angka_lalu }}"
                           {{ $catatan->status_meter === 'terkunci' ? 'disabled' : '' }}
                           class="w-36 border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-base font-mono font-black text-center focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none {{ $catatan->angka_ini !== null ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-50' }}"
                           placeholder="0">
                </div>
                @endif
            @endforeach

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="px-6 py-3.5 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-black text-sm rounded-xl shadow-lg transition">
                    Simpan Semua Angka Meter Sekaligus &rarr;
                </button>
            </div>
        </form>
    </div>

    {{-- Tutup Periode RT --}}
    @if($allFilled && $activePeriode)
    <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md">
        <div>
            <p class="text-sm font-black text-amber-950">Semua meter warga sudah dicatat!</p>
            <p class="text-xs text-amber-800 mt-0.5">Kunci periode untuk wilayah {{ $selectedRt?->nama_rt }} agar data menjadi final dan siap bayar.</p>
        </div>
        <form action="{{ route('petugas.input-meter.tutup-periode') }}" method="POST">
            @csrf
            <input type="hidden" name="rt_id" value="{{ $selectedRt?->id }}">
            <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
            <button type="submit"
                    onclick="return confirm('Apakah Anda yakin ingin mengunci periode input untuk {{ $selectedRt?->nama_rt }}?')"
                    class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl transition shadow-md">
                Kunci Periode RT
            </button>
        </form>
    </div>
    @endif

    @endif

</div>
@endsection
