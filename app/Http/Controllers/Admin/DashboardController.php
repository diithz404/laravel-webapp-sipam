<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
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

        // 1. Single aggregated KPI Stats for selected period
        $stats = CatatanMeter::where('periode_id', $selectedPeriode?->id)
            ->selectRaw('
                COALESCE(SUM(total_tagihan), 0) as total_tagihan,
                COALESCE(SUM(total_dibayar), 0) as total_dibayar,
                COALESCE(SUM(sisa_tagihan), 0) as total_tunggakan,
                COALESCE(SUM(pemakaian), 0) as total_pemakaian_m3,
                COUNT(CASE WHEN angka_ini IS NOT NULL THEN 1 END) as total_rumah_tercatat,
                COUNT(CASE WHEN status_bayar = "lunas" THEN 1 END) as count_lunas,
                COUNT(CASE WHEN status_bayar = "sebagian" THEN 1 END) as count_sebagian,
                COUNT(CASE WHEN status_bayar = "belum_bayar" THEN 1 END) as count_belum_bayar
            ')->first();

        $totalTagihan = (float) ($stats?->total_tagihan ?? 0);
        $totalTerbayar = (float) ($stats?->total_dibayar ?? 0);
        $totalTunggakan = (float) ($stats?->total_tunggakan ?? 0);
        $totalPemakaianM3 = (int) ($stats?->total_pemakaian_m3 ?? 0);
        $totalRumahTercatat = (int) ($stats?->total_rumah_tercatat ?? 0);

        $totalPelanggan = Pelanggan::where('status', 'aktif')->count();
        $totalRt = Rt::count();

        $progressPersen = $totalPelanggan > 0 ? round(($totalRumahTercatat / $totalPelanggan) * 100) : 0;

        $statusCounts = [
            'lunas' => (int) ($stats?->count_lunas ?? 0),
            'sebagian' => (int) ($stats?->count_sebagian ?? 0),
            'belum_bayar' => (int) ($stats?->count_belum_bayar ?? 0),
        ];

        // 2. Top 5 Tunggakan Pelanggan with eager loading
        $topDebtors = CatatanMeter::with(['pelanggan.rt'])
            ->where('periode_id', $selectedPeriode?->id)
            ->where('sisa_tagihan', '>', 0)
            ->orderBy('sisa_tagihan', 'desc')
            ->take(5)
            ->get();

        // 3. Batch RT Summary calculation (Eliminating N+1 queries)
        $rtStats = CatatanMeter::join('pelanggans', 'catatan_meters.pelanggan_id', '=', 'pelanggans.id')
            ->where('catatan_meters.periode_id', $selectedPeriode?->id)
            ->groupBy('pelanggans.rt_id')
            ->selectRaw('
                pelanggans.rt_id,
                COUNT(CASE WHEN catatan_meters.angka_ini IS NOT NULL THEN 1 END) as tercatat,
                COALESCE(SUM(catatan_meters.pemakaian), 0) as m3,
                COALESCE(SUM(catatan_meters.total_tagihan), 0) as tagihan,
                COALESCE(SUM(catatan_meters.total_dibayar), 0) as terbayar,
                COALESCE(SUM(catatan_meters.sisa_tagihan), 0) as tunggakan
            ')
            ->get()
            ->keyBy('rt_id');

        $rtSummaries = Rt::withCount(['pelanggans' => function ($q) {
                $q->where('status', 'aktif');
            }])
            ->with(['petugas'])
            ->orderBy('kode_rt')
            ->get()
            ->map(function ($rt) use ($rtStats) {
                $stat = $rtStats->get($rt->id);

                return [
                    'rt' => $rt,
                    'total_warga' => $rt->pelanggans_count,
                    'tercatat' => (int) ($stat?->tercatat ?? 0),
                    'm3' => (int) ($stat?->m3 ?? 0),
                    'tagihan' => (float) ($stat?->tagihan ?? 0),
                    'terbayar' => (float) ($stat?->terbayar ?? 0),
                    'tunggakan' => (float) ($stat?->tunggakan ?? 0),
                ];
            });

        // 4. Monthly trends for Chart.js (Single grouped query)
        $chartPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->take(6)->get()->reverse()->values();
        $chartPeriodeIds = $chartPeriodes->pluck('id');

        $trendStats = CatatanMeter::whereIn('periode_id', $chartPeriodeIds)
            ->groupBy('periode_id')
            ->selectRaw('
                periode_id,
                COALESCE(SUM(total_dibayar), 0) as revenue,
                COALESCE(SUM(pemakaian), 0) as usage_m3
            ')
            ->get()
            ->keyBy('periode_id');

        $chartLabels = $chartPeriodes->pluck('nama_periode');
        $chartRevenue = $chartPeriodes->map(fn($p) => (float) ($trendStats->get($p->id)?->revenue ?? 0));
        $chartUsage = $chartPeriodes->map(fn($p) => (int) ($trendStats->get($p->id)?->usage_m3 ?? 0));

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
            'chartLabels',
            'chartRevenue',
            'chartUsage',
            'activeTarif'
        ));
    }
}
