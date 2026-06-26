<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['message' => 'Fintech Wallet API']));

Route::get('/login', fn () => response()->json(['message' => 'Unauthenticated.'], 401))->name('login');
