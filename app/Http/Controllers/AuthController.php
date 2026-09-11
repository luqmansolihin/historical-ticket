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
            return redirect()->route('main.index');
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

            return redirect()->intended(route('main.index'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided email or password is incorrect.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form (Admin Only)
     */
    public function showRegister()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access Denied. Only Administrators can create new user accounts.');
        }

        return view('auth.register');
    }

    /**
     * Process user creation by Admin
     */
    public function register(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access Denied. Only Administrators can create new user accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|string|in:admin,finance,user',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'New user account "' . $newUser->name . '" (' . ucfirst($newUser->role) . ') has been created successfully!');
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
            ->with('success', 'You have been successfully logged out.');
    }
}
