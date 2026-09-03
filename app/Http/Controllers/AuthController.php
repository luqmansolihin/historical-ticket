<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('tickets.index');
        }

        return view('auth.login');
    }

    /**
     * Process login authentication
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('tickets.index'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Quick login helper for testing demo accounts
     */
    public function quickLogin(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('tickets.index')
                ->with('success', 'Login berhasil sebagai ' . $user->name . ' (' . ucfirst($user->role) . ')');
        }

        return redirect()->route('login')->with('error', 'Akun demo tidak ditemukan.');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('tickets.index');
        }

        return view('auth.register');
    }

    /**
     * Process user registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|string|in:booker,payer,user',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Auth::login($user);

        return redirect()->route('tickets.index')
            ->with('success', 'Akun berhasil dibuat! Anda telah login sebagai ' . ucfirst($user->role));
    }

    /**
     * Process logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
