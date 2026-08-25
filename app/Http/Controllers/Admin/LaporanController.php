<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
use App\Models\Pembayaran;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $periodeId = $request->query('periode_id', $activePeriode?->id);
        $selectedPeriode = PeriodeTagihan::find($periodeId) ?? $activePeriode;
        $rtId = $request->query('rt_id');
        $tab = $request->query('tab', 'rekap-rt'); // 'rekap-rt', 'rekap-warga', 'keuangan'

        $allPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        $allRts = Rt::orderBy('kode_rt')->get();

        // 1. Data Rekap per RT (Hal 7)
        $rekapPerRt = $allRts->map(function ($rt) use ($selectedPeriode) {
            $catatan = CatatanMeter::whereHas('pelanggan', function ($q) use ($rt) {
                $q->where('rt_id', $rt->id);
            })->where('periode_id', $selectedPeriode?->id)->get();

            $totalWarga = $rt->pelanggans()->count();
            $tercatat = $catatan->whereNotNull('angka_ini')->count();
            $m3 = $catatan->sum('pemakaian');
            $biayaPemakaian = $catatan->sum('biaya_pemakaian');
            $biayaAdmin = $catatan->sum('biaya_admin');
            $tunggakanLalu = $catatan->sum('tunggakan_lalu');
            $totalTagihan = $catatan->sum('total_tagihan');
            $totalDibayar = $catatan->sum('total_dibayar');
            $sisaTagihan = $catatan->sum('sisa_tagihan');

            return (object) [
                'rt' => $rt,
                'total_warga' => $totalWarga,
                'tercatat' => $tercatat,
                'total_m3' => $m3,
                'biaya_pemakaian' => $biayaPemakaian,
                'biaya_admin' => $biayaAdmin,
                'tunggakan_lalu' => $tunggakanLalu,
                'total_tagihan' => $totalTagihan,
                'total_dibayar' => $totalDibayar,
                'sisa_tagihan' => $sisaTagihan,
                'persen_lunas' => $totalTagihan > 0 ? round(($totalDibayar / $totalTagihan) * 100, 1) : 0,
            ];
        });

        // 2. Data Rekap Multi-Bulan per Pelanggan (Hal 8-11)
        $recentPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->take(4)->get()->reverse();

        $queryWarga = Pelanggan::with(['rt'])->orderBy('rt_id')->orderBy('urutan_rumah');
        if ($rtId) {
            $queryWarga->where('rt_id', $rtId);
        }
        $wargas = $queryWarga->get();

        $wargaMatrix = $wargas->map(function ($warga) use ($recentPeriodes) {
            $matrixPerBulan = [];
            foreach ($recentPeriodes as $p) {
                $catatan = CatatanMeter::where('pelanggan_id', $warga->id)
                    ->where('periode_id', $p->id)
                    ->first();
                $matrixPerBulan[$p->id] = $catatan;
            }

            return (object) [
                'warga' => $warga,
                'records' => $matrixPerBulan,
            ];
        });

        // 3. Data Laporan Keuangan HIPPAM
        $allCatatanSelected = CatatanMeter::where('periode_id', $selectedPeriode?->id)->get();
        $keuangan = (object) [
            'total_potensi' => $allCatatanSelected->sum('total_tagihan'),
            'total_realisasi' => $allCatatanSelected->sum('total_dibayar'),
            'total_piutang' => $allCatatanSelected->sum('sisa_tagihan'),
            'total_admin' => $allCatatanSelected->sum('biaya_admin'),
            'total_air' => $allCatatanSelected->sum('biaya_pemakaian'),
            'efisiensi' => $allCatatanSelected->sum('total_tagihan') > 0 
                ? round(($allCatatanSelected->sum('total_dibayar') / $allCatatanSelected->sum('total_tagihan')) * 100, 1) 
                : 0,
        ];

        return view('admin.laporan.index', compact(
            'selectedPeriode',
            'allPeriodes',
            'allRts',
            'rtId',
            'tab',
            'rekapPerRt',
            'recentPeriodes',
            'wargaMatrix',
            'keuangan'
        ));
    }

    public function exportCsv(Request $request)
    {
        $periodeId = $request->query('periode_id');
        $periode = PeriodeTagihan::find($periodeId) ?? PeriodeTagihan::getActivePeriode();

        $records = CatatanMeter::with(['pelanggan.rt'])
            ->where('periode_id', $periode?->id)
            ->get();

        $filename = "Rekap_SIPAM_Tirto_Makmur_" . str_replace(' ', '_', $periode->nama_periode) . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($records, $periode) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No. Rekening', 'Nama Warga', 'RT / Wilayah', 'Stand Lalu', 'Stand Ini', 'Pemakaian (m3)', 'Biaya Pemakaian (Rp)', 'Biaya Admin (Rp)', 'Tunggakan Lalu (Rp)', 'Total Tagihan (Rp)', 'Dibayar (Rp)', 'Sisa (Rp)', 'Status']);

            foreach ($records as $row) {
                fputcsv($file, [
                    $row->pelanggan->no_rekening,
                    $row->pelanggan->nama,
                    $row->pelanggan->rt->nama_rt,
                    $row->angka_lalu,
                    $row->angka_ini ?? 0,
                    $row->pemakaian,
                    $row->biaya_pemakaian,
                    $row->biaya_admin,
                    $row->tunggakan_lalu,
                    $row->total_tagihan,
                    $row->total_dibayar,
                    $row->sisa_tagihan,
                    ucfirst($row->status_bayar),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
