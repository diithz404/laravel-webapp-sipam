<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
use App\Models\Tarif;
use App\Models\ActivityLog;

class InputMeterController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->isPetugas()) {
            $userRts = $user->rts()->orderBy('kode_rt')->get();
            if ($userRts->isEmpty() && $user->rt_id) {
                $userRts = Rt::where('id', $user->rt_id)->get();
            }
        } else {
            $userRts = Rt::orderBy('kode_rt')->get();
        }

        $activePeriode = PeriodeTagihan::getActivePeriode() ?? PeriodeTagihan::latest('id')->first();
        $selectedRtId = $request->query('rt_id', $userRts->first()?->id);

        if ($user->isPetugas() && $selectedRtId && !$userRts->contains('id', $selectedRtId)) {
            abort(403, 'Anda tidak memiliki hak akses ke data RT ini.');
        }

        $selectedRt = $userRts->firstWhere('id', $selectedRtId) ?? $userRts->first();

        $activeTarif = Tarif::getActiveTarif() ?? new Tarif([
            'tarif_standar' => 350,
            'batas_kuota_standar' => 20,
            'tarif_progresif' => 400,
            'biaya_admin' => 2000,
        ]);

        // Get customers for selected RT
        $pelanggans = Pelanggan::where('rt_id', $selectedRt?->id)
            ->where('status', 'aktif')
            ->orderBy('urutan_rumah')
            ->get();

        $pelangganIds = $pelanggans->pluck('id');

        // Batch load existing CatatanMeter records (Eliminating N+1 queries)
        $existingRecords = CatatanMeter::where('periode_id', $activePeriode?->id)
            ->whereIn('pelanggan_id', $pelangganIds)
            ->get()
            ->keyBy('pelanggan_id');

        $catatanRecords = [];
        $missingPelanggans = [];

        foreach ($pelanggans as $pelanggan) {
            if ($existingRecords->has($pelanggan->id)) {
                $catatanRecords[$pelanggan->id] = $existingRecords->get($pelanggan->id);
            } else {
                $missingPelanggans[] = $pelanggan;
            }
        }

        // Initialize missing records if any
        if (!empty($missingPelanggans) && $activePeriode) {
            foreach ($missingPelanggans as $pelanggan) {
                $catatanLalu = CatatanMeter::where('pelanggan_id', $pelanggan->id)
                    ->where('periode_id', '!=', $activePeriode->id)
                    ->latest('id')
                    ->first();

                $angkaLalu = $catatanLalu ? ($catatanLalu->angka_ini ?? $catatanLalu->angka_lalu) : (int) ($pelanggan->angka_meter_awal ?? 0);
                $tunggakanLalu = $catatanLalu ? (float) $catatanLalu->sisa_tagihan : 0;
                $biayaAdmin = (float) $activeTarif->biaya_admin;

                $record = CatatanMeter::create([
                    'pelanggan_id' => $pelanggan->id,
                    'periode_id' => $activePeriode->id,
                    'angka_lalu' => (int) ($angkaLalu ?? 0),
                    'angka_ini' => null,
                    'pemakaian' => 0,
                    'biaya_admin' => $biayaAdmin,
                    'tunggakan_lalu' => $tunggakanLalu,
                    'total_tagihan' => $tunggakanLalu + $biayaAdmin,
                    'sisa_tagihan' => $tunggakanLalu + $biayaAdmin,
                    'status_meter' => 'draft',
                    'status_bayar' => 'belum_bayar',
                ]);

                $catatanRecords[$pelanggan->id] = $record;
            }
        }

        $allFilled = collect($catatanRecords)->every(fn($c) => $c->angka_ini !== null);
        $totalTercatat = collect($catatanRecords)->filter(fn($c) => $c->angka_ini !== null)->count();

        return view('petugas.input-meter.index', compact(
            'userRts',
            'selectedRt',
            'activePeriode',
            'pelanggans',
            'catatanRecords',
            'activeTarif',
            'allFilled',
            'totalTercatat'
        ));
    }

    public function storeSingle(Request $request)
    {
        $validated = $request->validate([
            'catatan_id' => 'required|exists:catatan_meters,id',
            'angka_ini' => 'required|integer|min:0',
        ]);

        $catatan = CatatanMeter::with('pelanggan')->findOrFail($validated['catatan_id']);
        $user = auth()->user();
        if ($user->isPetugas()) {
            $allowedRtIds = $user->rts->pluck('id')->all();
            if ($user->rt_id && !in_array($user->rt_id, $allowedRtIds)) {
                $allowedRtIds[] = $user->rt_id;
            }
            if (!in_array($catatan->pelanggan->rt_id, $allowedRtIds)) {
                abort(403, 'Anda tidak memiliki hak akses ke data warga RT ini.');
            }
        }
        
        if ($validated['angka_ini'] < $catatan->angka_lalu) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Angka meter baru ({$validated['angka_ini']}) tidak boleh lebih kecil dari meter bulan lalu ({$catatan->angka_lalu})."
                ], 422);
            }
            return back()->with('error', "Angka meter baru ({$validated['angka_ini']}) tidak boleh lebih kecil dari meter bulan lalu ({$catatan->angka_lalu}).");
        }

        $catatan->angka_ini = $validated['angka_ini'];
        $catatan->status_meter = 'tercatat';
        $catatan->input_by = auth()->id();
        $catatan->input_at = now();
        $catatan->recalculateBilling();
        $catatan->save();

        ActivityLog::log('INPUT_METER', "Pencatatan meter {$catatan->pelanggan->nama}: Lalu {$catatan->angka_lalu} -> Kini {$catatan->angka_ini} (Pemakaian: {$catatan->pemakaian} m3, Tagihan: Rp" . number_format($catatan->total_tagihan, 0, ',', '.') . ")");

        if ($request->expectsJson() || $request->ajax()) {
            $rtId = $catatan->pelanggan->rt_id;
            $rtCatatans = CatatanMeter::where('periode_id', $catatan->periode_id)
                ->whereHas('pelanggan', fn($q) => $q->where('rt_id', $rtId))
                ->get();
            $totalCount = $rtCatatans->count();
            $tercatatCount = $rtCatatans->whereNotNull('angka_ini')->count();

            return response()->json([
                'success' => true,
                'message' => "Meter warga {$catatan->pelanggan->nama} berhasil disimpan!",
                'catatan' => [
                    'id' => $catatan->id,
                    'angka_ini' => $catatan->angka_ini,
                    'pemakaian' => $catatan->pemakaian,
                    'biaya_pemakaian' => $catatan->biaya_pemakaian,
                    'biaya_admin' => $catatan->biaya_admin,
                    'tunggakan_lalu' => $catatan->tunggakan_lalu,
                    'total_tagihan' => $catatan->total_tagihan,
                    'sisa_tagihan' => $catatan->sisa_tagihan,
                    'status_meter' => $catatan->status_meter,
                    'status_bayar' => $catatan->status_bayar,
                ],
                'progress' => [
                    'total' => $totalCount,
                    'tercatat' => $tercatatCount,
                    'pct' => $totalCount > 0 ? round(($tercatatCount / $totalCount) * 100) : 0,
                    'all_filled' => $tercatatCount >= $totalCount && $totalCount > 0,
                ],
            ]);
        }

        return back()->with('success', "Meter warga {$catatan->pelanggan->nama} berhasil disimpan (Tagihan: Rp" . number_format($catatan->total_tagihan, 0, ',', '.') . ").");
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'meters' => 'required|array',
            'meters.*.id' => 'required|exists:catatan_meters,id',
            'meters.*.angka_ini' => 'nullable|integer|min:0',
        ]);

        $savedCount = 0;
        $tarif = Tarif::getActiveTarif();
        $user = auth()->user();
        $allowedRtIds = $user->isAdmin() ? null : ($user->rts->pluck('id')->all() ?: ($user->rt_id ? [$user->rt_id] : []));

        foreach ($validated['meters'] as $item) {
            if ($item['angka_ini'] !== null && $item['angka_ini'] !== '') {
                $catatan = CatatanMeter::with('pelanggan')->find($item['id']);
                if ($catatan && ($allowedRtIds === null || in_array($catatan->pelanggan->rt_id, $allowedRtIds))) {
                    if ($item['angka_ini'] >= $catatan->angka_lalu) {
                        $catatan->angka_ini = (int) $item['angka_ini'];
                        $catatan->status_meter = 'tercatat';
                        $catatan->input_by = auth()->id();
                        $catatan->input_at = now();
                        $catatan->recalculateBilling($tarif);
                        $catatan->save();
                        $savedCount++;
                    }
                }
            }
        }

        ActivityLog::log('BATCH_INPUT_METER', "Menyimpan serentak {$savedCount} catatan meter");

        return back()->with('success', "Berhasil menyimpan {$savedCount} data angka meter.");
    }

    public function tutupPeriodeRt(Request $request)
    {
        $validated = $request->validate([
            'rt_id' => 'required|exists:rts,id',
            'periode_id' => 'required|exists:periode_tagihans,id',
        ]);

        $user = auth()->user();
        if ($user->isPetugas()) {
            $allowedRtIds = $user->rts->pluck('id')->all();
            if ($user->rt_id && !in_array($user->rt_id, $allowedRtIds)) {
                $allowedRtIds[] = $user->rt_id;
            }
            if (!in_array($validated['rt_id'], $allowedRtIds)) {
                abort(403, 'Anda tidak memiliki hak akses ke RT ini.');
            }
        }

        $rt = Rt::findOrFail($validated['rt_id']);
        $catatans = CatatanMeter::whereHas('pelanggan', function ($q) use ($rt) {
            $q->where('rt_id', $rt->id);
        })->where('periode_id', $validated['periode_id'])->get();

        $unfilled = $catatans->whereNull('angka_ini')->count();
        if ($unfilled > 0) {
            return back()->with('error', "Masih ada {$unfilled} rumah yang belum diinput angka meternya. Semua rumah wajib diinput sebelum menutup periode RT.");
        }

        foreach ($catatans as $catatan) {
            $catatan->status_meter = 'terkunci';
            $catatan->save();
        }

        ActivityLog::log('TUTUP_PERIODE_RT', "Menutup & mengunci periode input untuk {$rt->nama_rt}");

        return back()->with('success', "Periode input untuk {$rt->nama_rt} berhasil ditutup dan dikunci.");
    }
}
