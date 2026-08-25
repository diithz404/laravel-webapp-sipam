<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRts = $user->rts()->orderBy('kode_rt')->get();
        if ($userRts->isEmpty() || $user->isAdmin()) {
            $userRts = Rt::orderBy('kode_rt')->get();
        }

        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $rtId = $request->query('rt_id', $userRts->first()?->id);
        $search = $request->query('search');
        $statusMeter = $request->query('status_meter'); // 'tercatat', 'draft'
        $statusBayar = $request->query('status_bayar'); // 'lunas', 'belum_bayar', 'sebagian'

        $query = Pelanggan::with(['rt', 'catatanMeters' => function ($q) use ($activePeriode) {
            $q->where('periode_id', $activePeriode?->id);
        }]);

        if ($rtId) {
            $query->where('rt_id', $rtId);
        } else {
            $query->whereIn('rt_id', $userRts->pluck('id'));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $pelanggans = $query->orderBy('urutan_rumah')->orderBy('nama')->get();

        // Apply filters on active period's catatan meter
        if ($statusMeter) {
            $pelanggans = $pelanggans->filter(function ($p) use ($statusMeter) {
                $catatan = $p->catatanMeters->first();
                if ($statusMeter === 'tercatat') {
                    return $catatan && $catatan->angka_ini !== null;
                }
                return !$catatan || $catatan->angka_ini === null;
            });
        }

        if ($statusBayar) {
            $pelanggans = $pelanggans->filter(function ($p) use ($statusBayar) {
                $catatan = $p->catatanMeters->first();
                return $catatan && $catatan->status_bayar === $statusBayar;
            });
        }

        // Summary stats
        $totalWarga = $pelanggans->count();
        $totalSudahDicatat = $pelanggans->filter(fn($p) => ($p->catatanMeters->first()?->angka_ini !== null))->count();
        $totalBelumDicatat = $totalWarga - $totalSudahDicatat;
        $totalLunas = $pelanggans->filter(fn($p) => ($p->catatanMeters->first()?->status_bayar === 'lunas'))->count();

        return view('petugas.warga.index', compact(
            'userRts',
            'rtId',
            'search',
            'statusMeter',
            'statusBayar',
            'activePeriode',
            'pelanggans',
            'totalWarga',
            'totalSudahDicatat',
            'totalBelumDicatat',
            'totalLunas'
        ));
    }
}
