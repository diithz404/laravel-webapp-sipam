<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — HIPPAM Tirto Makmur</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
                            50: '#f0f9ff',
                            200: '#bae6fd',
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
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-950 text-slate-100 flex flex-col justify-between relative overflow-x-hidden bg-grid-pattern">

    <!-- Ambient Glow Effects -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[600px] h-[350px] bg-sky-500/20 blur-[120px] rounded-full pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-0 w-[400px] h-[300px] bg-teal-500/15 blur-[100px] rounded-full pointer-events-none -z-10"></div>

    <!-- Header Navigation -->
    <header class="max-w-6xl w-full mx-auto px-4 py-6 flex items-center justify-between z-10">
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 via-cyan-400 to-teal-400 flex items-center justify-center shadow-lg shadow-sky-500/30 group-hover:scale-105 transition">
                <svg class="w-7 h-7 text-white" viewBox="0 0 32 32" fill="currentColor">
                    <path d="M16 3 C16 3 8 12 8 19 a8 8 0 0 0 16 0 C24 12 16 3 16 3 Z" opacity="0.95"/>
                    <path d="M16 9 C16 9 11 15.5 11 19.5 a5 5 0 0 0 10 0 C21 15.5 16 9 16 9 Z" fill="white" opacity="0.4"/>
                    <ellipse cx="13" cy="20" rx="1.5" ry="2.5" fill="white" opacity="0.5"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-white tracking-tight leading-tight">HIPPAM Tirto Makmur</h1>
                <p class="text-xs text-sky-300">Desa Argosari, Kec. Jabung, Kab. Malang</p>
            </div>
        </a>
    </header>

    <!-- Main Page Content -->
    <main class="flex-1 flex items-center justify-center p-4 z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-500 z-10 border-t border-slate-900">
        Sistem Informasi Pengelolaan &amp; Pembayaran Air Minum &bull; HIPPAM Tirto Makmur, Desa Argosari
    </footer>

</body>
</html>
