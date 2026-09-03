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
        $dusun = $request->query('dusun');
        $status = $request->query('status');
        $statusSetup = $request->query('status_setup');
        $jenisPelanggan = $request->query('jenis_pelanggan');
        $namaGanda = $request->query('nama_ganda');

        $query = Pelanggan::with(['rt', 'tarif', 'catatanMeterTerbaru']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('dusun', 'like', "%{$search}%")
                  ->orWhere('no_rt', 'like', "%{$search}%")
                  ->orWhere('no_rw', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($dusun) {
            $query->where(function ($q) use ($dusun) {
                $q->where('dusun', $dusun)
                  ->orWhereHas('rt', fn($rq) => $rq->where('dusun', $dusun)->orWhere('wilayah', 'like', "%{$dusun}%"));
            });
        }

        if ($rtId) {
            $query->where('rt_id', $rtId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($statusSetup) {
            $query->where('status_setup', $statusSetup);
        }

        if ($jenisPelanggan) {
            $query->where('jenis_pelanggan', $jenisPelanggan);
        }

        if ($namaGanda) {
            $query->whereNotNull('catatan_nama');
        }

        $pelanggans = $query->orderBy('rt_id')->orderBy('urutan_rumah')->paginate(25)->withQueryString();
        $rts = Rt::orderBy('kode_rt')->get();

        $dusunList = ['Pateguhan', 'Gentong', 'Bendrong'];

        // Extra stats for quick navigation
        $totalBelumSetup = Pelanggan::where('status_setup', 'belum_lengkap')->count();
        $totalNonRt = Pelanggan::where('jenis_pelanggan', 'non_rumah_tangga')->count();
        $totalNamaGanda = Pelanggan::whereNotNull('catatan_nama')->count();

        return view('admin.pelanggan.index', compact(
            'pelanggans', 
            'rts', 
            'search', 
            'rtId', 
            'dusun',
            'status', 
            'statusSetup',
            'jenisPelanggan',
            'namaGanda',
            'dusunList',
            'totalBelumSetup',
            'totalNonRt',
            'totalNamaGanda'
        ));
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
            'catatan_nama' => 'nullable|string|max:255',
            'jenis_pelanggan' => 'nullable|string|in:rumah_tangga,non_rumah_tangga',
            'sub_kategori' => 'nullable|string|max:100',
            'dusun' => 'required|string|max:100',
            'no_rt' => 'nullable|string|max:10',
            'rt' => 'nullable|string|max:10',
            'no_rw' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'rt_id' => 'required|exists:rts,id',
            'no_hp' => 'nullable|string|max:20',
            'angka_meter_awal' => 'nullable|integer|min:0',
            'status_setup' => 'nullable|string|in:belum_lengkap,lengkap',
            'urutan_rumah' => 'nullable|integer',
        ]);

        $dusun = trim($validated['dusun']);
        $no_rt = trim($validated['no_rt'] ?? $validated['rt'] ?? '');
        $no_rw = trim($validated['no_rw'] ?? $validated['rw'] ?? '');
        $alamat = Pelanggan::formatAlamat($dusun, $no_rt, $no_rw);

        $pelanggan = Pelanggan::create([
            'no_rekening' => $validated['no_rekening'],
            'nama' => $validated['nama'],
            'catatan_nama' => $validated['catatan_nama'] ?? null,
            'jenis_pelanggan' => $validated['jenis_pelanggan'] ?? 'rumah_tangga',
            'sub_kategori' => $validated['sub_kategori'] ?? null,
            'dusun' => $dusun,
            'no_rt' => $no_rt,
            'no_rw' => $no_rw,
            'alamat' => $alamat,
            'rt_id' => $validated['rt_id'],
            'no_hp' => $validated['no_hp'] ?? null,
            'angka_meter_awal' => $validated['angka_meter_awal'] ?? null,
            'status_setup' => $validated['status_setup'] ?? 'belum_lengkap',
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
                'angka_lalu' => (int) ($pelanggan->angka_meter_awal ?? 0),
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
            'catatan_nama' => 'nullable|string|max:255',
            'jenis_pelanggan' => 'nullable|string|in:rumah_tangga,non_rumah_tangga',
            'sub_kategori' => 'nullable|string|max:100',
            'dusun' => 'required|string|max:100',
            'no_rt' => 'nullable|string|max:10',
            'rt' => 'nullable|string|max:10',
            'no_rw' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'rt_id' => 'required|exists:rts,id',
            'no_hp' => 'nullable|string|max:20',
            'status_setup' => 'nullable|string|in:belum_lengkap,lengkap',
            'status' => 'required|in:aktif,nonaktif',
            'urutan_rumah' => 'nullable|integer',
        ]);

        $dusun = trim($validated['dusun']);
        $no_rt = trim($validated['no_rt'] ?? $validated['rt'] ?? '');
        $no_rw = trim($validated['no_rw'] ?? $validated['rw'] ?? '');
        $alamat = Pelanggan::formatAlamat($dusun, $no_rt, $no_rw);

        $pelanggan->update([
            'no_rekening' => $validated['no_rekening'],
            'nama' => $validated['nama'],
            'catatan_nama' => $validated['catatan_nama'] ?? null,
            'jenis_pelanggan' => $validated['jenis_pelanggan'] ?? 'rumah_tangga',
            'sub_kategori' => $validated['sub_kategori'] ?? null,
            'dusun' => $dusun,
            'no_rt' => $no_rt,
            'no_rw' => $no_rw,
            'alamat' => $alamat,
            'rt_id' => $validated['rt_id'],
            'no_hp' => $validated['no_hp'] ?? null,
            'status_setup' => $validated['status_setup'] ?? $pelanggan->status_setup,
            'status' => $validated['status'],
            'urutan_rumah' => $validated['urutan_rumah'] ?? $pelanggan->urutan_rumah,
        ]);

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
