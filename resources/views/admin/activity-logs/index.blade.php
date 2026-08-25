@extends('layouts.admin')

@section('title', 'Log Aktivitas & Audit')
@section('page_title', 'Log Aktivitas & Jejak Audit')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Catatan Aktivitas Sistem</h3>
            <p class="text-xs text-slate-500 mt-0.5">Seluruh aksi input meter, pencatatan pembayaran, dan ubah tarif terekam lengkap</p>
        </div>

        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2">
            <select name="aksi" onchange="this.form.submit()"
                    class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                <option value="">Semua Tipe Aksi</option>
                @foreach($distinctAksi as $a)
                    <option value="{{ $a }}" {{ $aksi === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            @if($aksi)
                <a href="{{ route('admin.activity-logs.index') }}" class="p-2 text-slate-400 hover:text-slate-600 bg-slate-100 rounded-xl" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Waktu</th>
                        <th class="p-3.5">Pengguna</th>
                        <th class="p-3.5 text-center">Aksi</th>
                        <th class="p-3.5">Deskripsi Detail</th>
                        <th class="p-3.5 text-right font-mono">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 font-medium text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="p-3.5 font-bold text-slate-900">
                                {{ $log->user->name ?? 'Sistem' }}
                                <span class="text-[10px] text-slate-400 font-normal block">{{ $log->user->role ?? '' }}</span>
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-sky-100 text-sky-800">
                                    {{ $log->aksi }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-800 font-medium">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="p-3.5 text-right font-mono text-slate-400">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">Belum ada catatan log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
