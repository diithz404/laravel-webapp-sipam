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
    public function index()
    {
        $users = User::with(['rts'])->orderBy('role')->orderBy('name')->get();
        $rts = Rt::orderBy('kode_rt')->get();

        return view('admin.users.index', compact('users', 'rts'));
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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'active',
            'password' => Hash::make($validated['password']),
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

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
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
