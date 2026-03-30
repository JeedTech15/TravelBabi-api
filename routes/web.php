<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('login', function(){
    return response()->json([
        'success' => false,
        'message' => 'Token invalide ou expiré. Veuillez vous connecter.'
    ],403);
})->name('login');