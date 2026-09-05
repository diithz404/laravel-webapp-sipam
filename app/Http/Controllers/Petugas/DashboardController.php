<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $rtQueryOrder = 'COALESCE(nomor_rt, CAST(SUBSTRING(kode_rt, 4) AS UNSIGNED)), id ASC';

        if ($user->isPetugas()) {
            $userRts = $user->rts()->orderByRaw($rtQueryOrder)->get();
            if ($userRts->isEmpty() && $user->rt_id) {
                $userRts = Rt::where('id', $user->rt_id)->get();
            }
        } else {
            $userRts = Rt::orderByRaw($rtQueryOrder)->get();
        }

        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $selectedRtId = $request->query('rt_id', $userRts->first()?->id);
        if ($user->isPetugas() && $selectedRtId && !$userRts->contains('id', $selectedRtId)) {
            $selectedRtId = $userRts->first()?->id;
        }
        $selectedRt = $userRts->firstWhere('id', $selectedRtId) ?? $userRts->first();

        // Group RTs by Dusun
        $rtsByDusun = $userRts->groupBy(function ($rt) {
            return $rt->dusun ?? (preg_match('/Pateguhan/i', $rt->wilayah) ? 'Pateguhan' : (preg_match('/Gentong/i', $rt->wilayah) ? 'Gentong' : 'Bendrong'));
        });

        // Compute quick progress map per RT for active period if multiple RTs
        $rtProgressMap = [];
        if ($userRts->count() > 1 && $activePeriode) {
            $rtStats = CatatanMeter::join('pelanggans', 'catatan_meters.pelanggan_id', '=', 'pelanggans.id')
                ->where('catatan_meters.periode_id', $activePeriode->id)
                ->whereIn('pelanggans.rt_id', $userRts->pluck('id'))
                ->groupBy('pelanggans.rt_id')
                ->selectRaw('
                    pelanggans.rt_id,
                    COUNT(pelanggans.id) as total_warga,
                    COUNT(CASE WHEN catatan_meters.angka_ini IS NOT NULL THEN 1 END) as tercatat
                ')
                ->get()
                ->keyBy('rt_id');

            $pelangganCounts = Pelanggan::whereIn('rt_id', $userRts->pluck('id'))
                ->where('status', 'aktif')
                ->groupBy('rt_id')
                ->selectRaw('rt_id, COUNT(*) as total')
                ->pluck('total', 'rt_id');

            foreach ($userRts as $rt) {
                $stat = $rtStats->get($rt->id);
                $totalW = $pelangganCounts->get($rt->id, 0);
                $tercatatW = $stat?->tercatat ?? 0;
                $rtProgressMap[$rt->id] = [
                    'total' => $totalW,
                    'tercatat' => $tercatatW,
                    'is_complete' => $totalW > 0 && $tercatatW >= $totalW,
                ];
            }
        }

        // Data for selected RT & active period
        $pelanggans = Pelanggan::where('rt_id', $selectedRt?->id)->where('status', 'aktif')->orderBy('urutan_rumah')->get();
        $totalPelanggan = $pelanggans->count();

        $catatans = CatatanMeter::whereIn('pelanggan_id', $pelanggans->pluck('id'))
            ->where('periode_id', $activePeriode?->id)
            ->get();

        $totalTercatat = $catatans->whereNotNull('angka_ini')->count();
        $progressPersen = $totalPelanggan > 0 ? round(($totalTercatat / $totalPelanggan) * 100) : 0;

        $totalTagihan = (float) $catatans->sum('total_tagihan');
        $totalTerbayar = (float) $catatans->sum('total_dibayar');
        $totalTunggakan = (float) $catatans->sum('sisa_tagihan');
        $totalPemakaianM3 = (int) $catatans->sum('pemakaian');

        // Recent payments recorded in this RT with eager loading
        $recentPayments = Pembayaran::whereHas('catatanMeter.pelanggan', function ($q) use ($selectedRt) {
            $q->where('rt_id', $selectedRt?->id);
        })->with(['catatanMeter.pelanggan.rt'])->latest('tanggal_bayar')->latest('id')->take(5)->get();

        // Pending input list (warga yang belum dicatat)
        $catatansByPelanggan = $catatans->keyBy('pelanggan_id');
        $unrecordedWargas = $pelanggans->filter(function ($warga) use ($catatansByPelanggan) {
            $c = $catatansByPelanggan->get($warga->id);
            return !$c || $c->angka_ini === null;
        })->take(5);

        return view('petugas.dashboard', compact(
            'userRts',
            'rtsByDusun',
            'rtProgressMap',
            'selectedRt',
            'activePeriode',
            'totalPelanggan',
            'totalTercatat',
            'progressPersen',
            'totalTagihan',
            'totalTerbayar',
            'totalTunggakan',
            'totalPemakaianM3',
            'recentPayments',
            'unrecordedWargas'
        ));
    }
}
