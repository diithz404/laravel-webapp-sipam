<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatatanMeter;
use App\Models\Tarif;

class KwitansiController extends Controller
{
    public function show(CatatanMeter $catatanMeter)
    {
        $catatanMeter->load(['pelanggan.rt', 'periode', 'pembayarans.kasir', 'pencatat', 'tarif']);

        $latestPayment = $catatanMeter->pembayarans->first();

        return view('kwitansi.show', compact('catatanMeter', 'latestPayment'));
    }
}
