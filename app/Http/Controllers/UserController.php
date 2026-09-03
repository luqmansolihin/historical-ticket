<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of user accounts (Admin only).
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur kelola akun khusus untuk Admin.');
        }

        $search = $request->input('search');
        $roleFilter = $request->input('role');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        // Summary Statistics
        $totalUsers = User::count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalBooker = User::where('role', 'booker')->count();
        $totalPayer = User::where('role', 'payer')->count();
        $totalRegularUser = User::where('role', 'user')->count();

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $roleOptions = ['admin', 'booker', 'payer', 'user'];

        return view('users.index', compact(
            'users',
            'search',
            'roleFilter',
            'totalUsers',
            'totalAdmin',
            'totalBooker',
            'totalPayer',
            'totalRegularUser',
            'roleOptions'
        ));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur kelola akun khusus untuk Admin.');
        }

        $roles = ['admin', 'booker', 'payer', 'user'];
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur kelola akun khusus untuk Admin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|in:admin,booker,payer,user',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur kelola akun khusus untuk Admin.');
        }

        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang terhubung.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Akun {$userName} berhasil dihapus.");
    }
}
