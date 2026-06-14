<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MealRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

// Guest / Public redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated user requests
Route::middleware('role:user,admin,super_admin')->group(function () {
    Route::get('/request', [MealRequestController::class, 'index'])->name('request.index');
    Route::post('/request', [MealRequestController::class, 'store'])->name('request.store');
});

// Admin area
Route::middleware('role:admin,super_admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
    
    // User management routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/template', [UserController::class, 'template'])->name('users.template');
    Route::post('/users/create', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/reset', [UserController::class, 'resetPassword'])->name('users.reset');
    Route::post('/users/{id}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');

    // Reports routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/download', [ReportController::class, 'download'])->name('reports.download');

    // Super Admin configurations
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    });
});


