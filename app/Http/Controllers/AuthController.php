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
            $request->session()->regenerate();

            // Sesi waktu login kini akurat mengikuti Asia/Jakarta
            session(['login_timestamp' => now()->translatedFormat('d M Y, H:i:s') . ' WIB']);

            Cookie::queue('del_last_librarian_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))
                ->with('success', 'Selamat datang! Sesi pustakawan telah aktif.');
        }

        // Hanya email yang dikembalikan ke form, password TIDAK ikut di-flash
        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Kredensial login pustakawan tidak valid.');
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