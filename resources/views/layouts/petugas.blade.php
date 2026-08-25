<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Petugas RT') — HIPPAM Tirto Makmur</title>
    
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
                    },
                    colors: {
                        aqua: {
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        /* Touch optimization */
        button, a, input, select {
            touch-action: manipulation;
        }
        
        /* Garis Tabel Tebal & Jelas */
        table {
            border-collapse: collapse !important;
            width: 100%;
        }
        table th, table td {
            border: 1.5px solid #cbd5e1 !important;
        }
        table thead th {
            border-bottom: 2.5px solid #64748b !important;
            background-color: #f1f5f9 !important;
            font-weight: 700 !important;
        }
        
        /* Card Border & Shadow Global */
        .card-custom {
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 3px 10px 0 rgba(15, 23, 42, 0.07), 0 1px 3px 0 rgba(15, 23, 42, 0.05);
            border-radius: 1.25rem;
            background-color: #ffffff;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex flex-col">

    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-gradient-to-r from-sky-800 via-cyan-900 to-teal-900 text-white shadow-lg border-b-2 border-sky-950/50">
        <div class="max-w-4xl mx-auto px-4 py-3 sm:py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/30 shadow-inner group-hover:scale-105 transition">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" viewBox="0 0 32 32" fill="currentColor">
                            <path d="M16 3 C16 3 8 12 8 19 a8 8 0 0 0 16 0 C24 12 16 3 16 3 Z" opacity="0.95"/>
                            <path d="M16 9 C16 9 11 15.5 11 19.5 a5 5 0 0 0 10 0 C21 15.5 16 9 16 9 Z" fill="white" opacity="0.4"/>
                            <ellipse cx="13" cy="20" rx="1.5" ry="2.5" fill="white" opacity="0.5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm sm:text-base font-extrabold tracking-tight text-white leading-tight">HIPPAM Tirto Makmur</h1>
                        <p class="text-[11px] text-sky-200 font-medium">Panel Petugas RT &bull; Desa Argosari</p>
                    </div>
                </a>
            </div>

            <!-- Desktop Quick Nav -->
            <div class="hidden sm:flex items-center gap-1 bg-white/10 p-1 rounded-xl border border-white/20 text-xs">
                <a href="{{ route('petugas.dashboard') }}" class="px-3 py-1.5 rounded-lg font-bold transition {{ request()->routeIs('petugas.dashboard') ? 'bg-white text-sky-900 shadow-sm' : 'text-sky-100 hover:text-white hover:bg-white/10' }}">Beranda</a>
                <a href="{{ route('petugas.input-meter.index') }}" class="px-3 py-1.5 rounded-lg font-bold transition {{ request()->routeIs('petugas.input-meter.*') ? 'bg-white text-sky-900 shadow-sm' : 'text-sky-100 hover:text-white hover:bg-white/10' }}">Input Meter</a>
                <a href="{{ route('petugas.pembayaran.index') }}" class="px-3 py-1.5 rounded-lg font-bold transition {{ request()->routeIs('petugas.pembayaran.*') ? 'bg-white text-sky-900 shadow-sm' : 'text-sky-100 hover:text-white hover:bg-white/10' }}">Kasir / Bayar</a>
                <a href="{{ route('petugas.warga.index') }}" class="px-3 py-1.5 rounded-lg font-bold transition {{ request()->routeIs('petugas.warga.*') ? 'bg-white text-sky-900 shadow-sm' : 'text-sky-100 hover:text-white hover:bg-white/10' }}">Data Warga</a>
            </div>

            <div class="flex items-center gap-2">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 text-xs font-bold bg-indigo-600 hover:bg-indigo-500 rounded-xl text-white transition shadow-md flex items-center gap-1">
                        <span>Panel Admin</span> &rarr;
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-white/15 hover:bg-rose-500/80 hover:border-rose-400 rounded-xl border border-white/25 text-sky-100 hover:text-white transition shadow-sm" title="Keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-4 pb-24 space-y-4">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border-2 border-emerald-300 text-emerald-900 rounded-2xl shadow-sm animate-fade-in text-sm font-semibold">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-rose-50 border-2 border-rose-300 text-rose-900 rounded-2xl shadow-sm animate-fade-in text-sm font-semibold">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Thumb Navigation Bar (Symmetrical & Uniform Across All 4 Menus) -->
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t-2 border-slate-200 shadow-2xl pb-safe">
        <div class="max-w-md mx-auto grid grid-cols-4 h-16">
            <!-- 1. Beranda -->
            <a href="{{ route('petugas.dashboard') }}" 
               class="flex flex-col items-center justify-center text-[11px] font-semibold transition {{ request()->routeIs('petugas.dashboard') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('petugas.dashboard') ? '2.5' : '1.8' }}" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Beranda</span>
            </a>

            <!-- 2. Input Meter (Same Uniform Icon Format) -->
            <a href="{{ route('petugas.input-meter.index') }}" 
               class="flex flex-col items-center justify-center text-[11px] font-semibold transition {{ request()->routeIs('petugas.input-meter.*') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('petugas.input-meter.*') ? '2.5' : '1.8' }}" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                <span>Input Meter</span>
            </a>

            <!-- 3. Kasir / Bayar -->
            <a href="{{ route('petugas.pembayaran.index') }}" 
               class="flex flex-col items-center justify-center text-[11px] font-semibold transition {{ request()->routeIs('petugas.pembayaran.*') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('petugas.pembayaran.*') ? '2.5' : '1.8' }}" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span>Kasir / Bayar</span>
            </a>

            <!-- 4. Data Warga -->
            <a href="{{ route('petugas.warga.index') }}" 
               class="flex flex-col items-center justify-center text-[11px] font-semibold transition {{ request()->routeIs('petugas.warga.*') ? 'text-sky-600 font-extrabold' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('petugas.warga.*') ? '2.5' : '1.8' }}" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span>Data Warga</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
