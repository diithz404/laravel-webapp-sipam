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
        $userRts = $user->rts()->orderBy('kode_rt')->get();

        // If user has no assigned RTs, fallback to all RTs
        if ($userRts->isEmpty()) {
            $userRts = Rt::orderBy('kode_rt')->get();
        }

        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $selectedRtId = $request->query('rt_id', $userRts->first()?->id);
        $selectedRt = $userRts->firstWhere('id', $selectedRtId) ?? $userRts->first();

        // Data for selected RT & active period
        $pelanggans = Pelanggan::where('rt_id', $selectedRt?->id)->where('status', 'aktif')->get();
        $totalPelanggan = $pelanggans->count();

        $catatans = CatatanMeter::whereIn('pelanggan_id', $pelanggans->pluck('id'))
            ->where('periode_id', $activePeriode?->id)
            ->get();

        $totalTercatat = $catatans->whereNotNull('angka_ini')->count();
        $progressPersen = $totalPelanggan > 0 ? round(($totalTercatat / $totalPelanggan) * 100) : 0;

        $totalTagihan = $catatans->sum('total_tagihan');
        $totalTerbayar = $catatans->sum('total_dibayar');
        $totalTunggakan = $catatans->sum('sisa_tagihan');
        $totalPemakaianM3 = $catatans->sum('pemakaian');

        // Recent payments recorded in this RT
        $recentPayments = Pembayaran::whereHas('catatanMeter.pelanggan', function ($q) use ($selectedRt) {
            $q->where('rt_id', $selectedRt?->id);
        })->with(['catatanMeter.pelanggan'])->latest('tanggal_bayar')->take(5)->get();

        // Pending input list (warga yang belum dicatat)
        $unrecordedWargas = $pelanggans->filter(function ($warga) use ($catatans) {
            $c = $catatans->firstWhere('pelanggan_id', $warga->id);
            return !$c || $c->angka_ini === null;
        })->take(5);

        return view('petugas.dashboard', compact(
            'userRts',
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
