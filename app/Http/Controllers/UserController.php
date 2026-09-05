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
        $searchName = $request->input('search_name', $search);
        $searchEmail = $request->input('search_email');
        $roleFilter = $request->input('role');
        $dateAfter = $request->input('date_after');
        $dateBefore = $request->input('date_before');
        $dateOn = $request->input('date_on');

        $query = User::query();

        if ($searchName) {
            $query->where('name', 'like', "%{$searchName}%");
        }

        if ($searchEmail) {
            $query->where('email', 'like', "%{$searchEmail}%");
        }

        if ($roleFilter) {
            if ($roleFilter === 'finance' || $roleFilter === 'booker') {
                $query->whereIn('role', ['finance', 'booker', 'payer']);
            } else {
                $query->where('role', $roleFilter);
            }
        }

        if ($dateOn) {
            $query->whereDate('created_at', '=', $dateOn);
        } else {
            if ($dateAfter) {
                $query->whereDate('created_at', '>=', $dateAfter);
            }
            if ($dateBefore) {
                $query->whereDate('created_at', '<=', $dateBefore);
            }
        }

        // Summary Statistics
        $totalUsers = User::count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalBooker = User::whereIn('role', ['finance', 'booker', 'payer'])->count();
        $totalRegularUser = User::where('role', 'user')->count();

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $roleOptions = ['admin', 'finance', 'user'];

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('users._rows', compact('users'))->render(),
                'next_page_url' => $users->nextPageUrl(),
                'has_more' => $users->hasMorePages(),
                'total' => $users->total(),
            ]);
        }

        return view('users.index', compact(
            'users',
            'search',
            'searchName',
            'searchEmail',
            'roleFilter',
            'dateAfter',
            'dateBefore',
            'dateOn',
            'totalUsers',
            'totalAdmin',
            'totalBooker',
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

        $roles = ['admin', 'finance', 'user'];
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
            'role' => 'required|string|in:admin,finance,booker,user',
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
