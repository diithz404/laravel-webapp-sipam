@extends('layouts.admin')

@section('title', 'Pengaturan Tarif & Biaya')
@section('page_title', 'Master Tarif & Skema Progresif')

@section('content')
<div class="space-y-6" x-data="{
    nama_skema: 'Tarif HIPPAM Tirto Makmur ' + new Date().getFullYear(),
    tarif_standar: 350,
    batas_kuota_standar: 20,
    tarif_progresif: 400,
    biaya_admin: 2000,
    tanggal_berlaku: new Date().toISOString().split('T')[0],
    keterangan: '',
    modalCreate: false
}">

    <!-- Active Tariff Showcase Card -->
    <div class="bg-gradient-to-r from-sky-900 via-sky-800 to-teal-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-sky-700/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        SKEMA TARIF AKTIF
                    </span>
                    <span class="text-xs text-sky-200">Berlaku sejak: {{ $activeTarif?->tanggal_berlaku->format('d F Y') }}</span>
                </div>
                <h3 class="text-2xl font-black tracking-tight text-white">{{ $activeTarif?->nama_skema ?? 'Tarif Standar HIPPAM' }}</h3>
                <p class="text-xs text-sky-200 mt-1 max-w-xl">
                    {{ $activeTarif?->keterangan ?? 'Tarif dasar mengacu pada ketetapan musyawarah HIPPAM Tirto Makmur Desa Argosari' }}
                </p>
            </div>

            <button @click="modalCreate = true" 
                    class="px-5 py-3 rounded-2xl bg-white text-sky-900 hover:bg-sky-50 font-bold text-xs shadow-lg transition flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tetapkan Skema Baru</span>
            </button>
        </div>

        <!-- Breakdown Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-6 border-t border-sky-700/60">
            <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                <span class="text-[10px] uppercase tracking-wider text-sky-200 font-semibold">Tarif Standar</span>
                <p class="text-2xl font-black text-white mt-1">Rp{{ number_format($activeTarif?->tarif_standar ?? 350, 0, ',', '.') }}<span class="text-xs font-normal text-sky-200">/m³</span></p>
                <p class="text-[11px] text-sky-200 mt-0.5">Pemakaian 0 s/d {{ $activeTarif?->batas_kuota_standar ?? 20 }} m³</p>
            </div>

            <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                <span class="text-[10px] uppercase tracking-wider text-sky-200 font-semibold">Batas Kuota Standar</span>
                <p class="text-2xl font-black text-white mt-1">{{ $activeTarif?->batas_kuota_standar ?? 20 }} <span class="text-xs font-normal text-sky-200">m³</span></p>
                <p class="text-[11px] text-sky-200 mt-0.5">Batas tarif tingkat 1</p>
            </div>

            <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                <span class="text-[10px] uppercase tracking-wider text-sky-200 font-semibold">Tarif Progresif</span>
                <p class="text-2xl font-black text-amber-300 mt-1">Rp{{ number_format($activeTarif?->tarif_progresif ?? 400, 0, ',', '.') }}<span class="text-xs font-normal text-sky-200">/m³</span></p>
                <p class="text-[11px] text-sky-200 mt-0.5">Kelebihan di atas {{ $activeTarif?->batas_kuota_standar ?? 20 }} m³</p>
            </div>

            <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                <span class="text-[10px] uppercase tracking-wider text-sky-200 font-semibold">Biaya Administrasi</span>
                <p class="text-2xl font-black text-emerald-300 mt-1">Rp{{ number_format($activeTarif?->biaya_admin ?? 2000, 0, ',', '.') }}</p>
                <p class="text-[11px] text-sky-200 mt-0.5">Flat per rekening/bulan</p>
            </div>
        </div>
    </div>

    <!-- Multi-Tier Simulation & Historical Rates -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Formula Calculator Preview (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4" x-data="{ testUsage: 55 }">
            <h4 class="text-sm font-bold text-slate-800">Simulasi Rumus Excel PRD</h4>
            <p class="text-xs text-slate-400">Verifikasi formula tagihan otomatis berdasarkan angka pemakaian (contoh Saiful 55 m³)</p>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Simulasi Pemakaian (m³):</label>
                <div class="flex items-center gap-3">
                    <input type="range" min="0" max="100" x-model.number="testUsage" class="flex-1 accent-sky-600">
                    <input type="number" x-model.number="testUsage" class="w-16 px-2 py-1 border border-slate-300 rounded-lg text-xs font-bold text-center font-mono">
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs space-y-2 font-mono">
                <div class="flex justify-between text-slate-600">
                    <span>Standar (<=20 m³):</span>
                    <span x-text="Math.min(testUsage, 20) + ' m³ x Rp350 = Rp' + (Math.min(testUsage, 20) * 350).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Progresif (>20 m³):</span>
                    <span x-text="Math.max(0, testUsage - 20) + ' m³ x Rp400 = Rp' + (Math.max(0, testUsage - 20) * 400).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Admin:</span>
                    <span>Rp2.000</span>
                </div>
                <div class="flex justify-between font-bold text-sky-800 pt-2 border-t border-slate-200 text-sm">
                    <span>Total Tagihan:</span>
                    <span x-text="'Rp' + ((Math.min(testUsage, 20) * 350) + (Math.max(0, testUsage - 20) * 400) + 2000).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </div>

        <!-- Historical Tariffs Table (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100">
                <h4 class="text-sm font-bold text-slate-800">Histori Perubahan & Versi Tarif</h4>
                <p class="text-xs text-slate-400">Tagihan pada periode yang telah ditutup tidak akan terpengaruh saat tarif baru dibuat</p>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-xs text-left text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Skema Tarif</th>
                            <th class="p-3.5 text-center">Standar (Rp)</th>
                            <th class="p-3.5 text-center">Kuota (m³)</th>
                            <th class="p-3.5 text-center">Progresif (Rp)</th>
                            <th class="p-3.5 text-center">Admin (Rp)</th>
                            <th class="p-3.5">Mulai Berlaku</th>
                            <th class="p-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($allTarifs as $t)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 font-bold text-slate-900">{{ $t->nama_skema }}</td>
                                <td class="p-3.5 text-center font-mono font-semibold">Rp{{ number_format($t->tarif_standar, 0, ',', '.') }}</td>
                                <td class="p-3.5 text-center font-mono">{{ $t->batas_kuota_standar }} m³</td>
                                <td class="p-3.5 text-center font-mono font-semibold text-amber-600">Rp{{ number_format($t->tarif_progresif, 0, ',', '.') }}</td>
                                <td class="p-3.5 text-center font-mono">Rp{{ number_format($t->biaya_admin, 0, ',', '.') }}</td>
                                <td class="p-3.5 text-slate-600">{{ $t->tanggal_berlaku->format('d/m/Y') }}</td>
                                <td class="p-3.5 text-center">
                                    @if($t->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">Arsip</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Create Tarif Baru -->
    <div x-show="modalCreate" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalCreate = false"></div>
            <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-sky-700 px-6 py-4 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold">Tetapkan Skema Tarif Baru</h3>
                    <button @click="modalCreate = false" class="text-white/80 hover:text-white">&times;</button>
                </div>
                <form action="{{ route('admin.tarif.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Skema Tarif</label>
                        <input type="text" name="nama_skema" x-model="nama_skema" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif Standar (Rp/m³)</label>
                            <input type="number" step="0.01" name="tarif_standar" x-model.number="tarif_standar" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Kuota Standar (m³)</label>
                            <input type="number" name="batas_kuota_standar" x-model.number="batas_kuota_standar" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif Progresif (Rp/m³)</label>
                            <input type="number" step="0.01" name="tarif_progresif" x-model.number="tarif_progresif" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Biaya Administrasi (Rp)</label>
                            <input type="number" step="0.01" name="biaya_admin" x-model.number="biaya_admin" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Berlaku Efektif</label>
                        <input type="date" name="tanggal_berlaku" x-model="tanggal_berlaku" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Tarif ini akan aktif untuk perhitungan periode yang dibuka pada atau setelah tanggal ini.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Dasar SK</label>
                        <textarea name="keterangan" x-model="keterangan" rows="2" placeholder="Dasar musyawarah warga..." class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 flex justify-end gap-2">
                        <button type="button" @click="modalCreate = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow">Aktifkan Skema Tarif</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
