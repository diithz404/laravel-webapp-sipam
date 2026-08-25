<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarif;
use App\Models\TarifTier;
use App\Models\ActivityLog;

class TarifController extends Controller
{
    public function index()
    {
        $activeTarif = Tarif::getActiveTarif();
        $allTarifs = Tarif::with(['tiers', 'creator'])->orderBy('tanggal_berlaku', 'desc')->get();

        return view('admin.tarif.index', compact('activeTarif', 'allTarifs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_skema' => 'required|string|max:150',
            'tarif_standar' => 'required|numeric|min:0',
            'batas_kuota_standar' => 'required|integer|min:1',
            'tarif_progresif' => 'required|numeric|min:0',
            'biaya_admin' => 'required|numeric|min:0',
            'tanggal_berlaku' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'tiers' => 'nullable|array',
            'tiers.*.batas_bawah' => 'required_with:tiers|integer|min:0',
            'tiers.*.batas_atas' => 'nullable|integer',
            'tiers.*.harga_per_m3' => 'required_with:tiers|numeric|min:0',
        ]);

        // Nonaktifkan tarif lama jika tanggal berlaku aktif sekarang
        if ($request->boolean('is_active', true)) {
            Tarif::query()->update(['is_active' => false]);
        }

        $tarif = Tarif::create([
            'nama_skema' => $validated['nama_skema'],
            'tarif_standar' => $validated['tarif_standar'],
            'batas_kuota_standar' => $validated['batas_kuota_standar'],
            'tarif_progresif' => $validated['tarif_progresif'],
            'biaya_admin' => $validated['biaya_admin'],
            'tanggal_berlaku' => $validated['tanggal_berlaku'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        // Simpan tiers jika dikirimkan, atau buat default 2 tiers
        if (!empty($validated['tiers'])) {
            foreach ($validated['tiers'] as $index => $tier) {
                TarifTier::create([
                    'tarif_id' => $tarif->id,
                    'urutan' => $index + 1,
                    'batas_bawah' => $tier['batas_bawah'],
                    'batas_atas' => $tier['batas_atas'] ?? null,
                    'harga_per_m3' => $tier['harga_per_m3'],
                ]);
            }
        } else {
            TarifTier::create([
                'tarif_id' => $tarif->id,
                'urutan' => 1,
                'batas_bawah' => 0,
                'batas_atas' => $tarif->batas_kuota_standar,
                'harga_per_m3' => $tarif->tarif_standar,
            ]);

            TarifTier::create([
                'tarif_id' => $tarif->id,
                'urutan' => 2,
                'batas_bawah' => $tarif->batas_kuota_standar,
                'batas_atas' => null,
                'harga_per_m3' => $tarif->tarif_progresif,
            ]);
        }

        ActivityLog::log('UPDATE_TARIF', "Membuat ketetapan tarif baru: {$tarif->nama_skema} (Standar: Rp{$tarif->tarif_standar}, Progresif: Rp{$tarif->tarif_progresif})");

        return redirect()->route('admin.tarif.index')->with('success', "Skema tarif {$tarif->nama_skema} berhasil disimpan dan diaktifkan.");
    }
}
