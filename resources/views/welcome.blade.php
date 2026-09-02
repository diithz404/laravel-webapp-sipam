<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HIPPAM TIRTO MAKMUR — Sistem Informasi Pengelolaan Air Minum</title>
    <link rel="icon" type="image/png" href="{{ asset('logohippam.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logohippam.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 text-slate-100 min-h-screen flex flex-col justify-between font-sans antialiased p-4 sm:p-8">

    <!-- Header -->
    <header class="w-full max-w-5xl mx-auto flex items-center justify-between py-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logohippam.png') }}" alt="Logo HIPPAM TIRTO MAKMUR" class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl object-cover shadow-md bg-white p-0.5 border border-white/20">
            <div>
                <h1 class="text-sm sm:text-base font-black tracking-wider text-white uppercase leading-none">HIPPAM TIRTO MAKMUR</h1>
                <p class="text-[10px] sm:text-xs text-sky-400 font-semibold mt-0.5">Desa Argosari, Kec. Jabung, Kab. Malang</p>
            </div>
        </div>

        <div>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl transition shadow-md">Dashboard Admin &rarr;</a>
                @else
                    <a href="{{ route('petugas.dashboard') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold rounded-xl transition shadow-md">Dashboard Petugas &rarr;</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold rounded-xl transition shadow-sm">Masuk (Login)</a>
            @endauth
        </div>
    </header>

    <!-- Hero Body -->
    <main class="w-full max-w-4xl mx-auto my-auto py-12 text-center flex flex-col items-center">
        <div class="mb-6 relative group">
            <div class="absolute -inset-1.5 bg-gradient-to-r from-sky-500 to-teal-400 rounded-3xl blur opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse"></div>
            <img src="{{ asset('logohippam.png') }}" alt="Logo HIPPAM TIRTO MAKMUR" class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-3xl object-cover shadow-2xl bg-white p-1.5 border-2 border-white/30">
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-300 border border-sky-500/30 mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            SIPAM • Sistem Informasi Pengelolaan Air Minum
        </span>

        <h2 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight max-w-2xl">
            Layanan Pencatatan Meter &amp; Iuran Air <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-teal-300">Tirto Makmur</span>
        </h2>

        <p class="text-slate-300 text-xs sm:text-sm max-w-xl mt-4 font-normal leading-relaxed">
            Platform digitalisasi pencatatan angka meter air warga, rekapitulasi tarif berjenjang, kasir pembayaran tunai, cetak kwitansi resmi, dan pengiriman bukti otomatis via WhatsApp.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('login') }}" 
               class="px-6 py-3.5 bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-400 hover:to-teal-400 text-slate-950 font-black text-sm rounded-2xl transition shadow-xl flex items-center gap-2 transform hover:-translate-y-0.5">
                <span>Masuk ke Sistem</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-5xl mx-auto py-4 border-t border-white/10 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} HIPPAM TIRTO MAKMUR, Desa Argosari. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
