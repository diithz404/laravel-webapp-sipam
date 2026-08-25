<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — HIPPAM Tirto Makmur</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        aqua: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        hippam: {
                            primary: '#0284c7',
                            secondary: '#0d9488',
                            dark: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(203, 213, 225, 0.9);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: #94a3b8;
            border-radius: 9999px;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Garis Tabel Tebal dan Jelas */
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
            color: #1e293b !important;
        }
        table tbody tr:hover {
            background-color: #f8fafc !important;
        }
        
        .card-custom {
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 2px 8px 0 rgba(15, 23, 42, 0.06), 0 1px 3px 0 rgba(15, 23, 42, 0.04);
            border-radius: 1rem;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
        }
        .card-custom:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px 0 rgba(15, 23, 42, 0.08), 0 2px 6px 0 rgba(15, 23, 42, 0.06);
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex flex-col min-h-screen" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-40 lg:hidden" x-cloak></div>

    <!-- Sidebar Navigation -->
    <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 border-r border-slate-800 shadow-2xl"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        
        <!-- App Brand Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-950/60 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-500 via-cyan-400 to-teal-400 flex items-center justify-center shadow-lg shadow-sky-500/30 group-hover:scale-105 transition">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 32 32" fill="currentColor">
                        <path d="M16 3 C16 3 8 12 8 19 a8 8 0 0 0 16 0 C24 12 16 3 16 3 Z" opacity="0.95"/>
                        <path d="M16 9 C16 9 11 15.5 11 19.5 a5 5 0 0 0 10 0 C21 15.5 16 9 16 9 Z" fill="white" opacity="0.4"/>
                        <ellipse cx="13" cy="20" rx="1.5" ry="2.5" fill="white" opacity="0.5"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-extrabold tracking-tight text-white flex items-center gap-1.5">
                        SIPAM <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-500/25 text-sky-300 font-bold border border-sky-500/40">ADMIN</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">HIPPAM Tirto Makmur</p>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto scrollbar-thin">
            <div class="px-3 pb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Menu Utama</div>
            
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-sky-600 to-sky-500 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard Global
            </a>

            <div class="pt-4 px-3 pb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Master Data RT & RW</div>

            <a href="{{ route('admin.rt.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.rt.*') ? 'bg-gradient-to-r from-sky-600 to-sky-500 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Data Wilayah RT
            </a>

            <a href="{{ route('admin.pelanggan.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.pelanggan.*') ? 'bg-gradient-to-r from-sky-600 to-sky-500 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Data RW / Warga
            </a>

            <a href="{{ route('admin.tarif.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.tarif.*') ? 'bg-gradient-to-r from-sky-600 to-sky-500 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pengaturan Tarif
            </a>

            <div class="pt-4 px-3 pb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Laporan & Pengguna</div>

            <a href="{{ route('admin.laporan.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.laporan.*') ? 'bg-gradient-to-r from-sky-600 to-sky-500 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Rekap & Laporan
            </a>

            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-sky-600 to-sky-500 text-white shadow-md shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Kelola Petugas / User
            </a>

            <div class="pt-4 px-3 pb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase">Akses Petugas</div>
            <a href="{{ route('petugas.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm text-sky-300 bg-sky-950/60 border border-sky-800/50 hover:bg-sky-900/60 transition shadow">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Buka Panel Petugas RT &rarr;
            </a>
        </nav>

        <!-- User Footer & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white font-bold flex items-center justify-center text-sm shadow shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@hippam.id' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-72">
        
        <!-- Header Navbar -->
        <header class="sticky top-0 z-30 flex items-center justify-between min-h-[70px] sm:min-h-[80px] px-4 sm:px-8 bg-white/95 backdrop-blur border-b-2 border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 py-2 min-w-0">
                <button @click="sidebarOpen = true" class="p-2 text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-xl lg:hidden border-2 border-slate-200 shrink-0" aria-label="Buka Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex flex-col justify-center min-w-0">
                    <h2 class="text-base sm:text-2xl font-black text-slate-900 tracking-tight leading-snug truncate">@yield('page_title', 'Dashboard')</h2>
                    <p class="text-[11px] sm:text-xs font-semibold text-slate-500 truncate hidden sm:block">Sistem Informasi Pengelolaan Air Minum HIPPAM Tirto Makmur</p>
                </div>
            </div>

            <!-- Top Right Bar -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('petugas.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-sky-700 bg-sky-50 border border-sky-200 rounded-xl hover:bg-sky-100 transition shadow-xs">
                    <span>Panel Petugas</span> &rarr;
                </a>
                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="hidden xs:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages & Main Page Content -->
        <main class="flex-1 p-3.5 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-4 sm:space-y-6">
            @if(session('success'))
                <div class="flex items-center gap-3 p-3.5 sm:p-4 bg-emerald-50 border-2 border-emerald-300 text-emerald-900 rounded-2xl shadow-xs animate-fade-in" role="alert">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-xs sm:text-sm font-bold">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center gap-3 p-3.5 sm:p-4 bg-rose-50 border-2 border-rose-300 text-rose-900 rounded-2xl shadow-xs animate-fade-in" role="alert">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-xs sm:text-sm font-bold">{{ session('error') }}</div>
                </div>
            @endif

            @if(session('warning'))
                <div class="flex items-center gap-3 p-3.5 sm:p-4 bg-amber-50 border-2 border-amber-300 text-amber-900 rounded-2xl shadow-xs animate-fade-in" role="alert">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div class="text-xs sm:text-sm font-bold">{{ session('warning') }}</div>
                </div>
            @endif

            <!-- Main Page Content -->
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="max-w-7xl w-full mx-auto px-4 sm:px-6 py-4 text-center text-xs text-slate-500 font-medium">
            SIPAM &bull; HIPPAM &ldquo;Tirto Makmur&rdquo; Desa Argosari &copy; {{ date('Y') }}
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
