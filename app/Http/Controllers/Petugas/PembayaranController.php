<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\PeriodeTagihan;
use App\Models\CatatanMeter;
use App\Models\Pembayaran;
use App\Models\ActivityLog;

class PembayaranController extends Controller
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
        $periodeId = $request->query('periode_id', $activePeriode?->id);
        $selectedPeriode = PeriodeTagihan::find($periodeId) ?? $activePeriode;

        $rtId = $request->query('rt_id', $userRts->first()?->id);
        if ($user->isPetugas() && $rtId && !$userRts->contains('id', $rtId)) {
            abort(403, 'Anda tidak memiliki hak akses ke data RT ini.');
        }

        $status = $request->query('status');
        $search = $request->query('search');

        $query = CatatanMeter::with(['pelanggan.rt', 'pembayarans.kasir'])
            ->where('periode_id', $selectedPeriode?->id);

        if ($rtId) {
            $query->whereHas('pelanggan', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            });
        }

        if ($status) {
            $query->where('status_bayar', $status);
        }

        if ($search) {
            $query->whereHas('pelanggan', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('dusun', 'like', "%{$search}%")
                  ->orWhere('no_rt', 'like', "%{$search}%")
                  ->orWhere('no_rw', 'like', "%{$search}%");
            });
        }

        $catatans = $query->orderBy('status_bayar')->get();
        $allPeriodes = PeriodeTagihan::orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();

        return view('petugas.pembayaran.index', compact(
            'userRts',
            'rtId',
            'status',
            'search',
            'selectedPeriode',
            'allPeriodes',
            'catatans'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'catatan_meter_id' => 'required|exists:catatan_meters,id',
            'jumlah_bayar' => 'required|numeric|min:100',
            'tanggal_bayar' => 'required|date',
            'metode' => 'required|in:tunai,transfer',
            'catatan' => 'nullable|string|max:255',
        ]);

        $catatan = CatatanMeter::with('pelanggan')->findOrFail($validated['catatan_meter_id']);
        $user = auth()->user();
        if ($user->isPetugas()) {
            $allowedRtIds = $user->rts->pluck('id')->all();
            if ($user->rt_id && !in_array($user->rt_id, $allowedRtIds)) {
                $allowedRtIds[] = $user->rt_id;
            }
            if (!in_array($catatan->pelanggan->rt_id, $allowedRtIds)) {
                abort(403, 'Anda tidak memiliki hak akses untuk mencatat pembayaran warga RT ini.');
            }
        }
        
        $pembayaran = Pembayaran::create([
            'no_transaksi' => 'TRX-' . date('Ym') . '-' . str_pad(Pembayaran::count() + 1, 4, '0', STR_PAD_LEFT),
            'catatan_meter_id' => $catatan->id,
            'jumlah_bayar' => $validated['jumlah_bayar'],
            'tanggal_bayar' => $validated['tanggal_bayar'],
            'metode' => $validated['metode'],
            'dicatat_oleh' => auth()->id(),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Update akumulasi bayar pada catatan meter
        $totalPaid = $catatan->pembayarans()->sum('jumlah_bayar');
        $catatan->total_dibayar = $totalPaid;
        $catatan->sisa_tagihan = max(0, $catatan->total_tagihan - $totalPaid);

        if ($catatan->sisa_tagihan == 0 && $catatan->total_tagihan > 0) {
            $catatan->status_bayar = 'lunas';
        } elseif ($catatan->total_dibayar > 0) {
            $catatan->status_bayar = 'sebagian';
        } else {
            $catatan->status_bayar = 'belum_bayar';
        }

        $catatan->save();

        ActivityLog::log('CATAT_BAYAR', "Mencatat pembayaran {$catatan->pelanggan->nama} sebesar Rp" . number_format($validated['jumlah_bayar'], 0, ',', '.') . " (Status: " . ucfirst($catatan->status_bayar) . ")");

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pembayaran {$catatan->pelanggan->nama} sebesar Rp" . number_format($validated['jumlah_bayar'], 0, ',', '.') . " berhasil dicatat.",
                'catatan' => [
                    'id' => $catatan->id,
                    'total_dibayar' => $catatan->total_dibayar,
                    'sisa_tagihan' => $catatan->sisa_tagihan,
                    'status_bayar' => $catatan->status_bayar,
                ],
                'pembayaran' => [
                    'id' => $pembayaran->id,
                    'no_transaksi' => $pembayaran->no_transaksi,
                    'jumlah_bayar' => $pembayaran->jumlah_bayar,
                    'metode' => $pembayaran->metode,
                    'tanggal_bayar' => $pembayaran->tanggal_bayar->format('d/m/Y'),
                ],
                'kwitansi_url' => route('kwitansi.show', $catatan->id),
            ]);
        }

        if ($request->has('cetak')) {
            return redirect()->route('kwitansi.show', $catatan->id)->with('success', 'Pembayaran berhasil disimpan!');
        }

        return back()->with('success', "Pembayaran untuk {$catatan->pelanggan->nama} berhasil dicatat.")
                     ->with('paid_catatan_id', $catatan->id);
    }
}
