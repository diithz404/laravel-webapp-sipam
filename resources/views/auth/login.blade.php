@extends('layouts.guest')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="max-w-md w-full my-8">
    <div class="bg-slate-900/90 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
        <div class="text-center mb-6">
            <img src="{{ asset('logohippam.png') }}" alt="Logo HIPPAM TIRTO MAKMUR" class="w-16 h-16 mx-auto mb-3 rounded-2xl object-cover shadow-lg bg-white p-1 border border-white/20">
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Selamat Datang</h2>
            <p class="text-sm text-slate-400 mt-1">HIPPAM TIRTO MAKMUR &bull; Desa Argosari</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs rounded-xl text-center font-medium shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs rounded-xl text-center font-medium shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email / Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-4 py-3 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition shadow-inner"
                           placeholder="nama@email.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-4 py-3 bg-slate-800/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition shadow-inner"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center text-slate-400 hover:text-slate-300 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-sky-500 focus:ring-sky-500">
                    <span class="ml-2">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 mt-2 rounded-xl bg-gradient-to-r from-sky-500 via-sky-600 to-teal-500 hover:from-sky-400 hover:to-teal-400 text-white font-bold text-sm shadow-lg shadow-sky-500/25 transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                <span>Masuk ke Sistem</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-500">
                Sistem Informasi Pembayaran Air Minum &bull; Desa Argosari
            </p>
        </div>
    </div>
</div>
@endsection
