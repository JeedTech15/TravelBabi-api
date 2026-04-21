<?php

use App\Http\Controllers\AdminControlleur;
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

Route::get('/admin/forgot-password', function () {
    return view('admin.forgot-password');
})->name('admin.forgot');

Route::post('/admin/forgot-password', [AdminControlleur::class, 'forgot'])->name('admin.forgot.post');

Route::get('/admin/reset-password/{token}', [AdminControlleur::class, 'showResetForm'])->name('admin.reset.form');

Route::post('/admin/reset-password', [AdminControlleur::class, 'reset'])->name('admin.reset.post');
Route::get('/admin/reset-password/{token}', [AdminControlleur::class, 'showResetForm'])
    ->name('password.reset');