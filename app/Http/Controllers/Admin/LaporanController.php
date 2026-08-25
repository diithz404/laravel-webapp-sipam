<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;

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

        // 1. Data Rekap per RT (Hal 7) - Single grouped query eliminating N+1
        $rtAggregates = CatatanMeter::join('pelanggans', 'catatan_meters.pelanggan_id', '=', 'pelanggans.id')
            ->where('catatan_meters.periode_id', $selectedPeriode?->id)
            ->groupBy('pelanggans.rt_id')
            ->selectRaw('
                pelanggans.rt_id,
                COUNT(CASE WHEN catatan_meters.angka_ini IS NOT NULL THEN 1 END) as tercatat,
                COALESCE(SUM(catatan_meters.pemakaian), 0) as total_m3,
                COALESCE(SUM(catatan_meters.biaya_pemakaian), 0) as biaya_pemakaian,
                COALESCE(SUM(catatan_meters.biaya_admin), 0) as biaya_admin,
                COALESCE(SUM(catatan_meters.tunggakan_lalu), 0) as tunggakan_lalu,
                COALESCE(SUM(catatan_meters.total_tagihan), 0) as total_tagihan,
                COALESCE(SUM(catatan_meters.total_dibayar), 0) as total_dibayar,
                COALESCE(SUM(catatan_meters.sisa_tagihan), 0) as sisa_tagihan
            ')
            ->get()
            ->keyBy('rt_id');

        $rtsWithCounts = Rt::withCount(['pelanggans' => function ($q) {
                $q->where('status', 'aktif');
            }])
            ->orderBy('kode_rt')
            ->get();

        $rekapPerRt = $rtsWithCounts->map(function ($rt) use ($rtAggregates) {
            $agg = $rtAggregates->get($rt->id);

            $totalWarga = $rt->pelanggans_count;
            $tercatat = (int) ($agg?->tercatat ?? 0);
            $totalM3 = (int) ($agg?->total_m3 ?? 0);
            $biayaPemakaian = (float) ($agg?->biaya_pemakaian ?? 0);
            $biayaAdmin = (float) ($agg?->biaya_admin ?? 0);
            $tunggakanLalu = (float) ($agg?->tunggakan_lalu ?? 0);
            $totalTagihan = (float) ($agg?->total_tagihan ?? 0);
            $totalDibayar = (float) ($agg?->total_dibayar ?? 0);
            $sisaTagihan = (float) ($agg?->sisa_tagihan ?? 0);
            $persenLunas = $totalTagihan > 0 ? round(($totalDibayar / $totalTagihan) * 100, 1) : 0;

            return (object) [
                'rt' => $rt,
                'total_warga' => $totalWarga,
                'tercatat' => $tercatat,
                'total_m3' => $totalM3,
                'biaya_pemakaian' => $biayaPemakaian,
                'biaya_admin' => $biayaAdmin,
                'tunggakan_lalu' => $tunggakanLalu,
                'total_tagihan' => $totalTagihan,
                'total_dibayar' => $totalDibayar,
                'sisa_tagihan' => $sisaTagihan,
                'persen_lunas' => $persenLunas,
            ];
        });

        // 2. Data Rekap Multi-Bulan per Pelanggan (Hal 8-11)
        // Fetches last 4 periods and batch loads all records in 1 query (Eliminating 800+ queries)
        $recentPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->take(4)->get()->reverse()->values();

        $queryWarga = Pelanggan::with(['rt'])->where('status', 'aktif')->orderBy('rt_id')->orderBy('urutan_rumah');
        if ($rtId) {
            $queryWarga->where('rt_id', $rtId);
        }
        $wargas = $queryWarga->get();

        $wargaIds = $wargas->pluck('id');
        $periodeIds = $recentPeriodes->pluck('id');

        $allCatatanMatrix = CatatanMeter::whereIn('periode_id', $periodeIds)
            ->whereIn('pelanggan_id', $wargaIds)
            ->get()
            ->groupBy('pelanggan_id');

        $wargaMatrix = $wargas->map(function ($warga) use ($recentPeriodes, $allCatatanMatrix) {
            $wargaCatatans = $allCatatanMatrix->get($warga->id, collect())->keyBy('periode_id');
            $matrixPerBulan = [];
            foreach ($recentPeriodes as $p) {
                $matrixPerBulan[$p->id] = $wargaCatatans->get($p->id);
            }

            return (object) [
                'warga' => $warga,
                'records' => $matrixPerBulan,
            ];
        });

        // 3. Data Laporan Keuangan HIPPAM (Single aggregated query)
        $keuanganStats = CatatanMeter::where('periode_id', $selectedPeriode?->id)
            ->selectRaw('
                COALESCE(SUM(total_tagihan), 0) as total_potensi,
                COALESCE(SUM(total_dibayar), 0) as total_realisasi,
                COALESCE(SUM(sisa_tagihan), 0) as total_piutang,
                COALESCE(SUM(biaya_admin), 0) as total_admin,
                COALESCE(SUM(biaya_pemakaian), 0) as total_air
            ')
            ->first();

        $potensi = (float) ($keuanganStats?->total_potensi ?? 0);
        $realisasi = (float) ($keuanganStats?->total_realisasi ?? 0);

        $keuangan = (object) [
            'total_potensi' => $potensi,
            'total_realisasi' => $realisasi,
            'total_piutang' => (float) ($keuanganStats?->total_piutang ?? 0),
            'total_admin' => (float) ($keuanganStats?->total_admin ?? 0),
            'total_air' => (float) ($keuanganStats?->total_air ?? 0),
            'efisiensi' => $potensi > 0 ? round(($realisasi / $potensi) * 100, 1) : 0,
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
