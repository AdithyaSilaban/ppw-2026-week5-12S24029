<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Baca email terakhir dari Cookie jika tersedia
        $savedEmail = $request->cookie('del_last_librarian_email', '');

        return view('auth.login', compact('savedEmail'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Wajib: Cegah serangan Session Fixation
            $request->session()->regenerate();

            // Simpan jejak waktu login pada Session server
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Wajib: Simpan email pustakawan ke HTTP Cookie (aktif selama 7 hari)
            Cookie::queue('del_last_librarian_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))
                ->with('success', 'Selamat datang! Sesi pustakawan telah aktif.');
        }

        return back()->withInput()->with('error', 'Kredensial login pustakawan tidak valid.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Hancurkan session server dan buat token CSRF baru
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi Anda telah berhasil dikeluarkan secara aman.');
    }
}