<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rt;
use App\Models\User;
use App\Models\ActivityLog;

class RtController extends Controller
{
    public function index()
    {
        $rts = Rt::withCount('pelanggans')
            ->with('petugas')
            ->orderByRaw('COALESCE(nomor_rt, CAST(SUBSTRING(kode_rt, 4) AS UNSIGNED)), id ASC')
            ->get();

        $allPetugas = User::where('role', 'petugas')->where('status', 'active')->get();

        $rtsByDusun = $rts->groupBy(function ($rt) {
            return $rt->dusun ?? (preg_match('/Pateguhan/i', $rt->wilayah) ? 'Pateguhan' : (preg_match('/Gentong/i', $rt->wilayah) ? 'Gentong' : 'Bendrong'));
        });

        return view('admin.rt.index', compact('rts', 'allPetugas', 'rtsByDusun'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_rt' => 'required|string|max:20|unique:rts,kode_rt',
            'nama_rt' => 'required|string|max:100',
            'wilayah' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'petugas_ids' => 'nullable|array',
            'petugas_ids.*' => 'exists:users,id',
        ]);

        $rt = Rt::create([
            'kode_rt' => $validated['kode_rt'],
            'nama_rt' => $validated['nama_rt'],
            'wilayah' => $validated['wilayah'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        if (!empty($validated['petugas_ids'])) {
            $rt->petugas()->sync($validated['petugas_ids']);
        }

        ActivityLog::log('TAMBAH_RT', "Menambahkan wilayah baru: {$rt->nama_rt} ({$rt->kode_rt})");

        return redirect()->route('admin.rt.index')->with('success', "RT {$rt->nama_rt} berhasil ditambahkan.");
    }

    public function update(Request $request, Rt $rt)
    {
        $validated = $request->validate([
            'kode_rt' => 'required|string|max:20|unique:rts,kode_rt,' . $rt->id,
            'nama_rt' => 'required|string|max:100',
            'wilayah' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'petugas_ids' => 'nullable|array',
            'petugas_ids.*' => 'exists:users,id',
        ]);

        $rt->update([
            'kode_rt' => $validated['kode_rt'],
            'nama_rt' => $validated['nama_rt'],
            'wilayah' => $validated['wilayah'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $rt->petugas()->sync($validated['petugas_ids'] ?? []);

        ActivityLog::log('UPDATE_RT', "Memperbarui data wilayah {$rt->nama_rt}");

        return redirect()->route('admin.rt.index')->with('success', "Data {$rt->nama_rt} berhasil diperbarui.");
    }

    public function destroy(Rt $rt)
    {
        if ($rt->pelanggans()->count() > 0) {
            return back()->with('error', "Tidak dapat menghapus {$rt->nama_rt} karena masih memiliki {$rt->pelanggans()->count()} pelanggan terdaftar.");
        }

        $nama = $rt->nama_rt;
        $rt->petugas()->detach();
        $rt->delete();

        ActivityLog::log('HAPUS_RT', "Menghapus wilayah: {$nama}");

        return redirect()->route('admin.rt.index')->with('success', "Wilayah {$nama} berhasil dihapus.");
    }
}
