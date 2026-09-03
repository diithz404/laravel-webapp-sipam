<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi #{{ $catatanMeter->pelanggan->no_rekening }} - {{ $catatanMeter->periode->nama_periode }} - HIPPAM TIRTO MAKMUR</title>
    <link rel="icon" type="image/png" href="{{ asset('logohippam.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logohippam.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- html2pdf & html2canvas for PDF and Image export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .print-receipt { 
                box-shadow: none !important; 
                border: 1.5px solid #000 !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                margin: 0 !important; 
            }
        }
        .font-mono-receipt {
            font-family: 'Courier Prime', monospace, 'Courier New', Courier;
        }
    </style>
</head>
<body class="bg-slate-200 min-h-screen p-3 sm:p-6 flex flex-col items-center justify-center font-sans antialiased text-slate-900"
      x-data="{ 
          waModal: false, 
          inputPhone: '{{ preg_replace('/[^0-9]/', '', $catatanMeter->pelanggan->no_hp ?? '') }}',
          copied: false,
          isGenerating: false,
          guideModal: false,
          guideMessage: '',

          getCleanPhone() {
              let p = this.inputPhone.replace(/[^0-9]/g, '');
              if (p.startsWith('0')) {
                  p = '62' + p.substring(1);
              } else if (p.startsWith('8')) {
                  p = '62' + p;
              }
              return p;
          },

          getWaUrl() {
              const text = document.getElementById('rawWaText').value;
              return 'https://wa.me/' + this.getCleanPhone() + '?text=' + encodeURIComponent(text);
          },

          // 1. KIRIM DOKUMEN PDF ASLI KE WHATSAPP
          async sharePdfToWhatsApp() {
              const element = document.getElementById('receiptSheet');
              const fileName = 'Kwitansi_{{ preg_replace('/[^A-Za-z0-9]/', '_', $catatanMeter->pelanggan->nama) }}_{{ $catatanMeter->periode->nama_periode }}.pdf';
              
              this.isGenerating = true;

              const opt = {
                  margin:       [4, 4, 4, 4],
                  filename:     fileName,
                  image:        { type: 'jpeg', quality: 0.98 },
                  html2canvas:  { scale: 2.5, useCORS: true, letterRendering: true, backgroundColor: '#ffffff' },
                  jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' }
              };

              try {
                  const pdfBlob = await html2pdf().set(opt).from(element).outputPdf('blob');
                  const pdfFile = new File([pdfBlob], fileName, { type: 'application/pdf' });

                  // Jika di HP / Browser yang mendukung Web Share API (Android / iOS):
                  if (navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
                      await navigator.share({
                          files: [pdfFile],
                          title: 'Kwitansi Air HIPPAM',
                          text: 'Berikut kami lampirkan dokumen Kwitansi Pembayaran Air HIPPAM Tirto Makmur.'
                      });
                  } else {
                      // Fallback di Desktop/Laptop: Otomatis Download PDF & Buka WhatsApp
                      html2pdf().set(opt).from(element).save();
                      
                      this.guideMessage = 'File dokumen PDF telah otomatis di-download ke perangkat Anda. WhatsApp Web akan terbuka, silakan klik ikon klip kertas (📎 Attach Document) dan kirim file PDF yang baru saja diunduh.';
                      this.guideModal = true;
                      
                      setTimeout(() => {
                          window.open(this.getWaUrl(), '_blank');
                      }, 1200);
                  }
              } catch (err) {
                  console.error(err);
                  // Fallback standard save
                  html2pdf().set(opt).from(element).save();
              } finally {
                  this.isGenerating = false;
                  this.waModal = false;
              }
          },

          // 2. KIRIM FILE GAMBAR STRUK (FOTO PNG HD) KE WHATSAPP
          async shareImageToWhatsApp() {
              const element = document.getElementById('receiptSheet');
              const fileName = 'Struk_{{ preg_replace('/[^A-Za-z0-9]/', '_', $catatanMeter->pelanggan->nama) }}_{{ $catatanMeter->periode->nama_periode }}.png';
              
              this.isGenerating = true;

              try {
                  const canvas = await html2canvas(element, { scale: 3, useCORS: true, backgroundColor: '#ffffff' });
                  canvas.toBlob(async (blob) => {
                      const imageFile = new File([blob], fileName, { type: 'image/png' });

                      if (navigator.canShare && navigator.canShare({ files: [imageFile] })) {
                          await navigator.share({
                              files: [imageFile],
                              title: 'Struk Kwitansi HIPPAM',
                              text: 'Struk Pembayaran Air HIPPAM Tirto Makmur'
                          });
                      } else {
                          // Download Gambar & Buka WA
                          const link = document.createElement('a');
                          link.download = fileName;
                          link.href = canvas.toDataURL('image/png');
                          link.click();

                          this.guideMessage = 'File gambar struk telah di-download. WhatsApp Web akan terbuka, silakan kirim gambar tersebut ke chat warga.';
                          this.guideModal = true;

                          setTimeout(() => {
                              window.open(this.getWaUrl(), '_blank');
                          }, 1200);
                      }
                      this.isGenerating = false;
                      this.waModal = false;
                  }, 'image/png');
              } catch (err) {
                  console.error(err);
                  this.isGenerating = false;
              }
          },

          // 3. UNDUH DOKUMEN PDF KE MEMORI
          downloadPdfOnly() {
              const element = document.getElementById('receiptSheet');
              const fileName = 'Kwitansi_{{ preg_replace('/[^A-Za-z0-9]/', '_', $catatanMeter->pelanggan->nama) }}_{{ $catatanMeter->periode->nama_periode }}.pdf';
              
              this.isGenerating = true;
              const opt = {
                  margin:       [4, 4, 4, 4],
                  filename:     fileName,
                  image:        { type: 'jpeg', quality: 0.98 },
                  html2canvas:  { scale: 2.5, useCORS: true, letterRendering: true, backgroundColor: '#ffffff' },
                  jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' }
              };

              html2pdf().set(opt).from(element).save().then(() => {
                  this.isGenerating = false;
              }).catch(() => {
                  this.isGenerating = false;
              });
          },

          // 4. BUKA CHAT WA (TEKS + LINK DIGITAL)
          openWhatsAppText() {
              window.open(this.getWaUrl(), '_blank');
              this.waModal = false;
          },

          copyText() {
              const text = document.getElementById('rawWaText').value;
              navigator.clipboard.writeText(text).then(() => {
                  this.copied = true;
                  setTimeout(() => { this.copied = false; }, 2500);
              });
          }
      }">

    @php
        $cleanPhone = preg_replace('/[^0-9]/', '', $catatanMeter->pelanggan->no_hp ?? '');
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }
        $latestPayment = $catatanMeter->pembayarans->last();
        $namaKasir = $latestPayment ? ($latestPayment->kasir->name ?? 'Petugas RT') : (auth()->user()->name ?? 'Petugas RT');
        $tglBayar = $latestPayment ? $latestPayment->tanggal_bayar->format('d/m/Y') : date('d/m/Y');
        $noTrx = $latestPayment->no_transaksi ?? 'TRX-' . date('Ym') . '-' . str_pad($catatanMeter->id, 4, '0', STR_PAD_LEFT);

        // Format Teks Kwitansi WhatsApp
        $waFullText = "*KWITANSI PEMBAYARAN AIR*\n"
                    . "*HIPPAM TIRTO MAKMUR*\n"
                    . "Desa Argosari, Kec. Jabung, Kab. Malang\n"
                    . "========================================\n"
                    . "No. Transaksi : {$noTrx}\n"
                    . "Tanggal Bayar : {$tglBayar}\n"
                    . "Petugas Kasir : {$namaKasir}\n"
                    . "----------------------------------------\n"
                    . "*DATA PELANGGAN*\n"
                    . "No. Pelanggan : {$catatanMeter->pelanggan->no_rekening}\n"
                    . "Nama Warga    : {$catatanMeter->pelanggan->nama}\n"
                    . "Alamat        : " . ($catatanMeter->pelanggan->dusun ? 'Dusun ' . preg_replace('/^dusun\s+/i', '', $catatanMeter->pelanggan->dusun) : $catatanMeter->pelanggan->alamat) . " (" . ($catatanMeter->pelanggan->rt->nama_rt ?? 'RT') . ")\n"
                    . "Periode       : {$catatanMeter->periode->nama_periode}\n"
                    . "----------------------------------------\n"
                    . "*RINCIAN METER AIR*\n"
                    . "Stand Lalu    : " . number_format($catatanMeter->angka_lalu) . " m³\n"
                    . "Stand Kini    : " . ($catatanMeter->angka_ini !== null ? number_format($catatanMeter->angka_ini) : '-') . " m³\n"
                    . "Pemakaian Air : " . number_format($catatanMeter->pemakaian) . " m³\n"
                    . "----------------------------------------\n"
                    . "*RINCIAN BIAYA*\n"
                    . "- Standar (" . $catatanMeter->pemakaian_standar . " m³ x Rp" . number_format($catatanMeter->snapshot_tarif_standar, 0, ',', '.') . ") : Rp" . number_format($catatanMeter->pemakaian_standar * $catatanMeter->snapshot_tarif_standar, 0, ',', '.') . "\n"
                    . ($catatanMeter->pemakaian_progresif > 0 ? "- Progresif (" . $catatanMeter->pemakaian_progresif . " m³ x Rp" . number_format($catatanMeter->snapshot_tarif_progresif, 0, ',', '.') . ") : Rp" . number_format($catatanMeter->pemakaian_progresif * $catatanMeter->snapshot_tarif_progresif, 0, ',', '.') . "\n" : "")
                    . "- Biaya Admin : Rp" . number_format($catatanMeter->biaya_admin, 0, ',', '.') . "\n"
                    . ($catatanMeter->tunggakan_lalu > 0 ? "- Tunggakan Lalu : Rp" . number_format($catatanMeter->tunggakan_lalu, 0, ',', '.') . "\n" : "")
                    . "----------------------------------------\n"
                    . "*TOTAL TAGIHAN : Rp" . number_format($catatanMeter->total_tagihan, 0, ',', '.') . "*\n"
                    . "*TOTAL DIBAYAR : Rp" . number_format($catatanMeter->total_dibayar, 0, ',', '.') . "*\n"
                    . ($catatanMeter->sisa_tagihan > 0 ? "*SISA TAGIHAN  : Rp" . number_format($catatanMeter->sisa_tagihan, 0, ',', '.') . "*\n" : "")
                    . "*STATUS BAYAR  : " . strtoupper($catatanMeter->status_bayar) . " " . ($catatanMeter->status_bayar === 'lunas' ? '✅ LUNAS' : '⚡ SEBAGIAN') . "*\n"
                    . "========================================\n"
                    . "📄 *Lihat Kwitansi Digital Resmi:*\n"
                    . route('kwitansi.show', $catatanMeter->id) . "\n\n"
                    . "_Simpan pesan ini sebagai bukti pembayaran iuran air yang sah._\n"
                    . "_HIPPAM TIRTO MAKMUR._";
    @endphp

    {{-- Hidden Raw Text for Clipboard and WA generator --}}
    <textarea id="rawWaText" class="hidden">{{ $waFullText }}</textarea>

    <!-- Action Toolbar (Hidden in Print) -->
    <div class="no-print w-full max-w-lg mb-4 space-y-2">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <button onclick="window.history.back()" class="px-3.5 py-2 text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 rounded-xl shadow-sm border border-slate-300 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </button>

            <div class="flex items-center gap-1.5 flex-wrap">
                {{-- Tombol Utama: Kirim PDF / Gambar ke WhatsApp --}}
                <button type="button" @click="waModal = true"
                   class="px-3.5 py-2 text-xs font-extrabold bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 active:scale-95 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Kirim ke WA</span>
                </button>

                {{-- Tombol Download PDF Langsung --}}
                <button type="button" @click="downloadPdfOnly()" :disabled="isGenerating"
                        class="px-3 py-2 text-xs font-bold bg-white hover:bg-slate-100 text-slate-800 rounded-xl shadow-sm border border-slate-300 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 16l4-5h-3V4h-2v7H8l4 5zm9-13v18H3V3h18zm-2 2H5v14h14V5z"/></svg>
                    <span>Unduh PDF</span>
                </button>

                {{-- Tombol Salin Teks --}}
                <button type="button" @click="copyText()"
                        class="px-3 py-2 text-xs font-bold bg-white hover:bg-slate-100 text-slate-800 rounded-xl shadow-sm border border-slate-300 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span x-text="copied ? '✓ Tersalin!' : 'Salin'"></span>
                </button>

                {{-- Tombol Cetak / Browser Print --}}
                <button onclick="window.print()" class="px-3 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white rounded-xl shadow-md transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        {{-- Toast Copied --}}
        <div x-show="copied" x-transition class="p-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl text-center shadow-lg" x-cloak>
            ✓ Format teks kwitansi lengkap berhasil disalin ke clipboard!
        </div>

        {{-- Loading Indicator saat render PDF/Gambar --}}
        <div x-show="isGenerating" x-transition class="p-2.5 bg-sky-600 text-white text-xs font-bold rounded-xl text-center shadow-lg flex items-center justify-center gap-2" x-cloak>
            <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span>Sedang memproses struk kwitansi... Mohon tunggu sebentar.</span>
        </div>
    </div>

    <!-- Official Receipt Sheet (Target for Print, PDF, and Image) -->
    <div id="receiptSheet" class="print-receipt bg-white w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-xl border-2 border-slate-300 relative text-slate-800">
        
        <!-- Header HIPPAM -->
        <div class="text-center pb-3 border-b-2 border-slate-800">
            <div class="flex items-center justify-center gap-2.5 mb-1.5">
                <img src="{{ asset('logohippam.png') }}" alt="Logo HIPPAM TIRTO MAKMUR" class="w-11 h-11 object-contain rounded-xl">
                <div class="text-left">
                    <h2 class="text-base font-black tracking-wider text-slate-900 uppercase leading-tight">HIPPAM TIRTO MAKMUR</h2>
                    <p class="text-[10px] font-bold text-slate-600">DESA ARGOSARI, KEC. JABUNG, KAB. MALANG</p>
                </div>
            </div>
            <div class="inline-block mt-1 px-3.5 py-0.5 bg-slate-900 text-white text-[10px] font-extrabold tracking-widest rounded-full uppercase">
                KWITANSI PEMBAYARAN AIR
            </div>
        </div>

        <!-- Customer & Period Info -->
        <div class="py-3.5 space-y-1.5 text-xs border-b border-slate-300">
            <div class="flex justify-between">
                <span class="text-slate-500">No. Transaksi:</span>
                <span class="font-bold font-mono text-slate-900">{{ $noTrx }}</span>
            </div>
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
                <span class="font-medium text-slate-800 text-right">
                    {{ $catatanMeter->pelanggan->dusun ? 'Dusun ' . preg_replace('/^dusun\s+/i', '', $catatanMeter->pelanggan->dusun) : $catatanMeter->pelanggan->alamat }} ({{ $catatanMeter->pelanggan->rt->nama_rt ?? 'RT' }})
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Periode Tagihan:</span>
                <span class="font-bold text-sky-800">{{ $catatanMeter->periode->nama_periode }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal Bayar:</span>
                <span class="font-medium text-slate-800">{{ $tglBayar }}</span>
            </div>
        </div>

        <!-- Meter Details -->
        <div class="py-3 bg-slate-50 rounded-xl p-3 my-3 border border-slate-200 text-xs space-y-1.5">
            <div class="flex justify-between items-center text-slate-600 font-semibold border-b border-slate-200 pb-1.5">
                <span>Stand Lalu</span>
                <span>Stand Kini</span>
                <span class="text-slate-900 font-bold">Pemakaian Air</span>
            </div>
            <div class="flex justify-between items-center font-mono text-sm pt-0.5">
                <span class="text-slate-600">{{ number_format($catatanMeter->angka_lalu) }}</span>
                <span class="text-slate-600">{{ $catatanMeter->angka_ini !== null ? number_format($catatanMeter->angka_ini) : '-' }}</span>
                <span class="font-black text-sky-700">{{ number_format($catatanMeter->pemakaian) }} m³</span>
            </div>
        </div>

        <!-- Breakdown Calculations -->
        <div class="py-2 space-y-1.5 text-xs border-b border-slate-300">
            <div class="flex justify-between text-slate-600">
                <span>Pemakaian 0 - 20 m³ ({{ $catatanMeter->pemakaian_standar }} m³ &times; Rp{{ number_format($catatanMeter->snapshot_tarif_standar, 0, ',', '.') }})</span>
                <span class="font-mono text-slate-800 font-medium">Rp{{ number_format($catatanMeter->pemakaian_standar * $catatanMeter->snapshot_tarif_standar, 0, ',', '.') }}</span>
            </div>
            @if($catatanMeter->pemakaian_progresif > 0)
                <div class="flex justify-between text-slate-600">
                    <span>Pemakaian &gt; 20 m³ ({{ $catatanMeter->pemakaian_progresif }} m³ &times; Rp{{ number_format($catatanMeter->snapshot_tarif_progresif, 0, ',', '.') }})</span>
                    <span class="font-mono text-slate-800 font-medium">Rp{{ number_format($catatanMeter->pemakaian_progresif * $catatanMeter->snapshot_tarif_progresif, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between text-slate-600">
                <span>Biaya Administrasi</span>
                <span class="font-mono text-slate-800 font-medium">Rp{{ number_format($catatanMeter->biaya_admin, 0, ',', '.') }}</span>
            </div>
            @if($catatanMeter->tunggakan_lalu > 0)
                <div class="flex justify-between text-rose-600 font-semibold">
                    <span>Tunggakan Bulan Lalu</span>
                    <span class="font-mono">Rp{{ number_format($catatanMeter->tunggakan_lalu, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Total Sum -->
        <div class="py-3 space-y-1.5 border-b-2 border-slate-800">
            <div class="flex justify-between items-center text-sm font-bold text-slate-900">
                <span>TOTAL TAGIHAN</span>
                <span class="font-mono text-base font-black">Rp{{ number_format($catatanMeter->total_tagihan, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold text-emerald-700">
                <span>JUMLAH DIBAYAR</span>
                <span class="font-mono text-sm font-black">Rp{{ number_format($catatanMeter->total_dibayar, 0, ',', '.') }}</span>
            </div>
            @if($catatanMeter->sisa_tagihan > 0)
                <div class="flex justify-between items-center text-xs font-bold text-rose-600">
                    <span>SISA TAGIHAN</span>
                    <span class="font-mono font-black">Rp{{ number_format($catatanMeter->sisa_tagihan, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Status Watermark & Signature -->
        <div class="mt-4 flex items-center justify-between text-xs">
            <div>
                @if($catatanMeter->status_bayar === 'lunas')
                    <div class="inline-block px-3 py-1.5 border-2 border-emerald-600 text-emerald-700 font-black text-sm tracking-wider rounded-lg transform -rotate-6">
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
                <p class="text-[10px] text-slate-400 mt-2 font-mono">No TRX: {{ $noTrx }}</p>
            </div>

            <div class="text-center w-36">
                <p class="text-[10px] text-slate-500 font-medium">Petugas Kasir,</p>
                <div class="h-10"></div>
                <p class="font-bold border-t border-slate-400 pt-0.5 text-slate-800 text-[11px]">
                    {{ $namaKasir }}
                </p>
            </div>
        </div>

        <!-- Cut Tear Line -->
        <div class="mt-6 pt-3 border-t border-dashed border-slate-400 text-center text-[10px] text-slate-400 font-mono">
            - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            <p class="mt-1">Simpan kwitansi ini sebagai bukti pembayaran yang sah</p>
        </div>

    </div>

    <!-- MODAL PILIHAN FORMAT PENGIRIMAN KE WHATSAPP -->
    <div x-show="waModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm" @click="if(!isGenerating) waModal = false"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md border-2 border-slate-300 z-10 overflow-hidden my-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-emerald-800 to-teal-900 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black">Kirim Kwitansi ke WhatsApp</h4>
                        <p class="text-[11px] text-emerald-200">{{ $catatanMeter->pelanggan->nama }} • {{ $catatanMeter->pelanggan->no_rekening }}</p>
                    </div>
                </div>
                <button @click="waModal = false" :disabled="isGenerating" class="p-1 text-white/80 hover:text-white rounded-lg">&times;</button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp Tujuan:</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">📱 +</span>
                        <input type="text" x-model="inputPhone"
                               placeholder="Misal: 081234567890"
                               class="w-full border-2 border-slate-300 rounded-xl pl-9 pr-3 py-2 text-sm font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                    </div>
                </div>

                {{-- Opsi Pilihan Pengiriman --}}
                <div class="space-y-2 pt-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Pilih Format Struk:</label>
                    
                    {{-- 1. Share File PDF --}}
                    <button type="button" @click="sharePdfToWhatsApp()" :disabled="isGenerating"
                            class="w-full p-3 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white rounded-2xl shadow-md transition flex items-center justify-between gap-3 text-left active:scale-[0.99]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 16l4-5h-3V4h-2v7H8l4 5zm9-13v18H3V3h18zm-2 2H5v14h14V5z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-extrabold flex items-center gap-1.5">
                                    <span>📄 Kirim File Dokumen PDF</span>
                                    <span class="px-1.5 py-0.2 bg-white/20 rounded text-[9px] uppercase font-bold">Resmi</span>
                                </div>
                                <p class="text-[10px] text-rose-100">Kirim lampiran dokumen PDF kwitansi ke WhatsApp warga</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold">&rarr;</span>
                    </button>

                    {{-- 2. Share Gambar Struk Foto --}}
                    <button type="button" @click="shareImageToWhatsApp()" :disabled="isGenerating"
                            class="w-full p-3 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-500 hover:to-teal-600 text-white rounded-2xl shadow-md transition flex items-center justify-between gap-3 text-left active:scale-[0.99]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-extrabold flex items-center gap-1.5">
                                    <span>🖼️ Kirim Gambar Struk (Foto HD)</span>
                                    <span class="px-1.5 py-0.2 bg-white/20 rounded text-[9px] uppercase font-bold">Populer</span>
                                </div>
                                <p class="text-[10px] text-teal-100">Struk langsung tampil sebagai foto di obrolan WhatsApp</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold">&rarr;</span>
                    </button>

                    {{-- 3. Teks Pesan & Link Digital --}}
                    <button type="button" @click="openWhatsAppText()" :disabled="isGenerating"
                            class="w-full p-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white rounded-2xl shadow-md transition flex items-center justify-between gap-3 text-left active:scale-[0.99]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-extrabold">💬 Kirim Pesan Teks &amp; Tautan Kwitansi</div>
                                <p class="text-[10px] text-emerald-100">Kirim format rincian tagihan + tautan web resmi</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold">&rarr;</span>
                    </button>
                </div>

                <div class="pt-2">
                    <button type="button" @click="waModal = false" :disabled="isGenerating"
                            class="w-full py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PANDUAN PENGIRIMAN DI LAPTOP / DESKTOP -->
    <div x-show="guideModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="guideModal = false"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md border-2 border-slate-300 z-10 overflow-hidden my-auto p-6 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900">File Struk Berhasil Diunduh!</h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed" x-text="guideMessage"></p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-[11px] text-amber-800 text-left font-medium">
                💡 <strong>Tips di WhatsApp Web:</strong> Klik ikon <strong>📎 (Lampiran)</strong> ➔ pilih <strong>Dokumen (untuk PDF)</strong> atau <strong>Foto/Video (untuk Gambar)</strong> ➔ kirim ke warga.
            </div>
            <button type="button" @click="guideModal = false"
                    class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl transition">
                Mengerti
            </button>
        </div>
    </div>

</body>
</html>
