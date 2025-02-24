<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function assignPermissions(User $user)
    {
        $permissions = Permission::all();
        return view('users.assign_permission', compact('user', 'permissions'));
    }

    public function storePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $user->syncPermissions($request->permissions);

        return redirect()->route('users.index', $user);
        // ->with('success', 'Permissions updated successfully!');
    }
}

