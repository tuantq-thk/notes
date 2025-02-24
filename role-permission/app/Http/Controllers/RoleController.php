<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    public function assign(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.assign', compact('role', 'permissions'));
    }

    public function storeAssign(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);
        // dd($request->permissions);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Permissions assigned successfully!');
        // ->with('success', 'Permissions assigned successfully!');
    }
}
