@extends('layouts.petugas')

@section('title', 'Kasir / Pembayaran')
@section('page_title', 'Kasir & Pembayaran Tagihan')

@section('content')
<div class="space-y-4" x-data="{ 
    payModal: false, 
    selectedCatatan: null,
    uangDiterima: 0,
    tagihanSisa: 0,
    paidSuccess: null,
    hitungKembalian() {
        return Math.max(0, this.uangDiterima - this.tagihanSisa);
    },
    setUangPas() {
        this.uangDiterima = this.tagihanSisa;
    },
    setNominal(val) {
        this.uangDiterima = val;
    },
    async submitPayment(e, isPrint, isWa) {
        e.preventDefault();
        const form = e.target.closest('form');
        const formData = new FormData(form);
        if (isPrint) {
            formData.append('cetak', '1');
        }

        try {
            const res = await fetch('{{ route('petugas.pembayaran.store') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                this.payModal = false;
                
                // Update row in DOM
                const rowEl = document.querySelector(`[data-catatan-id='${data.catatan.id}']`);
                if (rowEl) {
                    const sisaEl = rowEl.querySelector('[data-sisa]');
                    if (sisaEl) sisaEl.textContent = 'Rp' + Number(data.catatan.sisa_tagihan).toLocaleString('id-ID');
                    const dibayarEl = rowEl.querySelector('[data-dibayar]');
                    if (dibayarEl) dibayarEl.textContent = 'Rp' + Number(data.catatan.total_dibayar).toLocaleString('id-ID');
                    const badge = rowEl.querySelector('[data-badge]');
                    if (badge) {
                        badge.className = 'px-2.5 py-0.5 text-[10px] font-black rounded-full uppercase tracking-wider border bg-emerald-100 text-emerald-800 border-emerald-300';
                        badge.textContent = '✓ Lunas';
                    }
                    const payBtn = rowEl.querySelector('[data-pay-btn]');
                    if (payBtn && data.catatan.status_bayar === 'lunas') {
                        payBtn.classList.add('hidden');
                    }
                }

                // Show toast with receipt link & WA link
                const cleanPhone = this.selectedCatatan?.clean_phone || '';
                const waText = "*BUKTI PEMBAYARAN AIR - HIPPAM TIRTO MAKMUR*\n"
                             + "----------------------------------------\n"
                             + "No. Pelanggan : " + (this.selectedCatatan?.no_rek || '') + "\n"
                             + "Nama Warga    : " + (this.selectedCatatan?.nama || '') + "\n"
                             + "Periode       : " + (this.selectedCatatan?.periode || '') + "\n"
                             + "Total Bayar   : Rp" + Number(formData.get('jumlah_bayar')).toLocaleString('id-ID') + "\n"
                             + "Status        : LUNAS ✅\n"
                             + "No. Transaksi : " + (data.pembayaran?.no_transaksi || '-') + "\n\n"
                             + "Lihat Kwitansi Resmi: " + (data.kwitansi_url || '') + "\n\n"
                             + "_Terima kasih atas pembayaran iuran air HIPPAM TIRTO MAKMUR._";
                const waUrl = "https://wa.me/" + cleanPhone + "?text=" + encodeURIComponent(waText);

                this.paidSuccess = {
                    nama: this.selectedCatatan?.nama,
                    jumlah: formData.get('jumlah_bayar'),
                    kwitansi_url: data.kwitansi_url,
                    wa_url: waUrl
                };

                if (isPrint && data.kwitansi_url) {
                    window.open(data.kwitansi_url, '_blank');
                }
                if (isWa && waUrl) {
                    window.open(waUrl, '_blank');
                }
            } else {
                alert(data.message || 'Gagal mencatat pembayaran');
            }
        } catch (err) {
            form.submit();
        }
    }
}
@open-pay-modal.window="
    payModal = true; 
    selectedCatatan = $event.detail;
    tagihanSisa = Number($event.detail.sisa);
    uangDiterima = Number($event.detail.sisa);
">

    {{-- Live Payment Success Toast --}}
    <div x-show="paidSuccess !== null" x-transition class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl p-4 sm:p-5 shadow-lg border-2 border-emerald-800 flex flex-col sm:flex-row items-center justify-between gap-3" x-cloak>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-extrabold">Pembayaran Berhasil Disimpan!</p>
                <p class="text-xs text-emerald-100" x-text="paidSuccess?.nama + ' • Rp' + Number(paidSuccess?.jumlah).toLocaleString('id-ID') + ' (LUNAS)'"></p>
            </div>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a :href="paidSuccess?.wa_url" target="_blank"
               class="px-3.5 py-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs rounded-xl border border-emerald-400 shadow transition flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Kirim WA</span>
            </a>
            <a :href="paidSuccess?.kwitansi_url" target="_blank"
               class="px-3.5 py-2 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl border border-emerald-500 shadow transition flex items-center justify-center gap-1">
                <span>Lihat Struk</span> &rarr;
            </a>
            <button type="button" @click="paidSuccess = null" class="p-2 text-white/80 hover:text-white">&times;</button>
        </div>
    </div>

    {{-- Header & Filters Card --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Kasir Pembayaran Air</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    Periode: <span class="font-bold text-sky-700">{{ $selectedPeriode?->nama_periode ?? '-' }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-300">
                <span>Metode:</span>
                <span class="text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-md font-bold">💵 Cash / Tunai</span>
            </div>
        </div>

        <form method="GET" action="{{ route('petugas.pembayaran.index') }}" class="flex flex-wrap gap-2">
            {{-- RT Filter --}}
            @if($userRts->count() > 1)
            <select name="rt_id" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua RT Binaan</option>
                @foreach($userRts as $rt)
                    <option value="{{ $rt->id }}" {{ $rtId == $rt->id ? 'selected' : '' }}>{{ $rt->nama_rt }}</option>
                @endforeach
            </select>
            @else
                <input type="hidden" name="rt_id" value="{{ $rtId }}">
            @endif

            {{-- Periode Filter --}}
            <select name="periode_id" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                @foreach($allPeriodes as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriode?->id == $p->id ? 'selected' : '' }}>{{ $p->nama_periode }}</option>
                @endforeach
            </select>

            {{-- Status Filter --}}
            <select name="status" onchange="this.form.submit()"
                    class="border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <option value="">Semua Status</option>
                <option value="belum_bayar" {{ $status == 'belum_bayar' ? 'selected' : '' }}>⏳ Belum Bayar</option>
                <option value="sebagian" {{ $status == 'sebagian' ? 'selected' : '' }}>💳 Sebagian</option>
                <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>✅ Lunas</option>
            </select>

            {{-- Search Bar --}}
            <div class="flex-1 min-w-[200px] relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, no. rek, alamat..."
                       class="w-full border-2 border-slate-300 rounded-xl pl-8 pr-3 py-2 text-xs font-medium focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none bg-slate-50">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl transition shadow-sm">
                Filter
            </button>

            @if($search || $status || ($userRts->count() > 1 && $rtId))
                <a href="{{ route('petugas.pembayaran.index', ['rt_id' => $rtId, 'periode_id' => $selectedPeriode?->id]) }}" 
                   class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Summary Mini Cards --}}
    @php
        $totalWarga = $catatans->count();
        $totalLunas = $catatans->where('status_bayar', 'lunas')->count();
        $totalBelum = $catatans->where('status_bayar', 'belum_bayar')->count();
        $totalSebagian = $catatans->where('status_bayar', 'sebagian')->count();
        $totalUangMasuk = $catatans->sum('total_dibayar');
        $totalSisaPiutang = $catatans->sum('sisa_tagihan');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border-2 border-slate-200 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Tagihan</span>
            <p class="text-base sm:text-lg font-black text-slate-900 mt-0.5">{{ $totalWarga }} <span class="text-xs font-bold text-slate-500">Warga</span></p>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border-2 border-emerald-200 shadow-sm bg-emerald-50/30">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Sudah Lunas</span>
            <p class="text-base sm:text-lg font-black text-emerald-800 mt-0.5">{{ $totalLunas }} <span class="text-xs font-bold text-emerald-600">Rumah</span></p>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border-2 border-teal-200 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-700">Uang Masuk</span>
            <p class="text-sm sm:text-base font-black text-teal-800 font-mono mt-0.5">Rp{{ number_format($totalUangMasuk, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border-2 border-rose-200 shadow-sm bg-rose-50/30">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">Sisa Piutang</span>
            <p class="text-sm sm:text-base font-black text-rose-800 font-mono mt-0.5">Rp{{ number_format($totalSisaPiutang, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- List Pembayaran Warga --}}
    @if($catatans->isEmpty())
        <div class="bg-white rounded-2xl border-2 border-slate-200 p-8 text-center">
            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-700">Tidak ada data tagihan</h4>
            <p class="text-xs text-slate-400 mt-1">Pastikan angka meter sudah diinput atau ubah filter di atas.</p>
        </div>
    @else
    <div class="space-y-3">
        @foreach($catatans as $catatan)
        @php
            $isLunas = $catatan->status_bayar === 'lunas';
            $isSebagian = $catatan->status_bayar === 'sebagian';
            $cleanPhone = preg_replace('/[^0-9]/', '', $catatan->pelanggan->no_hp ?? '');
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $latestPay = $catatan->pembayarans->last();
            $waMsg = "*BUKTI PEMBAYARAN AIR - HIPPAM TIRTO MAKMUR*\n"
                   . "----------------------------------------\n"
                   . "No. Pelanggan: {$catatan->pelanggan->no_rekening}\n"
                   . "Nama Warga   : {$catatan->pelanggan->nama}\n"
                   . "Alamat       : {$catatan->pelanggan->alamat}\n"
                   . "Periode      : {$catatan->periode->nama_periode}\n\n"
                   . "*Rincian Pemakaian Air:*\n"
                   . "- Stand Lalu : " . number_format($catatan->angka_lalu) . "\n"
                   . "- Stand Kini : " . ($catatan->angka_ini !== null ? number_format($catatan->angka_ini) : '-') . "\n"
                   . "- Pemakaian  : " . number_format($catatan->pemakaian) . " m³\n\n"
                   . "*Rincian Biaya:*\n"
                   . "- Biaya Air  : Rp" . number_format($catatan->biaya_pemakaian, 0, ',', '.') . "\n"
                   . "- Biaya Admin: Rp" . number_format($catatan->biaya_admin, 0, ',', '.') . "\n"
                   . ($catatan->tunggakan_lalu > 0 ? "- Tunggakan  : Rp" . number_format($catatan->tunggakan_lalu, 0, ',', '.') . "\n" : "")
                   . "- Total Wajib: Rp" . number_format($catatan->total_tagihan, 0, ',', '.') . "\n"
                   . "- Total Bayar: Rp" . number_format($catatan->total_dibayar, 0, ',', '.') . "\n"
                   . "- Sisa       : Rp" . number_format($catatan->sisa_tagihan, 0, ',', '.') . "\n"
                   . "*Status      : " . strtoupper($catatan->status_bayar) . "* " . ($isLunas ? '✅' : '⏳') . "\n\n"
                   . ($latestPay ? "No. Kwitansi : {$latestPay->no_transaksi}\nTanggal Bayar: " . $latestPay->tanggal_bayar->format('d/m/Y') . "\n\n" : "")
                   . "_Terima kasih atas pembayaran iuran air HIPPAM TIRTO MAKMUR._";
            $waCardUrl = "https://wa.me/" . ($cleanPhone ?: '') . "?text=" . urlencode($waMsg);
        @endphp

        <div class="bg-white rounded-2xl border-2 transition shadow-sm p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $isLunas ? 'border-emerald-200 bg-emerald-50/15' : ($isSebagian ? 'border-amber-200 bg-amber-50/15' : 'border-slate-200 hover:border-slate-300') }}"
             data-catatan-id="{{ $catatan->id }}">
            
            {{-- Left: Pelanggan & Meter Info --}}
            <div class="space-y-1.5 flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-300">
                        {{ $catatan->pelanggan->no_rekening }}
                    </span>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 truncate">
                        {{ $catatan->pelanggan->nama }}
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">
                        &bull; {{ $catatan->pelanggan->rt->nama_rt }}
                    </span>
                </div>

                <p class="text-xs text-slate-500 truncate">
                    <span class="font-semibold text-slate-700">{{ $catatan->pelanggan->dusun ? 'Dusun ' . preg_replace('/^dusun\s+/i', '', $catatan->pelanggan->dusun) : $catatan->pelanggan->alamat }}</span>
                    @if($catatan->pelanggan->no_rt || $catatan->pelanggan->no_rw)
                        &bull; <span class="font-mono font-bold text-sky-700">RT {{ $catatan->pelanggan->no_rt ?? '-' }} / RW {{ $catatan->pelanggan->no_rw ?? '-' }}</span>
                    @endif
                    @if($catatan->pelanggan->no_hp)
                        &bull; <span class="font-mono text-slate-500">{{ $catatan->pelanggan->no_hp }}</span>
                    @endif
                </p>

                <div class="flex items-center gap-4 text-xs pt-1 flex-wrap font-medium text-slate-600">
                    <span class="bg-slate-100 px-2 py-0.5 rounded text-[11px] font-mono">
                        Meter: <strong>{{ number_format($catatan->angka_lalu) }}</strong> &rarr; <strong>{{ $catatan->angka_ini !== null ? number_format($catatan->angka_ini) : '-' }}</strong>
                    </span>
                    <span class="font-bold text-sky-800">
                        Pemakaian: {{ number_format($catatan->pemakaian) }} m³
                    </span>
                    @if($catatan->tunggakan_lalu > 0)
                        <span class="text-rose-600 font-bold">
                            +Tunggakan: Rp{{ number_format($catatan->tunggakan_lalu, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Center: Financial Numbers --}}
            <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                <div class="text-left md:text-right">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Tagihan</span>
                    <span class="text-sm font-black text-slate-900 font-mono">Rp{{ number_format($catatan->total_tagihan, 0, ',', '.') }}</span>
                    <div class="text-[11px] text-slate-500">
                        Dibayar: <span class="font-mono font-bold text-teal-700" data-dibayar>Rp{{ number_format($catatan->total_dibayar, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="text-right min-w-[110px]">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Tagihan</span>
                    <span class="text-base font-black font-mono {{ $catatan->sisa_tagihan > 0 ? 'text-rose-700' : 'text-emerald-700' }}" data-sisa>
                        Rp{{ number_format($catatan->sisa_tagihan, 0, ',', '.') }}
                    </span>
                    
                    {{-- Status Badge --}}
                    <div class="mt-0.5 flex justify-end">
                        <span data-badge class="px-2.5 py-0.5 text-[10px] font-black rounded-full uppercase tracking-wider border {{ $isLunas ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($isSebagian ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300') }}">
                            {{ $isLunas ? '✓ Lunas' : ($isSebagian ? '⚡ Sebagian' : '⏳ Belum Bayar') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Actions Toolbar --}}
            <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100 shrink-0">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    @if(!$isLunas)
                        @if($catatan->angka_ini !== null)
                        <button type="button" data-pay-btn
                                onclick="window.dispatchEvent(new CustomEvent('open-pay-modal', { detail: {
                                    id: '{{ $catatan->id }}',
                                    nama: '{{ addslashes($catatan->pelanggan->nama) }}',
                                    no_rek: '{{ $catatan->pelanggan->no_rekening }}',
                                    no_hp: '{{ $catatan->pelanggan->no_hp }}',
                                    clean_phone: '{{ $cleanPhone }}',
                                    alamat: '{{ addslashes($catatan->pelanggan->alamat) }}',
                                    sisa: '{{ $catatan->sisa_tagihan }}',
                                    total: '{{ $catatan->total_tagihan }}',
                                    periode: '{{ $catatan->periode->nama_periode }}'
                                } }))"
                                class="flex-1 py-2.5 px-4 bg-gradient-to-r from-teal-600 via-teal-700 to-sky-700 hover:from-teal-500 hover:to-sky-600 active:scale-[0.99] text-white text-xs font-extrabold rounded-xl transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Bayar Kasir (Tunai)</span>
                        </button>
                        @else
                        <div class="flex-1 py-2.5 bg-slate-100 border border-slate-300 text-slate-500 text-xs font-bold rounded-xl text-center">
                            ⚠ Angka meter belum diinput di periode ini
                        </div>
                        @endif
                    @endif
                    <a href="{{ $waCardUrl }}" target="_blank"
                       class="px-3 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white border border-emerald-600 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 shrink-0"
                       title="Kirim Bukti Pembayaran / Info Tagihan via WhatsApp">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Kirim WA</span>
                    </a>
                    <a href="{{ route('kwitansi.show', $catatan->id) }}" target="_blank"
                       class="px-3 py-2.5 bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0"
                       title="Lihat Struk Kwitansi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Struk</span>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- MODAL PEMBAYARAN: CASH / TUNAI --}}
    <div x-show="payModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm" @click="payModal = false"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg border-2 border-slate-300 z-10 overflow-hidden my-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-teal-950 px-6 py-4 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20">
                        <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Kasir Pembayaran Air (Tunai)</h3>
                        <p class="text-xs text-sky-300 font-medium" x-text="selectedCatatan?.nama + ' (' + selectedCatatan?.no_rek + ')'"></p>
                    </div>
                </div>
                <button @click="payModal = false" class="p-1.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="bg-slate-100 px-6 py-3 border-b-2 border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Yang Wajib Dibayar:</span>
                    <p class="text-xs text-slate-600 font-semibold" x-text="'Periode: ' + (selectedCatatan?.periode ?? '')"></p>
                </div>
                <div class="text-right">
                    <span class="text-xl font-black text-rose-700 font-mono" x-text="'Rp ' + Number(tagihanSisa).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <form action="{{ route('petugas.pembayaran.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="catatan_meter_id" :value="selectedCatatan?.id">
                <input type="hidden" name="tanggal_bayar" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="metode" value="tunai">

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase">Jumlah Bayar (Rp) <span class="text-rose-500">*</span></label>
                        <button type="button" @click="setUangPas()" class="text-[11px] font-bold text-sky-700 hover:underline bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">
                            ⚡ Uang Pas
                        </button>
                    </div>
                    <input type="number" name="jumlah_bayar" x-model.number="tagihanSisa"
                           min="100" :max="tagihanSisa" step="100" required
                           class="w-full border-2 border-slate-300 rounded-xl px-4 py-2.5 text-lg font-mono font-black text-slate-900 focus:ring-2 focus:ring-teal-400 focus:border-transparent outline-none bg-slate-50"
                           placeholder="0">
                </div>

                <div class="bg-slate-50 p-3.5 rounded-2xl border-2 border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700">Uang Diterima dari Warga:</label>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="setNominal(50000)" class="px-2 py-1 bg-white border border-slate-300 rounded-lg text-[10px] font-bold hover:bg-slate-100">50rb</button>
                            <button type="button" @click="setNominal(100000)" class="px-2 py-1 bg-white border border-slate-300 rounded-lg text-[10px] font-bold hover:bg-slate-100">100rb</button>
                            <button type="button" @click="setNominal(200000)" class="px-2 py-1 bg-white border border-slate-300 rounded-lg text-[10px] font-bold hover:bg-slate-100">200rb</button>
                        </div>
                    </div>
                    <input type="number" x-model.number="uangDiterima"
                           class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono font-bold focus:ring-2 focus:ring-teal-400 focus:outline-none"
                           placeholder="Masukkan uang yang diberikan warga...">

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-xs">
                        <span class="font-bold text-slate-600">Kembalian:</span>
                        <span class="font-mono font-black text-base"
                              :class="uangDiterima >= tagihanSisa ? 'text-emerald-700' : 'text-rose-600'"
                              x-text="uangDiterima >= tagihanSisa ? 'Rp ' + Number(hitungKembalian()).toLocaleString('id-ID') : 'Uang Kurang'">
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Pembayaran (Opsional)</label>
                    <input type="text" name="catatan" maxlength="255"
                           class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:ring-2 focus:ring-teal-400 focus:outline-none"
                           placeholder="Misal: Diterima tunai oleh Petugas RT">
                </div>

                <div class="pt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <button type="button" @click="submitPayment($event, false, false)"
                            class="py-3 px-3 bg-teal-600 hover:bg-teal-700 active:scale-[0.99] text-white font-extrabold text-xs rounded-xl transition shadow-md flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Simpan</span>
                    </button>
                    <button type="button" @click="submitPayment($event, false, true)"
                            class="py-3 px-3 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-extrabold text-xs rounded-xl transition shadow-md flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Simpan &amp; WA</span>
                    </button>
                    <button type="button" @click="submitPayment($event, true, false)"
                            class="py-3 px-3 bg-sky-600 hover:bg-sky-700 active:scale-[0.99] text-white font-extrabold text-xs rounded-xl transition shadow-md flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Simpan &amp; Struk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
