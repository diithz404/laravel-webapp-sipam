<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Rt;
use App\Models\CatatanMeter;
use App\Models\PeriodeTagihan;
use App\Models\ActivityLog;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $rtId = $request->query('rt_id');
        $status = $request->query('status');

        $query = Pelanggan::with(['rt', 'catatanMeterTerbaru']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($rtId) {
            $query->where('rt_id', $rtId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $pelanggans = $query->orderBy('rt_id')->orderBy('urutan_rumah')->paginate(15)->withQueryString();
        $rts = Rt::orderBy('kode_rt')->get();

        return view('admin.pelanggan.index', compact('pelanggans', 'rts', 'search', 'rtId', 'status'));
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load(['rt']);

        $riwayatMeter = CatatanMeter::with(['periode', 'pembayarans', 'pencatat'])
            ->where('pelanggan_id', $pelanggan->id)
            ->orderBy('id', 'desc')
            ->get();

        $totalPemakaian = $riwayatMeter->sum('pemakaian');
        $totalTagihan = $riwayatMeter->sum('total_tagihan');
        $totalDibayar = $riwayatMeter->sum('total_dibayar');
        $totalSisa = $riwayatMeter->where('periode.status', 'aktif')->sum('sisa_tagihan');

        return view('admin.pelanggan.show', compact('pelanggan', 'riwayatMeter', 'totalPemakaian', 'totalTagihan', 'totalDibayar', 'totalSisa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_rekening' => 'required|string|max:30|unique:pelanggans,no_rekening',
            'nama' => 'required|string|max:150',
            'alamat' => 'required|string|max:255',
            'rt_id' => 'required|exists:rts,id',
            'no_hp' => 'nullable|string|max:20',
            'angka_meter_awal' => 'required|integer|min:0',
            'urutan_rumah' => 'nullable|integer',
        ]);

        $pelanggan = Pelanggan::create([
            'no_rekening' => $validated['no_rekening'],
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'rt_id' => $validated['rt_id'],
            'no_hp' => $validated['no_hp'] ?? null,
            'angka_meter_awal' => $validated['angka_meter_awal'],
            'status' => 'aktif',
            'urutan_rumah' => $validated['urutan_rumah'] ?? (Pelanggan::where('rt_id', $validated['rt_id'])->count() + 1),
        ]);

        // Inisialisasi catatan meter untuk periode aktif jika ada
        $activePeriode = PeriodeTagihan::getActivePeriode();
        if ($activePeriode) {
            CatatanMeter::firstOrCreate([
                'pelanggan_id' => $pelanggan->id,
                'periode_id' => $activePeriode->id,
            ], [
                'angka_lalu' => $pelanggan->angka_meter_awal,
                'angka_ini' => null,
                'pemakaian' => 0,
                'biaya_admin' => 2000,
                'total_tagihan' => 2000,
                'sisa_tagihan' => 2000,
                'status_meter' => 'draft',
                'status_bayar' => 'belum_bayar',
            ]);
        }

        ActivityLog::log('TAMBAH_PELANGGAN', "Menambahkan pelanggan baru: {$pelanggan->nama} ({$pelanggan->no_rekening})");

        return redirect()->route('admin.pelanggan.index')->with('success', "Pelanggan {$pelanggan->nama} berhasil didaftarkan.");
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validated = $request->validate([
            'no_rekening' => 'required|string|max:30|unique:pelanggans,no_rekening,' . $pelanggan->id,
            'nama' => 'required|string|max:150',
            'alamat' => 'required|string|max:255',
            'rt_id' => 'required|exists:rts,id',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
            'urutan_rumah' => 'nullable|integer',
        ]);

        $pelanggan->update($validated);

        ActivityLog::log('UPDATE_PELANGGAN', "Memperbarui data pelanggan {$pelanggan->nama} ({$pelanggan->no_rekening})");

        return redirect()->route('admin.pelanggan.index')->with('success', "Data pelanggan {$pelanggan->nama} berhasil diperbarui.");
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $nama = $pelanggan->nama;
        $pelanggan->delete();

        ActivityLog::log('HAPUS_PELANGGAN', "Menghapus data pelanggan: {$nama}");

        return redirect()->route('admin.pelanggan.index')->with('success', "Data pelanggan {$nama} berhasil dihapus.");
    }
}
