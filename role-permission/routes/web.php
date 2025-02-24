<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'check.access'])->group(function () {
    Route::resource('posts', \App\Http\Controllers\PostController::class);
    Route::resource('categories', \App\Http\Controllers\PostController::class);
});


Route::middleware(['auth'])->group(function () {
    // Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    // Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    // Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');

    // Route::get('/roles/{role}/permissions', [PermissionController::class, 'assign'])->name('roles.permissions');
    // Route::post('/roles/{role}/permissions', [PermissionController::class, 'storeAssign'])->name('roles.permissions.store');

    // Quản lý Role
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');

    // Gán Permission vào Role
    Route::get('/roles/{role}/permissions', [RoleController::class, 'assign'])->name('roles.permissions');
    Route::post('/roles/{role}/permissions', [RoleController::class, 'storeAssign'])->name('roles.permissions.store');

    // Quản lý Permission
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');

    // users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/permissions', [UserController::class, 'assignPermissions'])->name('users.permissions');
    Route::post('/users/{user}/permissions', [UserController::class, 'storePermissions'])->name('users.permissions.store');
});
