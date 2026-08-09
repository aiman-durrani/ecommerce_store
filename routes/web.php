<?php

use Illuminate\Support\Facades\Route;

// Storefront routes
Route::get('/', function () {
    return view('products.index');
})->name('home');

Route::get('/products', function () {
    return view('products.index');
})->name('products.index');

Route::get('/products/{id}', function ($id) {
    return view('products.show', ['id' => $id]);
})->name('products.show');

Route::get('/cart', function () {
    return view('cart.index');
})->name('cart.index');

Route::get('/profile', function () {
    return view('profile.index');
})->name('profile.index');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Admin panel routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', function () {
        return view('admin.login');
    })->name('login');

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/products', function () {
        return view('admin.products.index');
    })->name('products.index');

    Route::get('/products/create', function () {
        return view('admin.products.form');
    })->name('products.create');

    Route::get('/products/{id}/edit', function ($id) {
        return view('admin.products.form', ['id' => $id]);
    })->name('products.edit');

    Route::get('/orders', function () {
        return view('admin.orders.index');
    })->name('orders.index');
});
