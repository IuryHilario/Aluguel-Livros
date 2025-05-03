<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\AluguelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

// Rota inicial redirecionando para o dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Books routes
    Route::prefix('books')->group(function () {
        Route::get('/', [LivroController::class, 'index'])->name('books.index');
        Route::get('/create', [LivroController::class, 'create'])->name('books.create');
        Route::post('/store', [LivroController::class, 'store'])->name('books.store');
        Route::get('/categories', [LivroController::class, 'categories'])->name('books.categories');
        Route::get('/{id}', [LivroController::class, 'show'])->name('books.show');
        Route::get('/{id}/edit', [LivroController::class, 'edit'])->name('books.edit');
        Route::put('/{id}', [LivroController::class, 'update'])->name('books.update');
        Route::delete('/{id}', [LivroController::class, 'destroy'])->name('books.destroy');
        Route::get('/{id}/capa', [LivroController::class, 'getCapa'])->name('books.capa');
    });

    // Rotas de aluguéis agrupadas com prefix
    Route::prefix('rentals')->group(function () {
        Route::get('/', [AluguelController::class, 'index'])->name('rentals.index');
        Route::get('/create', [AluguelController::class, 'create'])->name('rentals.create');
        Route::post('/', [AluguelController::class, 'store'])->name('rentals.store');
        Route::get('/history', [AluguelController::class, 'history'])->name('rentals.history');
        Route::get('/search/users', [AluguelController::class, 'searchUsers'])->name('rentals.search.users');
        Route::get('/search/books', [AluguelController::class, 'searchBooks'])->name('rentals.search.books');
        Route::get('/{id}', [AluguelController::class, 'show'])->name('rentals.show');
        Route::get('/{id}/return', [AluguelController::class, 'return'])->name('rentals.return');
        Route::get('/{id}/renew', [AluguelController::class, 'renew'])->name('rentals.renew');
    });

    // Users routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('users.index');
        Route::get('/create', [UsuarioController::class, 'create'])->name('users.create');
        Route::post('/store', [UsuarioController::class, 'store'])->name('users.store');
        Route::get('/{id}', [UsuarioController::class, 'show'])->name('users.show');
        Route::get('/{id}/edit', [UsuarioController::class, 'edit'])->name('users.edit');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('users.destroy');
    });

    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/overdue-filter', [ReportController::class, 'overdueFilter'])->name('reports.overdue-filter');
        Route::get('/pdf', [ReportController::class, 'generatePdf'])->name('reports.pdf');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
        
        // Gerenciamento de backups
        Route::get('/backups', [SettingsController::class, 'backups'])->name('backups');
        Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
        Route::get('/backup/download/{filename}', [SettingsController::class, 'downloadBackup'])->name('backup.download');
        Route::delete('/backup/delete/{filename}', [SettingsController::class, 'deleteBackup'])->name('backup.delete');
    });
});