<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('petugas.dashboard');
        }

        $demoUsers = User::with('rts')->get();

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            ActivityLog::log('LOGIN', "User {$user->name} berhasil login sebagai {$user->role}");

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', "Selamat datang kembali, {$user->name}!");
            }

            return redirect()->intended(route('petugas.dashboard'))->with('success', "Selamat datang kembali, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request, User $user)
    {
        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::log('QUICK_LOGIN', "Beralih akun ke {$user->name} ({$user->role})");

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', "Beralih ke Super Admin: {$user->name}");
        }

        return redirect()->route('petugas.dashboard')->with('success', "Beralih ke Petugas RT: {$user->name}");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('LOGOUT', "User " . Auth::user()->name . " keluar dari sistem");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
