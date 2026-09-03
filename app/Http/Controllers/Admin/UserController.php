<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rt;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roleFilter = $request->query('role');
        $rtFilter = $request->query('rt_id');
        $dusunFilter = $request->query('dusun');
        $search = $request->query('search');

        $query = User::with(['rts' => function ($q) {
            $q->withCount('pelanggans');
        }, 'rt' => function ($q) {
            $q->withCount('pelanggans');
        }]);

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        if ($dusunFilter) {
            $query->where(function ($q) use ($dusunFilter) {
                $q->whereHas('rt', fn($rq) => $rq->where('dusun', $dusunFilter)->orWhere('wilayah', 'like', "%{$dusunFilter}%"))
                  ->orWhereHas('rts', fn($rq) => $rq->where('dusun', $dusunFilter)->orWhere('wilayah', 'like', "%{$dusunFilter}%"));
            });
        }

        if ($rtFilter) {
            $query->where(function ($q) use ($rtFilter) {
                $q->where('rt_id', $rtFilter)
                  ->orWhereHas('rts', fn($rq) => $rq->where('rts.id', $rtFilter));
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->get();
        $rts = Rt::withCount('pelanggans')->orderBy('kode_rt')->get();
        $dusunList = ['Pateguhan', 'Gentong', 'Bendrong'];

        // Statistics
        $totalAdmin = User::where('role', 'admin')->count();
        $totalPetugas = User::where('role', 'petugas')->count();
        $petugasDefaultPassword = User::where('role', 'petugas')->whereNull('password_changed_at')->count();

        return view('admin.users.index', compact(
            'users',
            'rts',
            'roleFilter',
            'rtFilter',
            'dusunFilter',
            'dusunList',
            'search',
            'totalAdmin',
            'totalPetugas',
            'petugasDefaultPassword'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,petugas',
            'password' => 'required|string|min:6',
            'rt_ids' => 'nullable|array',
            'rt_ids.*' => 'exists:rts,id',
        ]);

        $primaryRtId = !empty($validated['rt_ids']) ? $validated['rt_ids'][0] : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'active',
            'is_active' => true,
            'rt_id' => $validated['role'] === 'petugas' ? $primaryRtId : null,
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(), // Manual create by admin with custom password
        ]);

        if (!empty($validated['rt_ids']) && $user->isPetugas()) {
            $user->rts()->sync($validated['rt_ids']);
        }

        ActivityLog::log('TAMBAH_USER', "Menambahkan pengguna baru: {$user->name} sebagai {$user->role}");

        return redirect()->route('admin.users.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,petugas',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6',
            'rt_ids' => 'nullable|array',
            'rt_ids.*' => 'exists:rts,id',
        ]);

        $primaryRtId = !empty($validated['rt_ids']) ? $validated['rt_ids'][0] : null;

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'is_active' => $validated['status'] === 'active',
            'rt_id' => $validated['role'] === 'petugas' ? $primaryRtId : null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['password_changed_at'] = now();
        }

        $user->update($updateData);

        if ($user->isPetugas()) {
            $user->rts()->sync($validated['rt_ids'] ?? []);
        } else {
            $user->rts()->detach();
        }

        ActivityLog::log('UPDATE_USER', "Memperbarui profil pengguna {$user->name} (Role: {$user->role}, Status: {$user->status})");

        return redirect()->route('admin.users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $newPassword = $request->input('custom_password') ?: 'password';

        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => null, // Reset status to default
        ]);

        ActivityLog::log('RESET_PASSWORD_USER', "Mereset password akun pengguna: {$user->name} ({$user->email})");

        return back()->with('success', "Password untuk akun {$user->name} berhasil direset menjadi: '{$newPassword}'. Silakan infokan ke petugas terkait.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.");
        }

        $nama = $user->name;
        $user->rts()->detach();
        $user->delete();

        ActivityLog::log('HAPUS_USER', "Menghapus akun pengguna: {$nama}");

        return redirect()->route('admin.users.index')->with('success', "Pengguna {$nama} berhasil dihapus.");
    }
}
