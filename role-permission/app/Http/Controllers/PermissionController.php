<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully!');
    }

    public function assign(Role $role)
    {
        $permissions = Permission::all();
        return view('permissions.assign', compact('role', 'permissions'));
    }

    public function storeAssign(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.permissions', $role)->with('success', 'Permissions assigned successfully!');
    }
}
