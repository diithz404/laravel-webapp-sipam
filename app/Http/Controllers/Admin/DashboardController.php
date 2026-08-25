<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
use App\Models\Pembayaran;
use App\Models\ActivityLog;
use App\Models\Tarif;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $periodeId = $request->query('periode_id', $activePeriode?->id);
        $selectedPeriode = PeriodeTagihan::find($periodeId) ?? $activePeriode;

        $allPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        // Global KPI Stats for selected period
        $queryCatatan = CatatanMeter::where('periode_id', $selectedPeriode?->id);

        $totalTagihan = (clone $queryCatatan)->sum('total_tagihan');
        $totalTerbayar = (clone $queryCatatan)->sum('total_dibayar');
        $totalTunggakan = (clone $queryCatatan)->sum('sisa_tagihan');
        $totalPemakaianM3 = (clone $queryCatatan)->sum('pemakaian');

        $totalPelanggan = Pelanggan::where('status', 'aktif')->count();
        $totalRt = Rt::count();

        // Meter progress
        $totalRumahTercatat = (clone $queryCatatan)->whereNotNull('angka_ini')->count();
        $progressPersen = $totalPelanggan > 0 ? round(($totalRumahTercatat / $totalPelanggan) * 100) : 0;

        // Payment status counts
        $statusCounts = [
            'lunas' => (clone $queryCatatan)->where('status_bayar', 'lunas')->count(),
            'sebagian' => (clone $queryCatatan)->where('status_bayar', 'sebagian')->count(),
            'belum_bayar' => (clone $queryCatatan)->where('status_bayar', 'belum_bayar')->count(),
        ];

        // Top 5 Tunggakan Pelanggan
        $topDebtors = (clone $queryCatatan)
            ->with(['pelanggan.rt'])
            ->where('sisa_tagihan', '>', 0)
            ->orderBy('sisa_tagihan', 'desc')
            ->take(5)
            ->get();

        // RT Summary for table
        $rtSummaries = Rt::withCount('pelanggans')
            ->with(['petugas'])
            ->get()
            ->map(function ($rt) use ($selectedPeriode) {
                $catatan = CatatanMeter::whereHas('pelanggan', function ($q) use ($rt) {
                    $q->where('rt_id', $rt->id);
                })->where('periode_id', $selectedPeriode?->id);

                return [
                    'rt' => $rt,
                    'total_warga' => $rt->pelanggans_count,
                    'tercatat' => (clone $catatan)->whereNotNull('angka_ini')->count(),
                    'm3' => (clone $catatan)->sum('pemakaian'),
                    'tagihan' => (clone $catatan)->sum('total_tagihan'),
                    'terbayar' => (clone $catatan)->sum('total_dibayar'),
                    'tunggakan' => (clone $catatan)->sum('sisa_tagihan'),
                ];
            });

        // Recent Activity Logs
        $recentLogs = ActivityLog::with('user')->latest()->take(6)->get();

        // Monthly trends for Chart.js (last 6 months)
        $chartPeriodes = PeriodeTagihan::orderBy('tahun', 'asc')->orderBy('bulan', 'asc')->take(6)->get();
        $chartLabels = $chartPeriodes->pluck('nama_periode');
        $chartRevenue = $chartPeriodes->map(fn($p) => CatatanMeter::where('periode_id', $p->id)->sum('total_dibayar'));
        $chartUsage = $chartPeriodes->map(fn($p) => CatatanMeter::where('periode_id', $p->id)->sum('pemakaian'));

        $activeTarif = Tarif::getActiveTarif();

        return view('admin.dashboard', compact(
            'selectedPeriode',
            'allPeriodes',
            'totalTagihan',
            'totalTerbayar',
            'totalTunggakan',
            'totalPemakaianM3',
            'totalPelanggan',
            'totalRt',
            'totalRumahTercatat',
            'progressPersen',
            'statusCounts',
            'topDebtors',
            'rtSummaries',
            'recentLogs',
            'chartLabels',
            'chartRevenue',
            'chartUsage',
            'activeTarif'
        ));
    }
}
