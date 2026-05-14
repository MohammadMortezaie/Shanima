<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProgramItemController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\CompletionController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('client.dashboard');
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->as('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('clients', ClientController::class)
            ->except(['destroy'])
            ->parameters(['clients' => 'client']);

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::post('/clients/{client}/program-items', [ProgramItemController::class, 'store'])
            ->name('clients.program-items.store');
        Route::get('/clients/{client}/program-items/{programItem}/edit', [ProgramItemController::class, 'edit'])
            ->name('clients.program-items.edit');
        Route::put('/clients/{client}/program-items/{programItem}', [ProgramItemController::class, 'update'])
            ->name('clients.program-items.update');
        Route::delete('/clients/{client}/program-items/{programItem}', [ProgramItemController::class, 'destroy'])
            ->name('clients.program-items.destroy');
    });

    Route::middleware('role:client')->group(function (): void {
        Route::get('/my-day', [ClientDashboardController::class, 'index'])->name('client.dashboard');
        Route::post('/my-day/program-items/{programItem}/completions', [CompletionController::class, 'store'])
            ->name('client.completions.store');
        Route::delete('/my-day/program-items/{programItem}/completions', [CompletionController::class, 'destroy'])
            ->name('client.completions.destroy');
    });
});
