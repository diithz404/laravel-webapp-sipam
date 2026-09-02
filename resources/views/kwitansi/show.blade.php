<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi #{{ $catatanMeter->pelanggan->no_rekening }} - {{ $catatanMeter->periode->nama_periode }} - HIPPAM TIRTO MAKMUR</title>
    <link rel="icon" type="image/png" href="{{ asset('logohippam.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logohippam.png') }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .print-receipt { box-shadow: none !important; border: 1px solid #000 !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; }
        }
        .font-mono-receipt {
            font-family: 'Courier Prime', monospace, 'Courier New', Courier;
        }
    </style>
</head>
<body class="bg-slate-200 min-h-screen p-4 sm:p-8 flex flex-col items-center justify-center font-sans antialiased text-slate-900">

    @php
        $cleanPhone = preg_replace('/[^0-9]/', '', $catatanMeter->pelanggan->no_hp ?? '');
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $latestPayment = $catatanMeter->pembayarans->last();
        $waText = "*BUKTI PEMBAYARAN AIR - HIPPAM TIRTO MAKMUR*\n"
                . "----------------------------------------\n"
                . "No. Pelanggan: {$catatanMeter->pelanggan->no_rekening}\n"
                . "Nama Warga   : {$catatanMeter->pelanggan->nama}\n"
                . "Alamat       : {$catatanMeter->pelanggan->alamat}\n"
                . "Periode      : {$catatanMeter->periode->nama_periode}\n\n"
                . "*Rincian Meter:*\n"
                . "- Stand Lalu : " . number_format($catatanMeter->angka_lalu) . "\n"
                . "- Stand Kini : " . ($catatanMeter->angka_ini !== null ? number_format($catatanMeter->angka_ini) : '-') . "\n"
                . "- Pemakaian  : " . number_format($catatanMeter->pemakaian) . " m³\n\n"
                . "*Rincian Tagihan:*\n"
                . "- Pemakaian  : Rp" . number_format($catatanMeter->biaya_pemakaian, 0, ',', '.') . "\n"
                . "- Beban/Admin: Rp" . number_format($catatanMeter->biaya_admin, 0, ',', '.') . "\n"
                . ($catatanMeter->tunggakan_lalu > 0 ? "- Tunggakan  : Rp" . number_format($catatanMeter->tunggakan_lalu, 0, ',', '.') . "\n" : "")
                . "- Total Tagihan: Rp" . number_format($catatanMeter->total_tagihan, 0, ',', '.') . "\n"
                . "- Sudah Dibayar: Rp" . number_format($catatanMeter->total_dibayar, 0, ',', '.') . "\n"
                . "- Sisa Tagihan : Rp" . number_format($catatanMeter->sisa_tagihan, 0, ',', '.') . "\n"
                . "*Status        : " . strtoupper($catatanMeter->status_bayar) . "* " . ($catatanMeter->status_bayar === 'lunas' ? '✅' : '⏳') . "\n\n"
                . "No. Transaksi: " . ($latestPayment->no_transaksi ?? '-') . "\n"
                . "Tanggal Bayar: " . ($latestPayment ? $latestPayment->tanggal_bayar->format('d/m/Y') : date('d/m/Y')) . "\n"
                . "Petugas Kasir: " . ($latestPayment ? ($latestPayment->kasir->name ?? 'Petugas RT') : (auth()->user()->name ?? 'Petugas RT')) . "\n\n"
                . "_Terima kasih atas pembayaran iuran air HIPPAM TIRTO MAKMUR._";
        $waReceiptUrl = "https://wa.me/" . ($cleanPhone ?: '') . "?text=" . urlencode($waText);
    @endphp

    <!-- Action Toolbar (Hidden in Print) -->
    <div class="no-print w-full max-w-md mb-4 flex items-center justify-between gap-2">
        <button onclick="window.history.back()" class="px-4 py-2 text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 rounded-xl shadow-sm border border-slate-300 transition flex items-center gap-1.5">
            &larr; Kembali
        </button>
        <div class="flex items-center gap-2">
            <a href="{{ $waReceiptUrl }}" target="_blank"
               class="px-3.5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Kirim WA</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Receipt Sheet -->
    <div class="print-receipt bg-white w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-xl border border-slate-300 relative text-slate-800">
        
        <!-- Header HIPPAM -->
        <div class="text-center pb-3 border-b-2 border-slate-800">
            <div class="flex items-center justify-center gap-2.5 mb-1.5">
                <img src="{{ asset('logohippam.png') }}" alt="Logo HIPPAM TIRTO MAKMUR" class="w-10 h-10 object-contain rounded-xl">
                <div class="text-left">
                    <h2 class="text-base font-extrabold tracking-wider text-slate-900 uppercase leading-none">HIPPAM TIRTO MAKMUR</h2>
                    <p class="text-[10px] font-semibold text-slate-600 mt-0.5">DESA ARGOSARI, KEC. JABUNG, KAB. MALANG</p>
                </div>
            </div>
            <div class="inline-block mt-1 px-3 py-0.5 bg-slate-900 text-white text-[10px] font-bold tracking-widest rounded-full uppercase">
                KWITANSI PEMBAYARAN AIR
            </div>
        </div>

        <!-- Customer & Period Info -->
        <div class="py-3.5 space-y-1.5 text-xs border-b border-slate-300">
            <div class="flex justify-between">
                <span class="text-slate-500">No. Pelanggan:</span>
                <span class="font-bold font-mono text-slate-900">{{ $catatanMeter->pelanggan->no_rekening }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Nama Pelanggan:</span>
                <span class="font-bold text-slate-900">{{ $catatanMeter->pelanggan->nama }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Alamat:</span>
                <span class="font-medium text-slate-800 text-right">{{ $catatanMeter->pelanggan->alamat }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Periode Tagihan:</span>
                <span class="font-bold text-sky-800">{{ $catatanMeter->periode->nama_periode }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal Bayar:</span>
                <span class="font-medium text-slate-800">{{ $latestPayment ? $latestPayment->tanggal_bayar->format('d/m/Y') : date('d/m/Y') }}</span>
            </div>
        </div>

        <!-- Meter Details -->
        <div class="py-3 bg-slate-50 rounded-xl p-3 my-3 border border-slate-200 text-xs space-y-1.5">
            <div class="flex justify-between items-center text-slate-600 font-semibold border-b border-slate-200 pb-1.5">
                <span>Stand Lalu</span>
                <span>Stand Kini</span>
                <span class="text-slate-900">Pemakaian Air</span>
            </div>
            <div class="flex justify-between items-center font-mono text-sm pt-0.5">
                <span class="text-slate-600">{{ $catatanMeter->angka_lalu }}</span>
                <span class="text-slate-600">{{ $catatanMeter->angka_ini ?? '-' }}</span>
                <span class="font-bold text-sky-700">{{ $catatanMeter->pemakaian }} m³</span>
            </div>
        </div>

        <!-- Breakdown Calculations -->
        <div class="py-2 space-y-1.5 text-xs border-b border-slate-300">
            <div class="flex justify-between text-slate-600">
                <span>Pemakaian 0 - 20 m³ ({{ $catatanMeter->pemakaian_standar }} m³ &times; Rp{{ number_format($catatanMeter->snapshot_tarif_standar, 0, ',', '.') }})</span>
                <span class="font-mono text-slate-800">Rp{{ number_format($catatanMeter->pemakaian_standar * $catatanMeter->snapshot_tarif_standar, 0, ',', '.') }}</span>
            </div>
            @if($catatanMeter->pemakaian_progresif > 0)
                <div class="flex justify-between text-slate-600">
                    <span>Pemakaian &gt; 20 m³ ({{ $catatanMeter->pemakaian_progresif }} m³ &times; Rp{{ number_format($catatanMeter->snapshot_tarif_progresif, 0, ',', '.') }})</span>
                    <span class="font-mono text-slate-800">Rp{{ number_format($catatanMeter->pemakaian_progresif * $catatanMeter->snapshot_tarif_progresif, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between text-slate-600">
                <span>Biaya Administrasi</span>
                <span class="font-mono text-slate-800">Rp{{ number_format($catatanMeter->biaya_admin, 0, ',', '.') }}</span>
            </div>
            @if($catatanMeter->tunggakan_lalu > 0)
                <div class="flex justify-between text-amber-700 font-semibold">
                    <span>Tunggakan Bulan Lalu</span>
                    <span class="font-mono">Rp{{ number_format($catatanMeter->tunggakan_lalu, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Total Sum -->
        <div class="py-3 space-y-1.5 border-b-2 border-slate-800">
            <div class="flex justify-between items-center text-sm font-bold text-slate-900">
                <span>TOTAL TAGIHAN</span>
                <span class="font-mono text-base">Rp{{ number_format($catatanMeter->total_tagihan, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-semibold text-emerald-700">
                <span>JUMLAH DIBAYAR</span>
                <span class="font-mono text-sm">Rp{{ number_format($catatanMeter->total_dibayar, 0, ',', '.') }}</span>
            </div>
            @if($catatanMeter->sisa_tagihan > 0)
                <div class="flex justify-between items-center text-xs font-bold text-rose-600">
                    <span>SISA TAGIHAN</span>
                    <span class="font-mono">Rp{{ number_format($catatanMeter->sisa_tagihan, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Status Watermark & Signature -->
        <div class="mt-4 flex items-center justify-between text-xs">
            <div>
                @if($catatanMeter->status_bayar === 'lunas')
                    <div class="inline-block px-3 py-1.5 border-2 border-emerald-600 text-emerald-700 font-extrabold text-sm tracking-wider rounded-lg transform -rotate-6">
                        *** LUNAS ***
                    </div>
                @elseif($catatanMeter->status_bayar === 'sebagian')
                    <div class="inline-block px-3 py-1.5 border-2 border-amber-600 text-amber-700 font-bold text-xs tracking-wider rounded-lg">
                        SEBAGIAN
                    </div>
                @else
                    <div class="inline-block px-3 py-1.5 border-2 border-rose-600 text-rose-700 font-bold text-xs tracking-wider rounded-lg">
                        BELUM LUNAS
                    </div>
                @endif
                <p class="text-[10px] text-slate-400 mt-2 font-mono">No TRX: {{ $latestPayment->no_transaksi ?? '-' }}</p>
            </div>

            <div class="text-center w-36">
                <p class="text-[10px] text-slate-500">Petugas Penerima,</p>
                <div class="h-10"></div>
                <p class="font-bold border-t border-slate-400 pt-0.5 text-slate-800 text-[11px]">
                    {{ $latestPayment ? ($latestPayment->kasir->name ?? 'Petugas RT') : (auth()->user()->name ?? 'Petugas RT') }}
                </p>
            </div>
        </div>

        <!-- Cut Tear Line -->
        <div class="mt-6 pt-3 border-t border-dashed border-slate-400 text-center text-[10px] text-slate-400 font-mono">
            - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            <p class="mt-1">Simpan kwitansi ini sebagai bukti pembayaran yang sah</p>
        </div>

    </div>

</body>
</html>
