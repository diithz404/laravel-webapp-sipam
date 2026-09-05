@extends('layouts.petugas')

@section('title', 'Input Meter Air')
@section('page_title', 'Pencatatan Meter Air RT')

@section('content')
<div class="space-y-4" x-data="{
    batchMode: false,
    totalTercatat: {{ $totalTercatat }},
    totalPelanggan: {{ $pelanggans->count() }},
    allFilled: {{ $allFilled ? 'true' : 'false' }},
    toastMsg: '',
    showToast: false,
    triggerToast(msg) {
        this.toastMsg = msg;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 2500);
    },
    async submitSingle(e, item) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const inputField = form.querySelector('input[name=angka_ini]');
        const val = parseInt(inputField.value);

        if (isNaN(val) || val < item.angka_lalu) {
            alert('Angka meter baru tidak boleh lebih kecil dari meter bulan lalu (' + item.angka_lalu + ')');
            return;
        }

        try {
            const res = await fetch('{{ route('petugas.input-meter.single') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                item.angka_ini = data.catatan.angka_ini;
                item.pemakaian = data.catatan.pemakaian;
                item.total_tagihan = data.catatan.total_tagihan;
                item.is_recorded = true;
                item.is_editing = false;
                this.totalTercatat = data.progress.tercatat;
                this.allFilled = data.progress.all_filled;
                this.triggerToast(data.message);

                // Auto focus next input
                const allInputs = Array.from(document.querySelectorAll('form[data-meter-form] input[name=angka_ini]'));
                const curIdx = allInputs.indexOf(inputField);
                if (curIdx >= 0 && curIdx < allInputs.length - 1) {
                    allInputs[curIdx + 1].focus();
                }
            } else {
                alert(data.message || 'Terjadi kesalahan');
            }
        } catch (err) {
            form.submit(); // fallback to normal submit if network error
        }
    }
}">

    {{-- Toast Notification --}}
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl border-2 border-emerald-500 flex items-center gap-3 font-bold text-xs" x-cloak>
        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-sm font-black">✓</span>
        <span x-text="toastMsg"></span>
    </div>

    {{-- Header & RT Selector Card --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base font-extrabold text-slate-900">Pencatatan Meter Air Warga</h2>
                <span class="text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-300 px-2 py-0.5 rounded-full">
                    Periode: {{ $activePeriode?->nama_periode ?? 'Belum ada periode' }}
                </span>
                @if(auth()->user()->isAdmin())
                    <span class="text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-300 px-2 py-0.5 rounded-full">
                        Super Admin
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">
                Wilayah Terpilih: <span class="font-extrabold text-sky-700">{{ $selectedRt?->nama_rt }} (Dusun {{ $selectedRt?->dusun ?? $selectedRt?->wilayah }})</span> &bull; <span class="font-semibold text-slate-700">{{ $pelanggans->count() }} Rumah Warga</span>
            </p>
        </div>

        {{-- RT Selector Dropdown (Same as Kasir & Data Warga) --}}
        @if($userRts->count() > 1)
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="rt_id" onchange="window.location.href='{{ route('petugas.input-meter.index') }}?rt_id=' + this.value"
                        class="w-full sm:w-auto border-2 border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50 shadow-xs">
                    @if(isset($rtsByDusun) && $rtsByDusun->isNotEmpty())
                        @foreach($rtsByDusun as $dusunName => $rts)
                            <optgroup label="Dusun {{ $dusunName }} ({{ $rts->count() }} RT)">
                                @foreach($rts as $rt)
                                    <option value="{{ $rt->id }}" {{ $selectedRt?->id == $rt->id ? 'selected' : '' }}>
                                        {{ $rt->nama_rt }} (Dusun {{ $dusunName }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @else
                        @foreach($userRts as $rt)
                            <option value="{{ $rt->id }}" {{ $selectedRt?->id == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        @else
            <div class="px-4 py-2 bg-slate-50 rounded-xl text-xs font-black text-slate-800 border border-slate-200">
                Wilayah Binaan: <span class="text-sky-700">{{ $selectedRt?->nama_rt }}</span>
            </div>
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
                <p class="text-xl font-black mt-1.5"><span x-text="totalTercatat"></span> / <span x-text="totalPelanggan"></span> Rumah Warga</p>
                <p class="text-xs text-sky-200 mt-0.5">Tersisa <span x-text="totalPelanggan - totalTercatat"></span> warga yang belum dicatat</p>
            </div>
            <div class="text-right">
                <span class="text-4xl font-black font-mono tracking-tight" x-text="totalPelanggan > 0 ? Math.round((totalTercatat / totalPelanggan) * 100) + '%' : '0%'"></span>
                <p class="text-[11px] text-sky-200 font-bold">Selesai</p>
            </div>
        </div>
        <div class="w-full bg-black/30 rounded-full h-3 mt-3.5 overflow-hidden border border-white/20">
            <div class="bg-gradient-to-r from-emerald-400 to-teal-300 h-3 rounded-full transition-all duration-500 shadow"
                 :style="'width: ' + (totalPelanggan > 0 ? (totalTercatat / totalPelanggan) * 100 : 0) + '%'"></div>
        </div>
        <div x-show="allFilled" class="mt-3 pt-2.5 border-t border-white/20 flex items-center justify-between" x-cloak>
            <span class="text-xs text-emerald-200 font-bold">✓ Semua meter warga telah tercatat lengkap!</span>
        </div>
    </div>
    @endif

    {{-- No periode / no pelanggan warning --}}
    @if(!$activePeriode)
        <div class="p-6 bg-amber-50 border-2 border-amber-300 rounded-2xl text-center shadow-sm">
            <p class="text-sm font-bold text-amber-900">⚠ Belum ada periode tagihan aktif.</p>
            <p class="text-xs text-amber-700 mt-1 font-medium">Silakan hubungi administrator untuk membuka periode tagihan bulan ini.</p>
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
            @endphp
            <div class="bg-white rounded-2xl border-2 shadow-md hover:shadow-lg transition overflow-hidden"
                 x-data="{
                    item: {
                        id: {{ $catatan?->id ?? 0 }},
                        angka_lalu: {{ $catatan?->angka_lalu ?? $pelanggan->angka_meter_awal }},
                        angka_ini: {{ $catatan?->angka_ini ?? 'null' }},
                        pemakaian: {{ $catatan?->pemakaian ?? 0 }},
                        total_tagihan: {{ $catatan?->total_tagihan ?? 0 }},
                        tunggakan_lalu: {{ $catatan?->tunggakan_lalu ?? 0 }},
                        is_recorded: {{ $isRecorded ? 'true' : 'false' }},
                        is_locked: {{ ($catatan?->status_meter === 'terkunci') ? 'true' : 'false' }},
                        is_editing: false
                    }
                 }"
                 :class="item.is_recorded ? 'border-emerald-300' : 'border-slate-300'">
                
                {{-- Warga Info Header --}}
                <div class="flex items-center justify-between px-4 py-3 transition"
                     :class="item.is_recorded ? 'bg-emerald-50/70 border-b-2 border-emerald-200' : 'bg-slate-50 border-b-2 border-slate-200'">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-sky-100 text-sky-800 border border-sky-300 font-mono">
                                {{ $pelanggan->no_rekening }}
                            </span>
                            <p class="text-sm font-black text-slate-900">{{ $pelanggan->nama }}</p>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $pelanggan->alamat }}</p>
                    </div>
                    <div>
                        <template x-if="item.is_recorded">
                            <span class="px-3 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                <span>✓</span> Tercatat
                            </span>
                        </template>
                        <template x-if="!item.is_recorded && item.is_locked">
                            <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-slate-200 text-slate-700 border border-slate-300">Terkunci</span>
                        </template>
                        <template x-if="!item.is_recorded && !item.is_locked">
                            <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-300">Belum Dicatat</span>
                        </template>
                    </div>
                </div>

                {{-- Meter Data Display --}}
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-2.5 mb-3">
                        <div class="text-center p-2.5 rounded-xl bg-slate-100 border border-slate-200">
                            <p class="text-[10px] text-slate-500 font-bold uppercase">Meter Lalu</p>
                            <p class="text-base font-black text-slate-800 font-mono" x-text="Number(item.angka_lalu).toLocaleString('id-ID')"></p>
                        </div>
                        <div class="text-center p-2.5 rounded-xl transition"
                             :class="item.is_recorded ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200'">
                            <p class="text-[10px] text-slate-500 font-bold uppercase">Meter Ini</p>
                            <p class="text-base font-black font-mono"
                               :class="item.is_recorded ? 'text-emerald-700' : 'text-amber-700'"
                               x-text="item.is_recorded ? Number(item.angka_ini).toLocaleString('id-ID') : '—'">
                            </p>
                        </div>
                        <div class="text-center p-2.5 rounded-xl bg-sky-50 border border-sky-200">
                            <p class="text-[10px] text-sky-700 font-bold uppercase">Pemakaian</p>
                            <p class="text-base font-black text-sky-800 font-mono"
                               x-text="item.is_recorded ? Number(item.pemakaian).toLocaleString('id-ID') + ' m³' : '—'">
                            </p>
                        </div>
                    </div>

                    {{-- Recorded State --}}
                    <div x-show="item.is_recorded">
                        <div class="flex items-center justify-between bg-slate-50 border-2 border-slate-200 rounded-xl px-3.5 py-2.5">
                            <div class="text-xs text-slate-700 font-medium">
                                Total Tagihan:
                                <span class="font-black text-slate-900 font-mono text-sm ml-1" x-text="'Rp' + Number(item.total_tagihan).toLocaleString('id-ID')"></span>
                                <template x-if="item.tunggakan_lalu > 0">
                                    <span class="text-rose-600 font-bold ml-1" x-text="'(+Rp' + Number(item.tunggakan_lalu).toLocaleString('id-ID') + ' tunggakan)'"></span>
                                </template>
                            </div>
                            <template x-if="!item.is_locked">
                                <button type="button" @click="item.is_editing = !item.is_editing"
                                        class="px-3 py-1 bg-sky-100 hover:bg-sky-200 text-sky-800 border border-sky-300 rounded-lg text-xs font-bold transition">
                                    ✎ Edit
                                </button>
                            </template>
                        </div>
                        
                        {{-- Edit form (inline) --}}
                        <form x-show="item.is_editing" @submit="submitSingle($event, item)" action="{{ route('petugas.input-meter.single') }}" method="POST" class="mt-3" data-meter-form>
                            @csrf
                            <input type="hidden" name="catatan_id" :value="item.id">
                            <div class="flex gap-2">
                                <input type="number" name="angka_ini" :value="item.angka_ini"
                                       :min="item.angka_lalu"
                                       class="flex-1 border-2 border-sky-400 rounded-xl px-3.5 py-2 text-base font-mono font-black focus:ring-2 focus:ring-sky-400 outline-none"
                                       placeholder="Angka meter baru" required>
                                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-extrabold rounded-xl transition shadow">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Unrecorded / Locked State --}}
                    <div x-show="!item.is_recorded">
                        <template x-if="item.is_locked">
                            <p class="text-xs text-slate-500 text-center py-2 font-medium">Periode telah dikunci oleh administrator.</p>
                        </template>
                        <template x-if="!item.is_locked">
                            <form @submit="submitSingle($event, item)" action="{{ route('petugas.input-meter.single') }}" method="POST" data-meter-form>
                                @csrf
                                <input type="hidden" name="catatan_id" :value="item.id">
                                <div class="flex gap-2">
                                    <input type="number" name="angka_ini"
                                           :min="item.angka_lalu"
                                           class="flex-1 border-2 border-slate-300 rounded-xl px-4 py-2.5 text-base font-mono font-black focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none placeholder-slate-400"
                                           placeholder="Masukkan angka meter saat ini..." required>
                                    <button type="submit"
                                            class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 active:scale-[0.98] text-white text-xs font-black rounded-xl transition shadow-md shrink-0">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
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
