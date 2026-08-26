@extends('layouts.admin')

@section('title', 'Dashboard Global')
@section('page_title', 'Dashboard Monitoring HIPPAM')

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header Controls & Period Selector -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 sm:p-6 bg-white rounded-2xl border-2 border-slate-200 shadow-md">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-base sm:text-xl font-extrabold text-slate-900">Periode Tagihan: <span class="text-sky-700">{{ $selectedPeriode?->nama_periode }}</span></h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-black {{ $selectedPeriode?->status === 'aktif' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-300' }}">
                    {{ strtoupper($selectedPeriode?->status ?? 'DRAFT') }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 font-medium">Monitoring operasional dan keuangan seluruh RT &amp; Warga HIPPAM TIRTO MAKMUR</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <label for="periode_id" class="text-xs font-bold text-slate-600 shrink-0">Periode:</label>
                <select name="periode_id" id="periode_id" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3.5 py-2 bg-slate-50 border-2 border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none shadow-xs">
                    @foreach($allPeriodes as $p)
                        <option value="{{ $p->id }}" {{ $selectedPeriode?->id == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }} ({{ ucfirst($p->status) }})
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white rounded-xl shadow-md transition shrink-0">
                <span>Rekap &amp; Laporan</span> &rarr;
            </a>
        </div>
    </div>

    <!-- 4 Primary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Total Tagihan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md flex flex-col justify-between relative overflow-hidden group hover:border-sky-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Tagihan</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 tracking-tight font-mono truncate">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</p>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-1 font-semibold flex items-center gap-1">
                    <span>{{ $totalPelanggan }} Warga Terdaftar</span> &bull; <span>{{ $totalRt }} RT</span>
                </p>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-sky-500"></div>
        </div>

        <!-- Total Terbayar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md flex flex-col justify-between relative overflow-hidden group hover:border-emerald-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Penerimaan Kas (Lunas)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 tracking-tight font-mono truncate">Rp{{ number_format($totalTerbayar, 0, ',', '.') }}</p>
                <p class="text-[11px] sm:text-xs text-emerald-700 font-bold mt-1">
                    {{ $totalTagihan > 0 ? round(($totalTerbayar / $totalTagihan) * 100, 1) : 0 }}% Terealisasi ({{ $statusCounts['lunas'] }} Lunas)
                </p>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-emerald-500"></div>
        </div>

        <!-- Total Tunggakan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md flex flex-col justify-between relative overflow-hidden group hover:border-rose-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Sisa Piutang / Tunggakan</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-rose-600 tracking-tight font-mono truncate">Rp{{ number_format($totalTunggakan, 0, ',', '.') }}</p>
                <p class="text-[11px] sm:text-xs text-rose-700 font-bold mt-1">
                    {{ $statusCounts['belum_bayar'] }} Belum Bayar &bull; {{ $statusCounts['sebagian'] }} Cicil
                </p>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-rose-500"></div>
        </div>

        <!-- Pemakaian Air & Input Progres -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200 shadow-md flex flex-col justify-between relative overflow-hidden group hover:border-teal-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-teal-700 uppercase tracking-wider">Volume Air &amp; Progres</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 border border-teal-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
            </div>
            <div class="mt-3 sm:mt-4">
                <p class="text-xl sm:text-2xl lg:text-3xl font-black text-teal-600 tracking-tight font-mono">{{ number_format($totalPemakaianM3) }} <span class="text-xs sm:text-sm font-bold text-slate-600">m³</span></p>
                <div class="mt-2">
                    <div class="flex justify-between text-[10px] sm:text-[11px] text-slate-600 font-bold mb-1">
                        <span>Meter: {{ $totalRumahTercatat }}/{{ $totalPelanggan }}</span>
                        <span class="text-teal-700">{{ $progressPersen }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                        <div class="bg-teal-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPersen }}%"></div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-teal-500"></div>
        </div>

    </div>

    <!-- Active Tariff Highlight Card -->
    @if($activeTarif)
        <div class="bg-gradient-to-r from-sky-950 via-slate-900 to-teal-950 rounded-2xl p-4 sm:p-5 text-white shadow-lg border-2 border-sky-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-sky-500/20 border border-sky-400/40 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-white">Skema Tarif Aktif: {{ $activeTarif->nama_skema }}</h4>
                    <p class="text-xs text-sky-200 mt-0.5 leading-relaxed">
                        Standar (&le;{{ $activeTarif->batas_kuota_standar }} m³): <strong>Rp{{ number_format($activeTarif->tarif_standar, 0, ',', '.') }}/m³</strong> &bull; 
                        Progresif (&gt;{{ $activeTarif->batas_kuota_standar }} m³): <strong>Rp{{ number_format($activeTarif->tarif_progresif, 0, ',', '.') }}/m³</strong> &bull; 
                        Admin: <strong>Rp{{ number_format($activeTarif->biaya_admin, 0, ',', '.') }}</strong>
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.tarif.index') }}" class="px-4 py-2 text-xs font-bold text-sky-200 bg-sky-900/80 hover:bg-sky-800 border border-sky-700/60 rounded-xl transition shrink-0 shadow self-stretch sm:self-auto text-center">
                Kelola Tarif &rarr;
            </a>
        </div>
    @endif

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- Revenue & Billing Chart -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border-2 border-slate-200 shadow-md">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-slate-900">Tren Penerimaan Kas (Rp)</h4>
                    <p class="text-[11px] text-slate-500 font-medium">Pembayaran masuk per bulan</p>
                </div>
                <span class="text-[10px] sm:text-xs px-2.5 py-1 bg-sky-100 text-sky-800 font-bold rounded-lg border border-sky-300">6 Bulan Terakhir</span>
            </div>
            <div class="h-56 sm:h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Water Usage Chart -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border-2 border-slate-200 shadow-md">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-slate-900">Tren Pemakaian Air (m³)</h4>
                    <p class="text-[11px] text-slate-500 font-medium">Volume air yang dikonsumsi</p>
                </div>
                <span class="text-[10px] sm:text-xs px-2.5 py-1 bg-teal-100 text-teal-800 font-bold rounded-lg border border-teal-300">Konsumsi m³</span>
            </div>
            <div class="h-56 sm:h-64">
                <canvas id="usageChart"></canvas>
            </div>
        </div>

    </div>

    <!-- RT Summary Table & Top Debtors -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- RT Summary Performance (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b-2 border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-slate-900">Rekapitulasi Tagihan per Wilayah RT</h4>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Performa penagihan periode {{ $selectedPeriode?->nama_periode }}</p>
                </div>
                <a href="{{ route('admin.laporan.index', ['tab' => 'rekap-rt']) }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 shrink-0">Lihat Semua &rarr;</a>
            </div>

            <div class="overflow-x-auto flex-1 p-2 sm:p-3">
                <table class="w-full text-xs text-left text-slate-700">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="p-3">RT / Wilayah</th>
                            <th class="p-3 text-center">Warga / Progres</th>
                            <th class="p-3 text-center">Volume (m³)</th>
                            <th class="p-3 text-right">Tagihan</th>
                            <th class="p-3 text-right">Terbayar</th>
                            <th class="p-3 text-right">Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($rtSummaries as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                                        <span>{{ $item['rt']->nama_rt }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-medium ml-4">Petugas: {{ $item['rt']->petugas->pluck('name')->join(', ') ?: 'Belum ada' }}</p>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="font-extrabold {{ $item['tercatat'] == $item['total_warga'] ? 'text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200' : 'text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200' }}">
                                        {{ $item['tercatat'] }} / {{ $item['total_warga'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-center font-mono font-bold">{{ $item['m3'] }} m³</td>
                                <td class="p-3 text-right font-mono font-black text-slate-900">Rp{{ number_format($item['tagihan'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono font-black text-emerald-700">Rp{{ number_format($item['terbayar'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono font-black {{ $item['tunggakan'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    Rp{{ number_format($item['tunggakan'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Debtors Card (1 Col) -->
        <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md p-4 sm:p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b-2 border-slate-200">
                    <h4 class="text-xs sm:text-sm font-black text-slate-900">Tunggakan Terbesar</h4>
                    <span class="text-[10px] sm:text-[11px] font-black text-rose-700 bg-rose-100 border border-rose-300 px-2.5 py-0.5 rounded-full">Prioritas</span>
                </div>

                <div class="divide-y divide-slate-200 mt-2">
                    @forelse($topDebtors as $debtor)
                        <div class="py-3 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <h5 class="text-xs font-bold text-slate-900 truncate">{{ $debtor->pelanggan->nama }}</h5>
                                <p class="text-[10px] text-slate-500 font-mono truncate">No. {{ $debtor->pelanggan->no_rekening }} &bull; {{ $debtor->pelanggan->rt->nama_rt }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs font-black text-rose-700 font-mono">Rp{{ number_format($debtor->sisa_tagihan, 0, ',', '.') }}</p>
                                <a href="{{ route('admin.pelanggan.show', $debtor->pelanggan->id) }}" class="text-[10px] text-sky-600 font-bold hover:underline">Detail &rarr;</a>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-400 font-medium">Tidak ada tunggakan berjalan.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chart 1: Revenue
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Penerimaan Kas (Rp)',
                    data: {!! json_encode($chartRevenue) !!},
                    backgroundColor: 'rgba(2, 132, 199, 0.85)',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp' + (value / 1000).toLocaleString('id-ID') + 'k';
                            }
                        }
                    }
                }
            }
        });

        // Chart 2: Usage
        const ctxUsage = document.getElementById('usageChart').getContext('2d');
        new Chart(ctxUsage, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Konsumsi Air (m³)',
                    data: {!! json_encode($chartUsage) !!},
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.15)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0d9488',
                    pointRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' m³';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
