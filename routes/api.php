<?php

use App\Http\Controllers\AdminControlleur;
use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;

Route::post('/authentification/user', [AuthUserController::class, 'auth_user']);
Route::post('/verify/otp/user', [AuthUserController::class, 'verify_otp']);
Route::post('/renvoyer/otp/user', [AuthUserController::class, 'renvoyer_otp']);
Route::get('/info/user', [AuthUserController::class, 'info_user'])->middleware('auth:user');
Route::post('/update/info/user', [AuthUserController::class, 'update_info_user'])->middleware('auth:user');
Route::post('/login/admin', [AdminControlleur::class, 'login_admin']);
Route::post('/ajouter/admin', [AdminControlleur::class, 'add_admin'])->middleware('auth:admin');
Route::post('/update/info/admin', [AdminControlleur::class, 'update_profil_admin'])->middleware('auth:admin');
Route::post('/delete/admin/{id}', [AdminControlleur::class, 'delete_admin'])->middleware('auth:admin');
Route::get('/liste/admins', [AdminControlleur::class, 'liste_admin'])->middleware('auth:admin');