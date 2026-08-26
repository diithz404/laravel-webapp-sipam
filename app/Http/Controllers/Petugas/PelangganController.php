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
        $allPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        
        $periodeId = $request->query('periode_id', $activePeriode?->id);
        $selectedPeriode = PeriodeTagihan::find($periodeId) ?? $activePeriode;

        $rtId = $request->query('rt_id', $userRts->first()?->id);
        $selectedRt = $userRts->firstWhere('id', $rtId) ?? $userRts->first();

        $search = $request->query('search');
        $statusMeter = $request->query('status_meter'); // 'tercatat', 'draft'
        $statusBayar = $request->query('status_bayar'); // 'lunas', 'belum_bayar', 'sebagian'

        $query = Pelanggan::with(['rt', 'catatanMeters' => function ($q) use ($selectedPeriode) {
            $q->where('periode_id', $selectedPeriode?->id);
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

        // Apply filters on selected period's catatan meter
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

        // Summary & Rekap Stats for this view
        $totalWarga = $pelanggans->count();
        $totalSudahDicatat = $pelanggans->filter(fn($p) => ($p->catatanMeters->first()?->angka_ini !== null))->count();
        $totalBelumDicatat = $totalWarga - $totalSudahDicatat;
        
        $totalLunas = $pelanggans->filter(fn($p) => ($p->catatanMeters->first()?->status_bayar === 'lunas'))->count();
        $totalSebagian = $pelanggans->filter(fn($p) => ($p->catatanMeters->first()?->status_bayar === 'sebagian'))->count();
        $totalBelumBayar = $totalWarga - $totalLunas - $totalSebagian;

        // Financial & Usage Aggregations
        $totalPemakaianM3 = (int) $pelanggans->sum(fn($p) => ($p->catatanMeters->first()?->pemakaian ?? 0));
        $totalTagihanRp = (float) $pelanggans->sum(fn($p) => ($p->catatanMeters->first()?->total_tagihan ?? 0));
        $totalTerbayarRp = (float) $pelanggans->sum(fn($p) => ($p->catatanMeters->first()?->total_dibayar ?? 0));
        $totalTunggakanRp = (float) $pelanggans->sum(fn($p) => ($p->catatanMeters->first()?->sisa_tagihan ?? 0));

        $persenCatat = $totalWarga > 0 ? round(($totalSudahDicatat / $totalWarga) * 100) : 0;
        $persenBayar = $totalTagihanRp > 0 ? round(($totalTerbayarRp / $totalTagihanRp) * 100) : 0;

        return view('petugas.warga.index', compact(
            'userRts',
            'rtId',
            'selectedRt',
            'search',
            'statusMeter',
            'statusBayar',
            'activePeriode',
            'selectedPeriode',
            'allPeriodes',
            'pelanggans',
            'totalWarga',
            'totalSudahDicatat',
            'totalBelumDicatat',
            'totalLunas',
            'totalSebagian',
            'totalBelumBayar',
            'totalPemakaianM3',
            'totalTagihanRp',
            'totalTerbayarRp',
            'totalTunggakanRp',
            'persenCatat',
            'persenBayar'
        ));
    }

    public function exportCsv(Request $request)
    {
        $user = auth()->user();
        $userRts = $user->rts()->orderBy('kode_rt')->get();
        if ($userRts->isEmpty() || $user->isAdmin()) {
            $userRts = Rt::orderBy('kode_rt')->get();
        }

        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $periodeId = $request->query('periode_id', $activePeriode?->id);
        $selectedPeriode = PeriodeTagihan::find($periodeId) ?? $activePeriode;

        $rtId = $request->query('rt_id', $userRts->first()?->id);
        $selectedRt = $userRts->firstWhere('id', $rtId) ?? $userRts->first();

        $query = Pelanggan::with(['rt', 'catatanMeters' => function ($q) use ($selectedPeriode) {
            $q->where('periode_id', $selectedPeriode?->id);
        }]);

        if ($rtId) {
            $query->where('rt_id', $rtId);
        } else {
            $query->whereIn('rt_id', $userRts->pluck('id'));
        }

        $pelanggans = $query->orderBy('urutan_rumah')->orderBy('nama')->get();

        $rtSlug = $selectedRt ? str_replace(' ', '_', $selectedRt->nama_rt) : 'Semua_RT';
        $periodeSlug = $selectedPeriode ? str_replace(' ', '_', $selectedPeriode->nama_periode) : 'Periode';
        $filename = "Rekap_Warga_HIPPAM_{$rtSlug}_{$periodeSlug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($pelanggans, $selectedPeriode, $selectedRt) {
            $file = fopen('php://output', 'w');
            
            // Header information
            fputcsv($file, ['REKAP DATA WARGA & TAGIHAN AIR - HIPPAM TIRTO MAKMUR']);
            fputcsv($file, ['Wilayah RT:', $selectedRt?->nama_rt ?? 'Semua RT', 'Periode:', $selectedPeriode?->nama_periode ?? '-']);
            fputcsv($file, ['Tanggal Ekspor:', date('d/m/Y H:i')]);
            fputcsv($file, []); // Empty row

            // Table columns
            fputcsv($file, [
                'No',
                'No. Rekening',
                'Nama Warga',
                'No. HP',
                'Alamat',
                'Stand Lalu',
                'Stand Kini',
                'Pemakaian (m3)',
                'Total Tagihan (Rp)',
                'Sudah Dibayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Status Bayar'
            ]);

            $totalM3 = 0;
            $totalTagihan = 0;
            $totalBayar = 0;
            $totalSisa = 0;

            foreach ($pelanggans as $index => $warga) {
                $c = $warga->catatanMeters->first();
                $standLalu = $c ? $c->angka_lalu : $warga->angka_meter_awal;
                $standKini = $c && $c->angka_ini !== null ? $c->angka_ini : '-';
                $m3 = $c ? $c->pemakaian : 0;
                $tagihan = $c ? $c->total_tagihan : 0;
                $bayar = $c ? $c->total_dibayar : 0;
                $sisa = $c ? $c->sisa_tagihan : 0;
                $status = $c ? ucfirst(str_replace('_', ' ', $c->status_bayar)) : 'Belum Bayar';

                $totalM3 += $m3;
                $totalTagihan += $tagihan;
                $totalBayar += $bayar;
                $totalSisa += $sisa;

                fputcsv($file, [
                    $index + 1,
                    $warga->no_rekening,
                    $warga->nama,
                    $warga->no_hp ?? '-',
                    $warga->alamat,
                    $standLalu,
                    $standKini,
                    $m3,
                    $tagihan,
                    $bayar,
                    $sisa,
                    $status
                ]);
            }

            // Total row
            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                '',
                $totalM3,
                $totalTagihan,
                $totalBayar,
                $totalSisa,
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
