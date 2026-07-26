<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'login')->name('login');
});

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/employees', 'employees')->name('employees.index');
    Route::livewire('/suppliers', 'suppliers')->name('suppliers.index');

    Route::middleware('can:roles.view')->group(function () {
        Route::livewire('/roles', 'roles.index')->name('roles.index');
    });
    Route::middleware('can:roles.create')->group(function () {
        Route::livewire('/roles/create', 'roles.form')->name('roles.create');
    });
    Route::middleware('can:roles.edit')->group(function () {
        Route::livewire('/roles/{role}/edit', 'roles.form')->name('roles.edit');
    });

    Route::middleware('can:users.view')->group(function () {
        Route::livewire('/users', 'users.index')->name('users.index');
    });
    Route::middleware('can:users.create')->group(function () {
        Route::livewire('/users/create', 'users.form')->name('users.create');
    });
    Route::middleware('can:users.edit')->group(function () {
        Route::livewire('/users/{user}/edit', 'users.form')->name('users.edit');
    });

    Route::middleware('can:customers.view')->group(function () {
        Route::livewire('/customers', 'customers.index')->name('customers.index');
    });
    Route::middleware('can:customers.create')->group(function () {
        Route::livewire('/customers/create', 'customers.form')->name('customers.create');
    });
    Route::middleware('can:customers.edit')->group(function () {
        Route::livewire('/customers/{customer}/edit', 'customers.form')->name('customers.edit');
    });
    Route::middleware('can:customers.view')->group(function () {
        Route::livewire('/customers/{customer}', 'customers.show')->name('customers.show');
    });
});
